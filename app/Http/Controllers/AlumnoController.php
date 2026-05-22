<?php

namespace App\Http\Controllers;

use App\Services\AlumnoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AlumnoController extends Controller
{
    private AlumnoService $alumnoService;

    public function __construct(AlumnoService $alumnoService)
    {
        $this->alumnoService = $alumnoService;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->alumnoService->getAll(), 200);
    }

    public function show($id): JsonResponse
    {
        $alumno = $this->alumnoService->getById($id);

        if (!$alumno) {
            return response()->json(['error' => 'Alumno no encontrado'], 404);
        }

        return response()->json($alumno, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'matricula' => 'required|string|max:50',
            'promedio' => 'required|numeric|between:0,10',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $alumno = $this->alumnoService->create($validator->validated());

        return response()->json($alumno, 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombres' => 'sometimes|string|max:255',
            'apellidos' => 'sometimes|string|max:255',
            'matricula' => 'sometimes|string|max:50',
            'promedio' => 'sometimes|numeric|between:0,10',
            'password' => 'sometimes|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $alumno = $this->alumnoService->update($id, $validator->validated());

        if (!$alumno) {
            return response()->json(['error' => 'Alumno no encontrado'], 404);
        }

        return response()->json($alumno, 200);
    }

    public function destroy($id): JsonResponse
    {
        $deleted = $this->alumnoService->delete($id);

        if (!$deleted) {
            return response()->json(['error' => 'Alumno no encontrado'], 404);
        }

        return response()->json(['message' => 'Alumno eliminado correctamente'], 200);
    }

    public function sendEmail(Request $request, $id): JsonResponse
    {
        // Invocamos el servicio (el cual internamente debe publicar en el topic de SNS)
        $emailEnviado = $this->alumnoService->sendNotificationEmail($id);

        if (!$emailEnviado) {
            return response()->json(['error' => 'No se pudo enviar el correo o el alumno no existe'], 400);
        }

        // El test requiere rigurosamente un código 200 y formato JSON
        return response()->json([
            'message' => 'Email enviado correctamente'
        ], 200);
    }

    public function uploadFotoPerfil(Request $request, $id): JsonResponse
    {
        // Validación que venga un archivo y que sea una imagen válida
        $validator = Validator::make($request->all(), [
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // multipart/form-data
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Invocación del servicio para procesar el archivo y guardarlo en S3
        $alumno = $this->alumnoService->uploadFoto($id, $request->file('foto'));

        if (!$alumno) {
            return response()->json(['error' => 'Alumno no encontrado'], 404);
        }

        return response()->json($alumno, 200);
    }

    public function login(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Invocación del servicio para validar contraseña y registrar la sesión en DynamoDB
        $sessionData = $this->alumnoService->createSession($id, $request->input('password'));

        if (!$sessionData) {
            return response()->json(['error' => 'Credenciales inválidas o alumno no encontrado'], 400);
        }

        return response()->json($sessionData, 200);
    }

    public function verifySession(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sessionString' => 'required|string|size:128',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $isValid = $this->alumnoService->verifySession($id, $request->input('sessionString'));

        if (!$isValid) {
            return response()->json(['error' => 'Sesión inválida o expirada'], 400);
        }

        return response()->json(['message' => 'Sesión válida'], 200);
    }

    public function logout(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sessionString' => 'required|string|size:128',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $loggedOut = $this->alumnoService->logoutSession($id, $request->input('sessionString'));

        if (!$loggedOut) {
            return response()->json(['error' => 'No se pudo cerrar la sesión o no existe'], 400);
        }

        return response()->json(['message' => 'Sesión cerrada correctamente'], 200);
    }
}