<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A class opened for one batch. No row means the class is locked for that batch. */
class ClassUnlock extends Model
{
    protected $fillable = ['batch', 'module', 'test_slug', 'teacher_id', 'answers_shown_at'];

    protected $casts = ['answers_shown_at' => 'datetime'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
