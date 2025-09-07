<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("attendance_records", function (Blueprint $table) {
            $table->id();
            $table->foreignId("student_id")->constrained("students")->cascadeOnDelete();
            $table->foreignId("attendance_session_id")->constrained("attendance_sessions")->cascadeOnDelete();
            $table->timestamp("check_in_time")->nullable();
            $table->decimal("check_in_latitude", 10, 8)->nullable();
            $table->decimal("check_in_longitude", 11, 8)->nullable();
            $table->string("check_in_ip", 45)->nullable();
            $table->boolean("is_valid")->default(true);
            $table->text("reason")->nullable();
            $table->timestamps();
            $table->unique(["student_id", "attendance_session_id"], "uniq_student_session");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("attendance_records");
    }
};
