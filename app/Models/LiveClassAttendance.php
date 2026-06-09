<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveClassAttendance extends Model
{
    use HasFactory;

    protected $table = 'live_class_attendance';

    protected $fillable = [
        'user_id',
        'live_class_id',
        'attended_at',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
    ];

    /**
     * Relationship: The student who attended the class.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: The live class that was attended.
     */
    public function liveClass()
    {
        return $this->belongsTo(LiveClass::class);
    }
}
