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
}