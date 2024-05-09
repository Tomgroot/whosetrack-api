<?php

namespace App\Http\Controllers;

use App\Models\Guess;

class GuessController extends Controller {

    public function __construct() {
        self::$entity = Guess::class;
    }
}
