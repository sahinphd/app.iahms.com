<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassNote extends Model
{
    protected $table = 'class_notes';

    protected $fillable = [
        'teacher_id',
        'school_class_id',
        'title',
        'content'
    ];

    /**
     * Relationship: The teacher who wrote this note.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Relationship: The class this note is sent to.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }
}
