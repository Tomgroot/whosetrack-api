<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    public function round() {
        return $this->belongsTo(Round::class);
    }

    public function guesses() {
        return $this->hasMany(Guess::class);
    }
}
