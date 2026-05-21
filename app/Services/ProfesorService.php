<?php

namespace App\Services;

use App\Models\Profesor;

class ProfesorService
{
    /**
     * Obtener todos los profesores de la base de datos.
     */
    public function getAll(): array
    {
        // Eloquent nos devuelve una colección; la convertimos a array.
        return Profesor::all()->toArray();
    }

    /**
     * Obtener un profesor por su ID autónomo de la base de datos.
     */
    public function getById($id): ?Profesor
    {
        // find($id) busca por la llave primaria y regresa el modelo o null si no existe.
        return Profesor::find($id);
    }

    /**
     * Crear un profesor delegando el ID a la base de datos.
     */
    public function create(array $data): Profesor
    {
        return Profesor::create([
            'numeroEmpleado' => $data['numeroEmpleado'],
            'nombres'        => $data['nombres'],
            'apellidos'      => $data['apellidos'],
            'horasClase'     => $data['horasClase'],
        ]);
    }

    /**
     * Actualizar los datos de un profesor existente.
     */
    public function update($id, array $data): ?Profesor
    {
        $profesor = Profesor::find($id);

        if (!$profesor) {
            return null;
        }

        // Actualizamos solo los campos que vengan en la petición gracias al 'sometimes' del validador.
        $profesor->update($data);

        return $profesor;
    }

    /**
     * Eliminar un profesor por su ID.
     */
    public function delete($id): bool
    {
        $profesor = Profesor::find($id);

        if (!$profesor) {
            return false;
        }

        return (bool) $profesor->delete();
    }
}