<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
	use HasFactory;

	protected $fillable = [
		'student_id',
		'attendance_session_id',
		'check_in_time',
		'check_in_latitude',
		'check_in_longitude',
		'check_in_ip',
		'is_valid',
		'reason',
	];

	protected $casts = [
		'check_in_time' => 'datetime',
		'is_valid' => 'boolean',
	];

	public function student(): BelongsTo
	{
		return $this->belongsTo(Student::class);
	}

	public function session(): BelongsTo
	{
		return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
	}
}
