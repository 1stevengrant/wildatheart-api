<?php

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

if (!function_exists('prettyDate')) {
    function prettyDate(string $date): string
    {
        return date('l, F jS, Y', strtotime($date));
    }
}
