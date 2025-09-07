<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassRoom extends Model
{
	use HasFactory;

	protected $fillable = [
		'teacher_id',
		'name',
		'description',
		'location_latitude',
		'location_longitude',
	];

	public function teacher(): BelongsTo
	{
		return $this->belongsTo(Teacher::class);
	}

	public function students(): HasMany
	{
		return $this->hasMany(Student::class, 'class_id');
	}

	public function sessions(): HasMany
	{
		return $this->hasMany(AttendanceSession::class, 'class_id');
	}
}
