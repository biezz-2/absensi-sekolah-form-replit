<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassRoom;
use App\Models\AttendanceRecord;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function statistics(): JsonResponse
    {
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = ClassRoom::count();
        $totalAttendanceRecords = AttendanceRecord::count();

        return response()->json([
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_classes' => $totalClasses,
            'total_attendance_records' => $totalAttendanceRecords,
        ]);
    }
}
