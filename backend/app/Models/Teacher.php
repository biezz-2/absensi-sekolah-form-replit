<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'teacher_id_number',
	];

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function classRooms(): HasMany
	{
		return $this->hasMany(ClassRoom::class, 'teacher_id');
	}
}
