<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'teacher_id',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    /**
     * Relationship: The teacher who created the course.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Relationship: The modules (chapters) of the course.
     */
    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    /**
     * Relationship: Enrollments for the course.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Relationship: Students enrolled in the course.
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'course_id', 'student_id');
    }

    /**
     * Relationship: Live classes scheduled for the course.
     */
    public function liveClasses()
    {
        return $this->hasMany(LiveClass::class);
    }
}
