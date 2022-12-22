<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObedUser extends Model
{
	use HasFactory;

	protected $fillable = [
		'userId',
		'login',
		'pass',
		'isActive'
	];
}
