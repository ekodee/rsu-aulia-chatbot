<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    protected $guarded = [];

    // Satu sesi punya banyak pesan
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    // Sesi ini milik satu user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
