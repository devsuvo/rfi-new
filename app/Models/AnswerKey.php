<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnswerKey extends Model
{
    protected $fillable = ['module', 'test_slug', 'test_name', 'answers'];

    protected $casts = ['answers' => 'array'];
}
