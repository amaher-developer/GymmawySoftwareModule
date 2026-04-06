<?php

// ── Loyalty points ─────────────────────────────────────────────────────────
Route::prefix('loyalty')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::post('', 'Api\GymAppFeaturesApiController@loyaltyPoints');
    });

// ── Trainers ───────────────────────────────────────────────────────────────
Route::prefix('trainers')
    ->middleware(['api'])
    ->group(function () {
        Route::post('', 'Api\GymAppFeaturesApiController@trainers');
        Route::post('/{id}', 'Api\GymAppFeaturesApiController@trainer');
    });

// ── Suggestions & Complaints ───────────────────────────────────────────────
Route::prefix('suggestion')
    ->middleware(['api'])
    ->group(function () {
        Route::post('', 'Api\GymAppFeaturesApiController@storeSuggestion');
    });

// ── About / Terms / Policy (JSON) ─────────────────────────────────────────
Route::prefix('info')
    ->middleware(['api'])
    ->group(function () {
        Route::post('about', 'Api\GymAppFeaturesApiController@about');
        Route::post('terms', 'Api\GymAppFeaturesApiController@terms');
        Route::post('policy', 'Api\GymAppFeaturesApiController@policy');
    });
