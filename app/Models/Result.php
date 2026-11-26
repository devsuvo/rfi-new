<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = ['user_id', 'module', 'test_slug', 'kind', 'part', 'late', 'answers', 'score', 'total', 'band', 'feedback', 'marked_by', 'feedback_sent_at'];

    protected $casts = ['late' => 'boolean', 'answers' => 'array', 'feedback_sent_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function testTitle(): string
    {
        return \App\Support\Tests::title($this->module, $this->test_slug);
    }
}
