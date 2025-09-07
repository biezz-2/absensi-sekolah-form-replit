<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
        ]);

        // Create teachers
        $teacher1User = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@teacher.com',
            'password' => bcrypt('password'),
            'user_type' => 'teacher',
        ]);

        $teacher1 = Teacher::create([
            'user_id' => $teacher1User->id,
            'teacher_id_number' => 'T001',
        ]);

        $teacher2User = User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@teacher.com',
            'password' => bcrypt('password'),
            'user_type' => 'teacher',
        ]);

        $teacher2 = Teacher::create([
            'user_id' => $teacher2User->id,
            'teacher_id_number' => 'T002',
        ]);

        // Create classes
        $class1 = ClassRoom::create([
            'name' => 'Kelas 10A',
            'teacher_id' => $teacher1->id,
            'location_latitude' => -6.2088,
            'location_longitude' => 106.8456,
        ]);

        $class2 = ClassRoom::create([
            'name' => 'Kelas 10B',
            'teacher_id' => $teacher2->id,
            'location_latitude' => -6.2089,
            'location_longitude' => 106.8457,
        ]);

        // Create students
        $students = [
            ['name' => 'Ahmad Rizki', 'email' => 'ahmad@student.com', 'student_id' => 'S001', 'class_id' => $class1->id],
            ['name' => 'Dewi Sari', 'email' => 'dewi@student.com', 'student_id' => 'S002', 'class_id' => $class1->id],
            ['name' => 'Reza Pratama', 'email' => 'reza@student.com', 'student_id' => 'S003', 'class_id' => $class1->id],
            ['name' => 'Maya Sari', 'email' => 'maya@student.com', 'student_id' => 'S004', 'class_id' => $class2->id],
            ['name' => 'Andi Wijaya', 'email' => 'andi@student.com', 'student_id' => 'S005', 'class_id' => $class2->id],
        ];

        foreach ($students as $studentData) {
            $user = User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => bcrypt('password'),
                'user_type' => 'student',
            ]);

            Student::create([
                'user_id' => $user->id,
                'student_id' => $studentData['student_id'],
                'class_id' => $studentData['class_id'],
            ]);
        }

        // Create attendance sessions
        $session1 = AttendanceSession::create([
            'uuid' => 'session-uuid-1',
            'class_id' => $class1->id,
            'start_time' => now()->subHours(2),
            'end_time' => now()->addHours(1),
            'is_active' => true,
        ]);

        $session2 = AttendanceSession::create([
            'uuid' => 'session-uuid-2',
            'class_id' => $class2->id,
            'start_time' => now()->subHours(1),
            'end_time' => now()->addHours(2),
            'is_active' => true,
        ]);

        // Create some attendance records
        $students = Student::all();
        foreach ($students as $student) {
            if (rand(0, 1)) { // 50% chance to have attendance record
                AttendanceRecord::create([
                    'student_id' => $student->id,
                    'attendance_session_id' => $student->class_id == $class1->id ? $session1->id : $session2->id,
                    'check_in_time' => now()->subMinutes(rand(10, 120)),
                    'check_in_latitude' => -6.2088 + (rand(-10, 10) / 10000),
                    'check_in_longitude' => 106.8456 + (rand(-10, 10) / 10000),
                    'check_in_ip' => '127.0.0.1',
                    'is_valid' => rand(0, 1),
                    'reason' => rand(0, 1) ? null : 'outside_radius',
                ]);
            }
        }
    }
}