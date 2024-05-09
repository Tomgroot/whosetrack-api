<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model {
    use HasFactory;

    public static $rules = [
        'competition_id' => 'required|integer|exists:competitions,id',
    ];

    public static function rules($id) {
        return self::$rules;
    }

    public function competition() {
        return $this->belongsTo(Competition::class);
    }

    public function songs() {
        return $this->hasMany(Song::class);
    }
}
