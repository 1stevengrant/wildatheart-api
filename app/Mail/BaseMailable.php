<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use App\Traits\LogsFailedDelivery;

abstract class BaseMailable extends Mailable
{
    use LogsFailedDelivery;

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
