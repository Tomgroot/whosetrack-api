<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\GuessController;

Route::prefix('v1')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('competitions', CompetitionController::class);
    Route::apiResource('guesses', GuessController::class);
    Route::apiResource('tracks', TrackController::class);
    Route::apiResource('rounds', RoundController::class);

    Route::post('competitions/{join_code}/users/{user_id}', [CompetitionController::class, 'join']);
    Route::get('users/{user_id}/competitions', [UserController::class, 'getCompetitions']);
    Route::get('competitions/{competition_id}/{relation}', [CompetitionController::class, 'getRelation']);
    Route::post('competitions/{competition_id}/rounds', [RoundController::class, 'store']);

    Route::post('rounds/{round_id}/tracks', [TrackController::class, 'store']);
    Route::put('rounds/{round_id}/tracks', [TrackController::class, 'updateByRound']);

    Route::delete('rounds/{round_id}/users/{user_id}', [RoundController::class, 'leaveRound']);
    Route::get('rounds/{round_id}/results', [RoundController::class, 'getResults']);
    Route::get('rounds/{round_id}/{relation}', [RoundController::class, 'getRelation']);
    Route::put('rounds/{round_id}/guesses', [RoundController::class, 'readyGuesses']);

    Route::post('tracks/{track_id}/guesses', [GuessController::class, 'store']);
});
