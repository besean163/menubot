<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\EloquentBuilder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/** 
 * @mixin EloquentBuilder 
 * @mixin QueryBuilder 
 */
class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegramName',
        'telegramId',
        'firstName',
        'lastName'
    ];
}
