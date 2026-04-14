<?php

namespace App\Services;

use App\Models\Alumno;
use Illuminate\Support\Facades\Cache;

class AlumnoService
{
    private const CACHE_KEY = 'alumnos';
    private const NEXT_ID_KEY = 'next_alumno_id';

    public function getAll(): array
    {
        $alumnos = Cache::get(self::CACHE_KEY, []);
        return array_values($alumnos);
    }

    public function getById($id): ?Alumno
    {
        $alumnos = Cache::get(self::CACHE_KEY, []);
        return $alumnos[$id] ?? null;
    }

    public function create(array $data): Alumno
    {
        $alumnos = Cache::get(self::CACHE_KEY, []);
        
        // Verificar si viene un ID en los datos
        if (isset($data['id'])) {
            $id = $data['id'];
            
            // Verificar si ya existe un alumno con ese ID
            if (isset($alumnos[$id])) {
                // Si ya existe, actualizar
                return $this->update($id, $data);
            }
            
            // Crear con el ID proporcionado
            $alumno = Alumno::fromArray($id, $data);
            $alumnos[$id] = $alumno;
            Cache::put(self::CACHE_KEY, $alumnos);
            
            // Actualizar el nextId si es necesario
            $nextId = Cache::get(self::NEXT_ID_KEY, 1);
            if ($id >= $nextId) {
                Cache::put(self::NEXT_ID_KEY, $id + 1);
            }
            
            return $alumno;
        }
        
        // Si no viene ID, usar el auto-increment
        $nextId = Cache::get(self::NEXT_ID_KEY, 1);
        $alumno = Alumno::fromArray($nextId, $data);
        $alumnos[$nextId] = $alumno;
        
        Cache::put(self::CACHE_KEY, $alumnos);
        Cache::put(self::NEXT_ID_KEY, $nextId + 1);
        
        return $alumno;
    }

    public function update($id, array $data): ?Alumno
    {
        $alumnos = Cache::get(self::CACHE_KEY, []);

        if (!isset($alumnos[$id])) {
            return null;
        }

        $alumno = $alumnos[$id];

        if (isset($data['nombres'])) $alumno->nombres = $data['nombres'];
        if (isset($data['apellidos'])) $alumno->apellidos = $data['apellidos'];
        if (isset($data['matricula'])) $alumno->matricula = $data['matricula'];
        if (isset($data['promedio'])) $alumno->promedio = (float) $data['promedio'];

        $alumnos[$id] = $alumno;
        Cache::put(self::CACHE_KEY, $alumnos);

        return $alumno;
    }

    public function delete($id): bool
    {
        $alumnos = Cache::get(self::CACHE_KEY, []);

        if (!isset($alumnos[$id])) {
            return false;
        }

        unset($alumnos[$id]);
        Cache::put(self::CACHE_KEY, $alumnos);

        return true;
    }
}