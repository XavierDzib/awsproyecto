<?php

namespace App\Http\Controllers;

use App\Services\ProfesorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProfesorController extends Controller
{
    private ProfesorService $profesorService;

    public function __construct(ProfesorService $profesorService)
    {
        $this->profesorService = $profesorService;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->profesorService->getAll(), 200);
    }

    public function show($id): JsonResponse
    {
        $profesor = $this->profesorService->getById($id);

        if (!$profesor) {
            return response()->json(['error' => 'Profesor no encontrado'], 404);
        }

        return response()->json($profesor, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'sometimes|integer|min:1',
            'numeroEmpleado' => 'required|string|max:50|regex:/^[0-9]+$/',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'horasClase' => 'required|numeric|min:0|max:40',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $profesor = $this->profesorService->create($validator->validated());

        return response()->json($profesor, 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'numeroEmpleado' => 'sometimes|string|max:50|regex:/^[0-9]+$/',
            'nombres' => 'sometimes|string|max:255',
            'apellidos' => 'sometimes|string|max:255',
            'horasClase' => 'sometimes|numeric|min:0|max:40',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $profesor = $this->profesorService->update($id, $validator->validated());

        if (!$profesor) {
            return response()->json(['error' => 'Profesor no encontrado'], 404);
        }

        return response()->json($profesor, 200);
    }

    public function destroy($id): JsonResponse
    {
        $deleted = $this->profesorService->delete($id);

        if (!$deleted) {
            return response()->json(['error' => 'Profesor no encontrado'], 404);
        }

        return response()->json(['message' => 'Profesor eliminado correctamente'], 200);
    }
}