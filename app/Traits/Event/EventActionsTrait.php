<?php

namespace App\Traits\Event;

trait EventActionsTrait
{
    public function unpublish(): void
    {
        $this->approved_at = null;
        $this->save();
    }

    public function publish(): void
    {
        $this->approved_at = now();
        $this->save();
    }
}
