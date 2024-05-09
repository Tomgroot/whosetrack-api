<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackController extends Controller {

    public function __construct() {
        self::$entity = Track::class;
    }

    public function store(Request $request): JsonResponse {
        $round_id = $request->route('round_id') ?? $request->get('round_id');

        $validator = Validator::make([
            'round_id' => $round_id,
            'user_id' => $request->get('user_id'),
            'spotify_url' => $request->get('spotify_url'),
        ], Track::$rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $track = Track::create([
            'round_id' => $round_id,
            'user_id' => $request->get('user_id'),
            'spotify_url' => $request->get('spotify_url'),
        ]);

        $track->round->updateStatus();

        return response()->json($track, 201);
    }
}
