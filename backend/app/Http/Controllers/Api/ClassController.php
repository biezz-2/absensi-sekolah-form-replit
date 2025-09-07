<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClassController extends Controller
{
    public function index(): JsonResponse
    {
        $classes = ClassRoom::with('teacher')->get();
        return response()->json($classes);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'teacher_id' => 'required|exists:teachers,id',
            'location_latitude' => 'required|numeric|between:-90,90',
            'location_longitude' => 'required|numeric|between:-180,180',
        ]);

        $class = ClassRoom::create($data);

        return response()->json($class->load('teacher'), 201);
    }

    public function show(ClassRoom $class): JsonResponse
    {
        return response()->json($class->load('teacher'));
    }

    public function update(Request $request, ClassRoom $class): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'teacher_id' => 'required|exists:teachers,id',
            'location_latitude' => 'required|numeric|between:-90,90',
            'location_longitude' => 'required|numeric|between:-180,180',
        ]);

        $class->update($data);

        return response()->json($class->load('teacher'));
    }

    public function destroy(ClassRoom $class): JsonResponse
    {
        $class->delete();
        return response()->json(['message' => 'Class deleted successfully']);
    }
}