<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\Round;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackController extends Controller {

    public function __construct() {
        self::$entity = Track::class;
    }

    public function update(Request $request, $id): JsonResponse {
        $track = Track::findOrFail($id);
        $rules = Track::rules($id);
        $validated = $request->validate($rules);

        $track->update($validated);
        return response()->json($track);
    }

    public function store(Request $request): JsonResponse {
        $round_id = $request->route('round_id') ?? $request->get('round_id');

        $validator = Validator::make([
            'round_id' => $round_id,
            'user_id' => $request->get('user_id'),
            'spotify_url' => $request->get('spotify_url'),
            'ready' => false
        ], Track::$rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = [
            'round_id' => intval($round_id),
            'user_id' => $request->get('user_id'),
            'spotify_url' => $request->get('spotify_url'),
            'ready' => false
        ];

        if (!is_null($ready = $request->get('ready'))) {
            $data['ready'] = $ready;
        }

        $track = Track::create($data);

        $track->guesses = [];

        return response()->json($track, 201);
    }

    public function updateByRound(Request $request): JsonResponse {
        $round = Round::findOrFail($request->route('round_id'));
        $track = $round->tracks()->where('user_id', $request->user_id)->first();

        // TODO: make sure validation rules accept empty spotify url and add validation here
        $track->update(["ready" => $request->ready]);

        return response()->json($track);
    }
}
