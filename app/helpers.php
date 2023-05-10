<?php

if (! function_exists('generateTimeList')) {
    function generateTimeList(): array
    {
        $times = [];
        $startTime = strtotime('00:00');
        $endTime = strtotime('23:45');

        while ($startTime <= $endTime) {
            $times[] = date('H:i', $startTime);
            $startTime += 900;
        }

        return $times;
    }
}
