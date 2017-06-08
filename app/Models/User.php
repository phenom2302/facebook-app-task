<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class User extends Model
{
    protected $table = 'users';
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'profile_url',
        'token',
        'is_active'
    ];

}