<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("class_rooms", function (Blueprint $table) {
            $table->id();
            $table->foreignId("teacher_id")->constrained("teachers")->cascadeOnDelete();
            $table->string("name");
            $table->text("description")->nullable();
            $table->decimal("location_latitude", 10, 8)->nullable();
            $table->decimal("location_longitude", 11, 8)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("class_rooms");
    }
};
