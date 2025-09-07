<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    public function index(): JsonResponse
    {
        $students = Student::with(['user', 'class'])->get();
        return response()->json($students);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'student_id' => 'required|string|unique:students,student_id',
            'class_id' => 'required|exists:class_rooms,id',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt('password123'),
            'user_type' => 'student',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'student_id' => $data['student_id'],
            'class_id' => $data['class_id'],
        ]);

        return response()->json($student->load(['user', 'class']), 201);
    }

    public function show(Student $student): JsonResponse
    {
        return response()->json($student->load(['user', 'class']));
    }

    public function update(Request $request, Student $student): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $student->user_id,
            'student_id' => 'required|string|unique:students,student_id,' . $student->id,
            'class_id' => 'required|exists:class_rooms,id',
        ]);

        $student->user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $student->update([
            'student_id' => $data['student_id'],
            'class_id' => $data['class_id'],
        ]);

        return response()->json($student->load(['user', 'class']));
    }

    public function destroy(Student $student): JsonResponse
    {
        $student->user->delete();
        $student->delete();
        return response()->json(['message' => 'Student deleted successfully']);
    }
}