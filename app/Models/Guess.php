<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guess extends Model {
    use HasFactory;

    public static $rules = [
        'user_id' => 'required|integer|exists:users,id',
        'song_id' => 'required|integer|exists:songs,id',
        'guessed_user_id' => 'nullable|integer|exists:users,id',
    ];

    public static function rules($id) {
        return self::$rules;
    }

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
