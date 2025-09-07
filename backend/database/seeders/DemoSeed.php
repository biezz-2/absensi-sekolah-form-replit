<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeed extends Seeder
{
	public function run(): void
	{
		$admin = User::firstOrCreate(
			['email' => 'admin@example.com'],
			[
				'name' => 'Attabi',
				'password' => Hash::make('password'),
				'user_type' => 'admin',
			]
		);

		$teacherUser = User::firstOrCreate(
			['email' => 'risna@example.com'],
			[
				'name' => 'Risna Citra',
				'password' => Hash::make('password'),
				'user_type' => 'teacher',
			]
		);
		$teacher = Teacher::firstOrCreate(
			['user_id' => $teacherUser->id],
			['teacher_id_number' => 'T-001']
		);

		$class = ClassRoom::firstOrCreate(
			['name' => 'Kelas 10 A', 'teacher_id' => $teacher->id],
			[
				'description' => 'Demo class',
				'location_latitude' => -6.2,
				'location_longitude' => 106.816666,
			]
		);

		$studentUser = User::firstOrCreate(
			['email' => 'omar@example.com'],
			[
				'name' => 'Omar Fassya',
				'password' => Hash::make('password'),
				'user_type' => 'student',
			]
		);
		Student::firstOrCreate(
			['user_id' => $studentUser->id],
			['student_id_number' => 'S-001', 'class_id' => $class->id]
		);
	}
}
