<?php

if (! function_exists('getDrupalEvents')) {
    function getDrupalEvents($filterCallback): Collection
    {
        $drupalEvents = collect(Http::get('https://wildatheart.org/json/allies')->json());

        return $drupalEvents->filter($filterCallback);
    }
}

// Grouped Routes
Route::prefix('drupal-events')->group(function () {
    Route::get('/', function () {
        return getDrupalEvents(function ($experience) {
            return true; // No filtering, return all events
        });
    });

    Route::get('/past', function () {
        return getDrupalEvents(function ($experience) {
            return $experience['start_date'] < now()->format('Y-m-d');
        });
    });

    Route::get('/upcoming', function () {
        return getDrupalEvents(function ($experience) {
            return $experience['start_date'] > now()->format('Y-m-d');
        });
    });

    Route::get('/null', function () {
        return getDrupalEvents(function ($experience) {
            return $experience['first_name'] === null || $experience['last_name'] === null;
        });
    });
});
