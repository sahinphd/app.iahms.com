<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LectureProgress extends Model
{
    use HasFactory;

    protected $table = 'lecture_progress';

    protected $fillable = [
        'user_id',
        'lecture_id',
        'seconds_watched',
        'is_completed',
        'last_watched_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'last_watched_at' => 'datetime',
    ];

    /**
     * Relationship: The student this progress belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: The video lecture this progress tracks.
     */
    public function lecture()
    {
        return $this->belongsTo(Lecture::class);
    }
}
