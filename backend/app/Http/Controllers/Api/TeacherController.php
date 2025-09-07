<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeacherController extends Controller
{
    public function index(): JsonResponse
    {
        $teachers = Teacher::with('user')->get();
        return response()->json($teachers);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'teacher_id_number' => 'required|string|unique:teachers,teacher_id_number',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt('password123'),
            'user_type' => 'teacher',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'teacher_id_number' => $data['teacher_id_number'],
        ]);

        return response()->json($teacher->load('user'), 201);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        return response()->json($teacher->load('user'));
    }

    public function update(Request $request, Teacher $teacher): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $teacher->user_id,
            'teacher_id_number' => 'required|string|unique:teachers,teacher_id_number,' . $teacher->id,
        ]);

        $teacher->user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $teacher->update([
            'teacher_id_number' => $data['teacher_id_number'],
        ]);

        return response()->json($teacher->load('user'));
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        $teacher->user->delete();
        $teacher->delete();
        return response()->json(['message' => 'Teacher deleted successfully']);
    }
}