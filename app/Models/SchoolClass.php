<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Relationship: The students in this class.
     */
    public function students()
    {
        return $this->hasMany(User::class, 'school_class_id');
    }

    /**
     * Relationship: The courses assigned to this class.
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'school_class_id');
    }

    /**
     * Relationship: The teachers assigned to this class.
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'school_class_user', 'school_class_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Relationship: The announcements/notices for this class.
     */
    public function classNotes()
    {
        return $this->hasMany(ClassNote::class, 'school_class_id');
    }
}
