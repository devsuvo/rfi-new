<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoPopup extends Model
{
    protected $fillable = ['title', 'body', 'image_url', 'button_text', 'button_url', 'active'];

    protected $casts = ['active' => 'boolean'];
}
