<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model {
    use HasFactory;

    public static $rules = [
        'name' => 'string',
        'created_by' => 'required|integer|exists:users,id',
    ];

    protected $fillable = [
        'name',
        'join_code',
        'joinable',
        'created_by',
    ];

    protected $with = [
        'users',
        'creator',
    ];

    protected $appends = [
        'most_recent_round',
    ];

    protected $casts = [
        'created_by' => 'integer',
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
        return $this->hasMany(Round::class)->orderBy('created_at', 'desc');
    }

    public function getMostRecentRoundAttribute() {
        return $this->mostRecentRound();
    }

    public function mostRecentRound() {
        return $this->rounds()->first();
    }
}
