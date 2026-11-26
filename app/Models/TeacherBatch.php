<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherBatch extends Model
{
    protected $fillable = ['teacher_id', 'batch'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
