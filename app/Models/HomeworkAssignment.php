<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Homework of one class set for one batch, with an optional deadline and note. */
class HomeworkAssignment extends Model
{
    protected $fillable = ['batch', 'module', 'test_slug', 'due_at', 'note', 'teacher_id'];

    protected $casts = ['due_at' => 'datetime'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function overdue(): bool
    {
        return $this->due_at !== null && $this->due_at->isPast();
    }

    /** "Due Mon 18 Nov, 9:00 PM" / "No deadline" */
    public function dueLabel(): string
    {
        return $this->due_at ? 'Due '.$this->due_at->format('D j M, g:i A') : 'No deadline';
    }
}
