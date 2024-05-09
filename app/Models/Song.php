<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model {
    use HasFactory;

    public static $rules = [
        'user_id' => 'required|integer|exists:users,id',
        'round_id' => 'required|integer|exists:rounds,id',
        'spotify_url' => 'required|url|starts_with:https://open.spotify.com/',
    ];

    public static function rules($id) {
        return self::$rules;
    }

    public function round() {
        return $this->belongsTo(Round::class);
    }

    public function guesses() {
        return $this->hasMany(Guess::class);
    }
}
