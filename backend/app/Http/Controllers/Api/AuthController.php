<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Get user profile based on user type
        $profile = null;
        if ($user->user_type === 'student') {
            $profile = Student::where('user_id', $user->id)->with('class')->first();
        } elseif ($user->user_type === 'teacher') {
            $profile = Teacher::where('user_id', $user->id)->first();
        }

        $token = $user->createToken('auth-token', [$user->user_type])->plainTextToken;

        return response()->json([
            'user' => $user,
            'profile' => $profile,
            'token' => $token,
            'user_type' => $user->user_type,
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'user_type' => 'required|in:student,teacher,admin',
            'student_id_number' => 'required_if:user_type,student|string|unique:students,student_id_number',
            'teacher_id_number' => 'required_if:user_type,teacher|string|unique:teachers,teacher_id_number',
            'class_id' => 'required_if:user_type,student|exists:class_rooms,id',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'user_type' => $data['user_type'],
        ]);

        // Create profile based on user type
        $profile = null;
        if ($data['user_type'] === 'student') {
            $profile = Student::create([
                'user_id' => $user->id,
                'student_id_number' => $data['student_id_number'],
                'class_id' => $data['class_id'],
            ]);
            $profile->load('class');
        } elseif ($data['user_type'] === 'teacher') {
            $profile = Teacher::create([
                'user_id' => $user->id,
                'teacher_id_number' => $data['teacher_id_number'],
            ]);
        }

        $token = $user->createToken('auth-token', [$user->user_type])->plainTextToken;

        return response()->json([
            'user' => $user,
            'profile' => $profile,
            'token' => $token,
            'user_type' => $user->user_type,
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $profile = null;
        if ($user->user_type === 'student') {
            $profile = Student::where('user_id', $user->id)->with('class')->first();
        } elseif ($user->user_type === 'teacher') {
            $profile = Teacher::where('user_id', $user->id)->first();
        }

        return response()->json([
            'user' => $user,
            'profile' => $profile,
            'user_type' => $user->user_type,
        ]);
    }
}