<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
	use HasFactory;

	protected $fillable = [
		'uuid',
		'class_id',
		'start_time',
		'end_time',
		'is_active',
	];

	protected $casts = [
		'is_active' => 'boolean',
		'start_time' => 'datetime',
		'end_time' => 'datetime',
	];

	public function classRoom(): BelongsTo
	{
		return $this->belongsTo(ClassRoom::class, 'class_id');
	}

	public function attendanceRecords(): HasMany
	{
		return $this->hasMany(AttendanceRecord::class, 'attendance_session_id');
	}
}
