<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory;

    public function competition() {
        return $this->belongsTo(Competition::class);
    }

    public function songs() {
        return $this->hasMany(Song::class);
    }
}
