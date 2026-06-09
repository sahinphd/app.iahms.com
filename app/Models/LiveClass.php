<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LiveClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'datetime',
        'link',
    ];

    protected $casts = [
        'datetime' => 'datetime',
    ];

    /**
     * Relationship: The course this live class is scheduled for.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
