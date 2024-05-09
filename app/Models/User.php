<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nickname',
        'spotify_guid',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    public static $rules = [
        'nickname' => 'required|string',
        'spotify_guid' => 'string|unique:users,spotify_guid'
    ];

    public static function rules($id) {
        $rules = self::$rules;
        $rules['spotify_guid'] .= ',' . $id;
        return $rules;
    }

    public function competitions() {
        return $this->belongsToMany(Competition::class)->orderBy('created_at', 'desc');
    }
}
