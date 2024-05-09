<?php

namespace App\Http\Controllers;

use App\Models\Round;

class RoundController extends Controller {

    public function __construct() {
        self::$entity = Round::class;
    }
}
