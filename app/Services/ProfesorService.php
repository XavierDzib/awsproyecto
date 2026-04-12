<?php

namespace App\Services;

use App\Models\Profesor;
use Illuminate\Support\Facades\Cache;

class ProfesorService
{
    private const CACHE_KEY = 'profesores';
    private const NEXT_ID_KEY = 'next_profesor_id';

    public function getAll(): array
    {
        $profesores = Cache::get(self::CACHE_KEY, []);
        return array_values($profesores);
    }

    public function getById($id): ?Profesor
    {
        $profesores = Cache::get(self::CACHE_KEY, []);
        return $profesores[$id] ?? null;
    }

    public function create(array $data): Profesor
    {
        $profesores = Cache::get(self::CACHE_KEY, []);
        $nextId = Cache::get(self::NEXT_ID_KEY, 1);

        $profesor = Profesor::fromArray($nextId, $data);
        $profesores[$nextId] = $profesor;

        Cache::put(self::CACHE_KEY, $profesores);
        Cache::put(self::NEXT_ID_KEY, $nextId + 1);

        return $profesor;
    }

    public function update($id, array $data): ?Profesor
    {
        $profesores = Cache::get(self::CACHE_KEY, []);

        if (!isset($profesores[$id])) {
            return null;
        }

        $profesor = $profesores[$id];

        if (isset($data['numeroEmpleado'])) $profesor->numeroEmpleado = $data['numeroEmpleado'];
        if (isset($data['nombres'])) $profesor->nombres = $data['nombres'];
        if (isset($data['apellidos'])) $profesor->apellidos = $data['apellidos'];
        if (isset($data['horasClase'])) $profesor->horasClase = (int) $data['horasClase'];

        $profesores[$id] = $profesor;
        Cache::put(self::CACHE_KEY, $profesores);

        return $profesor;
    }

    public function delete($id): bool
    {
        $profesores = Cache::get(self::CACHE_KEY, []);

        if (!isset($profesores[$id])) {
            return false;
        }

        unset($profesores[$id]);
        Cache::put(self::CACHE_KEY, $profesores);

        return true;
    }
}