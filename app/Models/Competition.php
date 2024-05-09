<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model {
    use HasFactory;

    public static $rules = [
        'user_id' => 'required|integer|exists:users,id',
    ];

    protected $fillable = [
        'join_code',
        'joinable',
        'created_by',
    ];

    public static function rules($id) {
        return [];
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users() {
        return $this->belongsToMany(User::class);
    }

    public function rounds() {
        return $this->hasMany(Round::class);
    }
}
