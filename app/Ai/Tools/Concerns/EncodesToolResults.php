<?php

namespace App\Ai\Tools\Concerns;

trait EncodesToolResults
{
    /**
     * @param  array<array-key, mixed>  $result
     */
    protected function encode(array $result): string
    {
        return json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
