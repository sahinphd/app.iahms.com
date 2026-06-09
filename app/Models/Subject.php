<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'duration',
    ];

    /**
     * Relationship: The course this subject belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Relationship: Modules under this subject.
     */
    public function modules()
    {
        return $this->hasMany(Module::class, 'subject_id');
    }

    /**
     * Relationship: Live classes under this subject.
     */
    public function liveClasses()
    {
        return $this->hasMany(LiveClass::class, 'subject_id');
    }

    /**
     * Relationship: Teachers assigned to this subject.
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'subject_user', 'subject_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }
}
