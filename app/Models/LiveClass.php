<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LiveClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'title',
        'datetime',
        'link',
        'duration_minutes',
    ];

    protected $casts = [
        'datetime' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    /**
     * Relationship: The subject this live class is scheduled for.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
