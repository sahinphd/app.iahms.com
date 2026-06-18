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
        'school_class_id',
        'is_completed',
        'duration',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_completed' => 'boolean',
        'school_class_id' => 'integer',
    ];

    /**
     * Relationship: The teacher who created the course.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Relationship: The subjects of the course.
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'course_id');
    }

    /**
     * Relationship: The modules (chapters) of the course through subjects.
     */
    public function modules()
    {
        return $this->hasManyThrough(Module::class, Subject::class);
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
     * Relationship: Live classes scheduled for the course through subjects.
     */
    public function liveClasses()
    {
        return $this->hasManyThrough(LiveClass::class, Subject::class);
    }

    /**
     * Relationship: Teachers assigned to this course.
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Relationship: The class this course belongs to.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }
}
