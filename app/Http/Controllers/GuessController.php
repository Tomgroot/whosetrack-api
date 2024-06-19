<?php

namespace App\Http\Controllers;

use App\Models\Guess;
use App\Models\Round;
use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GuessController extends Controller {

    public function __construct() {
        self::$entity = Guess::class;
    }

    public function updateByTrack(Request $request): JsonResponse {
        $track_id = $request->route('track_id') ?? $request->get('track_id');

        $validator = Validator::make([
            'track_id' => $track_id,
            'user_id' => $request->get('user_id'),
            'guessed_user_id' => $request->get('guessed_user_id'),
        ], Guess::$rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $guess = Guess::where('track_id', $track_id)->where('user_id', $request->get('user_id'))->first();

        if($guess != null){
            $guess->guessed_user_id = $request->get('guessed_user_id');
            $guess->save();
        }

        return response()->json($guess, 201);
    }

    public function store(Request $request): JsonResponse {
        $track_id = $request->route('track_id') ?? $request->get('track_id');

        $validator = Validator::make([
            'track_id' => $track_id,
            'user_id' => $request->get('user_id'),
            'guessed_user_id' => $request->get('guessed_user_id'),
        ], Guess::$rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $guess = Guess::create([
            'track_id' => intval($track_id),
            'user_id' => $request->get('user_id'),
            'guessed_user_id' => $request->get('guessed_user_id'),
        ]);

        return response()->json($guess, 201);
    }
}
