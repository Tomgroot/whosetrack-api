<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guess extends Model
{
    use HasFactory;

    public function song() {
        return $this->belongsTo(Song::class);
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function guessedUser() {
        return $this->belongsTo(User::class, 'guessed_user_id');
    }
}
