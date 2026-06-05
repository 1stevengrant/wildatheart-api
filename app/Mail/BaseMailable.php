<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

abstract class BaseMailable extends Mailable
{
    public $queue = 'mail';

    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }
}
