<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\GuessController;

Route::prefix('v1')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('competitions', CompetitionController::class);
    Route::apiResource('guesses', GuessController::class);
    Route::apiResource('songs', SongController::class);
    Route::apiResource('rounds', RoundController::class);

    Route::post('competitions/{join_code}/users/{user_id}', [CompetitionController::class, 'join']);
    Route::get('users/{user_id}/competitions', [UserController::class, 'getCompetitions']);
});
