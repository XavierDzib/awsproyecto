<?php

namespace App\Services;

use App\Models\Alumno;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;

class AlumnoService
{
    private DynamoDbClient $dynamoDb;
    private string $tableName = 'sesiones-alumnos';

    public function __construct()
    {
        // Configuramos el cliente oficial usando los datos del .env
        $this->dynamoDb = new DynamoDbClient([
            'region'      => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'version'     => 'latest',
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                'token'  => env('AWS_SESSION_TOKEN'),
            ],
        ]);
    }

    /**
     * Obtener todos los alumnos de la base de datos.
     */
    public function getAll(): array
    {
        return Alumno::all()->toArray();
    }

    /**
     * Obtener un alumno por su ID.
     */
    public function getById($id): ?Alumno
    {
        return Alumno::find($id);
    }

    /**
     * Crear un alumno en la base de datos (con contraseña encriptada).
     */
    public function create(array $data): Alumno
    {
        return Alumno::create([
            'nombres'   => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'matricula' => $data['matricula'],
            'promedio'  => $data['promedio'],
            'password'  => Hash::make($data['password']),
        ]);
    }

    /**
     * Actualizar los datos de un alumno existente.
     */
    public function update($id, array $data): ?Alumno
    {
        $alumno = Alumno::find($id);

        if (!$alumno) {
            return null;
        }

        // Si la petición incluye un cambio de contraseña, la encriptamos antes de guardar.
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $alumno->update($data);

        return $alumno;
    }

    /**
     * Eliminar un alumno por su ID.
     */
    public function delete($id): bool
    {
        $alumno = Alumno::find($id);

        if (!$alumno) {
            return false;
        }

        return (bool) $alumno->delete();
    }

    /**
     * Sube la foto de perfil a S3 y actualiza la URL en la base de datos.
     */
    public function uploadFoto($id, $file): ?Alumno
    {
        $alumno = Alumno::find($id);

        if (!$alumno) {
            return null;
        }

        // Definimos un nombre único para el archivo usando la matrícula o ID
        $fileName = 'avatar_' . $alumno->id . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Subimos el archivo a S3 con permisos de lectura pública (Public Read)
        // Laravel se encarga de usar el SDK de AWS
        $path = Storage::disk('s3')->putFileAs('fotos-perfil', $file, $fileName, 'public');

        if (!$path) {
            return null;
        }

        // Obtenemos la URL pública del archivo en el bucket de Amazon S3
        $url = Storage::disk('s3')->url($path);

        // Guardamos la URL en el registro del alumno
        $alumno->fotoPerfilUrl = $url;
        $alumno->save();

        return $alumno;
    }

    /**
     * Valida el password y genera una sesión activa en la tabla de DynamoDB.
     */
    public function createSession($id, string $password): ?array
    {
        $alumno = Alumno::find($id);

        // Verifica si el alumno existe y coincide la contraseña encriptada en la RDS
        if (!$alumno || !Hash::check($password, $alumno->password)) {
            return null;
        }

        $sessionId = (string) Str::uuid();
        $timestamp = time();
        $sessionString = Str::random(128); 

        try {
            // Inserción directa en DynamoDB utilizando el tipado Marshaled de AWS (S = String, N = Number, BOOL = Boolean)
            $this->dynamoDb->putItem([
                'TableName' => $this->tableName,
                'Item' => [
                    'id'            => ['S' => $sessionId],
                    'fecha'         => ['N' => (string) $timestamp],
                    'alumnoId'      => ['N' => (string) $id],
                    'active'        => ['BOOL' => true],
                    'sessionString' => ['S' => $sessionString],
                ]
            ]);

            return [
                'id'            => $sessionId,
                'fecha'         => $timestamp,
                'alumnoId'      => (int) $id,
                'active'        => true,
                'sessionString' => $sessionString
            ];

        } catch (DynamoDbException $e) {
            \Log::error("Error en DynamoDB putItem: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica que exista la sesión en DynamoDB para ese alumnoId, 
     * que coincida el sessionString y que active sea TRUE.
     */
    public function verifySession($alumnoId, string $sessionString): bool
    {
        try {
            // Buscamos en DynamoDB mediante un Scan para evaluar atributos secundarios
            $result = $this->dynamoDb->scan([
                'TableName' => $this->tableName,
                'FilterExpression' => 'alumnoId = :aId AND sessionString = :sStr',
                'ExpressionAttributeValues' => [
                    ':aId'  => ['N' => (string) $alumnoId],
                    ':sStr' => ['S' => $sessionString]
                ]
            ]);

            if ($result['Count'] > 0) {
                $item = $result['Items'][0];
                // Retorna true solo si el valor booleano 'active' es verdadero
                return isset($item['active']['BOOL']) && $item['active']['BOOL'] === true;
            }

            return false;

        } catch (DynamoDbException $e) {
            \Log::error("Error en DynamoDB scan (verify): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca la sesión activa y actualiza el campo active a FALSE.
     */
    public function logoutSession($alumnoId, string $sessionString): bool
    {
        try {
            // Buscamos el ítem para obtener su llave primaria (id)
            $result = $this->dynamoDb->scan([
                'TableName' => $this->tableName,
                'FilterExpression' => 'alumnoId = :aId AND sessionString = :sStr',
                'ExpressionAttributeValues' => [
                    ':aId'  => ['N' => (string) $alumnoId],
                    ':sStr' => ['S' => $sessionString]
                ]
            ]);

            if ($result['Count'] === 0) {
                return false;
            }

            $primaryKeyId = $result['Items'][0]['id']['S'];

            // Actualizamos el registro cambiando active a false
            $this->dynamoDb->updateItem([
                'TableName' => $this->tableName,
                'Key' => [
                    'id' => ['S' => $primaryKeyId]
                ],
                'UpdateExpression' => 'SET active = :act',
                'ExpressionAttributeValues' => [
                    ':act' => ['BOOL' => false]
                ]
            ]);

            return true;

        } catch (DynamoDbException $e) {
            \Log::error("Error en DynamoDB updateItem (logout): " . $e->getMessage());
            return false;
        }
    }
}