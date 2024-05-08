<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model {
    use HasFactory;

    public function users() {
        return $this->belongsToMany(User::class, 'competition_users');
    }

    public function rounds() {
        return $this->hasMany(Round::class);
    }
}
