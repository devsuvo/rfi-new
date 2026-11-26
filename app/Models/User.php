<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * role: admin | teacher | student
 * batch: the student's batch (lower-case); teachers get batches through teacher_batches.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = ['admin' => 'Admin', 'teacher' => 'Teacher', 'student' => 'Student'];

    public const STUDENT_ACCESS_MONTHS = 7;

    /** A student or teacher belongs to one course; admins see both. */
    public const COURSES = ['reading' => 'Reading for IELTS', 'writing' => 'Writing for IELTS'];

    protected $fillable = ['name', 'email', 'password', 'role', 'course', 'phone', 'batch', 'access_until', 'auto_password'];

    protected $hidden = ['password', 'remember_token'];

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            $user->batch = self::normalizeBatch($user->batch) ?: null;
            if ($user->role === 'admin') {
                $user->course = null;
            } elseif (! isset(self::COURSES[$user->course])) {
                $user->course = 'reading';
            }
            if ($user->role !== 'student') {
                $user->batch = null;
                $user->access_until = null;
            } elseif (! $user->access_until) {
                // a new student gets 7 months of access from the day they are added
                $user->access_until = ($user->created_at ?? now())->copy()->addMonths(self::STUDENT_ACCESS_MONTHS);
            }
        });
    }

    public static function normalizeBatch(?string $batch): string
    {
        return trim(mb_strtolower($batch ?? ''));
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isTeacher();
    }

    /** reading / writing for a student or teacher, null for an admin (sees both). */
    public function courseKey(): ?string
    {
        return $this->isAdmin() ? null : (isset(self::COURSES[$this->course]) ? $this->course : 'reading');
    }

    /** May this person use the Reading / Writing (or any other) part of the site? */
    public function canUseCourse(string $module): bool
    {
        return $this->isAdmin() || $module === $this->courseKey();
    }

    public function accessExpired(): bool
    {
        return $this->isStudent() && $this->access_until && $this->access_until->isPast();
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    /** Teacher → batches they look after. */
    public function batches()
    {
        return $this->hasMany(TeacherBatch::class, 'teacher_id');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'access_until' => 'datetime',
            'password' => 'hashed',
            'auto_password' => 'boolean',
        ];
    }
}
