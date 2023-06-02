<?php

use App\Models\State;
use Creativeorange\Gravatar\Facades\Gravatar;

if (! function_exists('generateTimeList')) {
    function generateTimeList(): array
    {
        $times = [];
        $startTime = strtotime('00:00:00');
        $endTime = strtotime('23:45:00');

        while ($startTime <= $endTime) {
            $times[] = date('H:i:s', $startTime);
            $startTime += 900;
        }

        return $times;
    }
}

if (! function_exists('prettyDate')) {
    function prettyDate(string $date): string
    {
        return date('l, F jS, Y', strtotime($date));
    }
}

if (! function_exists('prettyTime')) {
    function prettyTime(string $time): string
    {
        return date('g:i A', strtotime($time));
    }
}

if (! function_exists('getStateAbbreviation')) {
    function getStateAbbreviation(string|null $stateName = null): ?string
    {
        $stateAbbreviations = State::all()->pluck('code', 'name')->toArray();

        $stateName = ucwords(strtolower($stateName));
        if (array_key_exists($stateName, $stateAbbreviations)) {
            return $stateAbbreviations[$stateName];
        }

        return null;
    }
}

if (! function_exists('getStateName')) {
    function getStateName(string|null $stateCode = null): string
    {
        $stateNames = State::all()->pluck('name', 'code')->toArray();

        return $stateNames[$stateCode];
    }
}

if (! function_exists('gravatar')) {
    function gravatar(string $email, string $transform = null): string
    {
        return Gravatar::get($email, $transform);
    }
}
