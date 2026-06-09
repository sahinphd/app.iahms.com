<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permissions';

    protected $fillable = [
        'user_id',
        'permission',
        'is_allowed',
    ];

    protected $casts = [
        'is_allowed' => 'boolean',
    ];

    /**
     * Relationship: The user this override belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
