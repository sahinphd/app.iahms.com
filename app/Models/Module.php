<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
    ];

    /**
     * Relationship: The course this module belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relationship: Lectures in this module.
     */
    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }

    /**
     * Relationship: Study materials in this module.
     */
    public function materials()
    {
        return $this->hasMany(Material::class);
    }
}
