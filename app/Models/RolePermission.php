<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RolePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'role',
        'permission',
        'is_allowed',
    ];

    protected $casts = [
        'is_allowed' => 'boolean',
    ];
}
