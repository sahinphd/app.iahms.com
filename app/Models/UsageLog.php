<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'action',
        'details',
    ];

    /**
     * Relationship: The user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: The course where the action was performed.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
