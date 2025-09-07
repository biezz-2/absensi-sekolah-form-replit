<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("attendance_sessions", function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid")->unique();
            $table->foreignId("class_id")->constrained("class_rooms")->cascadeOnDelete();
            $table->timestamp("start_time");
            $table->timestamp("end_time");
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("attendance_sessions");
    }
};
