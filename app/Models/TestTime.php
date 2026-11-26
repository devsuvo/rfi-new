<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestTime extends Model
{
    protected $fillable = ['batch', 'module', 'test_slug', 'part', 'minutes', 'teacher_id'];
}
