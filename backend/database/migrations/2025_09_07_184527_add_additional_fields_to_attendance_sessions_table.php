<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->string('name')->nullable()->after('uuid');
            $table->foreignId('created_by')->nullable()->constrained('users')->after('is_active');
            $table->timestamp('closed_at')->nullable()->after('created_by');
            $table->foreignId('closed_by')->nullable()->constrained('users')->after('closed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['closed_by']);
            $table->dropColumn(['name', 'created_by', 'closed_at', 'closed_by']);
        });
    }
};
