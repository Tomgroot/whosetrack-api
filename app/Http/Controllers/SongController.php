<?php

namespace App\Http\Controllers;

use App\Models\Song;

class SongController extends Controller {

    public function __construct() {
        self::$entity = Song::class;
    }
}
