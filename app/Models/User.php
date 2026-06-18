<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // admin, teacher, student
        'is_approved',
        'is_suspended',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
            'is_suspended' => 'boolean',
        ];
    }

    /**
     * Check if user is Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is Teacher.
     */
    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /**
     * Check if user is Student.
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Relationship: Courses created by the user (as a Teacher).
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    /**
     * Relationship: Enrollments of the user (as a Student).
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    /**
     * Relationship: Courses the user is enrolled in (as a Student).
     */
    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'enrollments', 'student_id', 'course_id');
    }

    /**
     * Relationship: The class this user (student) belongs to.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    /**
     * Relationship: The login history records for this user.
     */
    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }

    /**
     * Relationship: The activity usage logs for this user.
     */
    public function usageLogs()
    {
        return $this->hasMany(UsageLog::class);
    }

    /**
     * Relationship: The user specific permission overrides.
     */
    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Relationship: The lecture progress records for this user.
     */
    public function lectureProgresses()
    {
        return $this->hasMany(LectureProgress::class);
    }

    /**
     * Relationship: The live class attendance records for this user.
     */
    public function liveClassAttendances()
    {
        return $this->hasMany(LiveClassAttendance::class);
    }

    /**
     * Relationship: Courses assigned to this user (as a teacher).
     */
    public function assignedCourses()
    {
        return $this->belongsToMany(Course::class, 'course_user', 'user_id', 'course_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Relationship: Classes assigned to this user (as a teacher).
     */
    public function assignedClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'school_class_user', 'user_id', 'school_class_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Relationship: Subjects assigned to this user (as a teacher).
     */
    public function assignedSubjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_user', 'user_id', 'subject_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Check if user is assigned to a course.
     */
    public function isAssignedToCourse(Course $course, string $role = null): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $query = $this->assignedCourses()->where('course_id', $course->id);
        if ($role) {
            $query->wherePivot('role', $role);
        }

        return $query->exists();
    }

    /**
     * Check if user is assigned to a class.
     */
    public function isAssignedToClass(SchoolClass $class, string $role = null): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $query = $this->assignedClasses()->where('school_class_id', $class->id);
        if ($role) {
            $query->wherePivot('role', $role);
        }

        return $query->exists();
    }

    /**
     * Check if user is assigned to a subject.
     */
    public function isAssignedToSubject(Subject $subject, string $role = null): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // 1. Direct assignment to the subject
        $query = $this->assignedSubjects()->where('subject_id', $subject->id);
        if ($role) {
            $query->wherePivot('role', $role);
        }
        if ($query->exists()) {
            return true;
        }

        // 2. Fallback: Course Admin of the parent course
        return $this->isAssignedToCourse($subject->course, 'course_admin');
    }

    /**
     * Check if user has permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // 1. Check user-specific override first
        $override = UserPermission::where('user_id', $this->id)
            ->where('permission', $permission)
            ->first();

        if ($override !== null) {
            return (bool) $override->is_allowed;
        }

        // 2. Fall back to role-based permission
        return RolePermission::where('role', $this->role)
            ->where('permission', $permission)
            ->where('is_allowed', true)
            ->exists();
    }
}

