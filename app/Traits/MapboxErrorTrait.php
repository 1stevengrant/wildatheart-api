<?php

namespace App\Traits;

trait MapboxErrorTrait
{
    public string $mapboxError = '';

    public function updatedMapboxError(): void
    {
        if (! empty($this->mapboxError)) {
            $this->dispatchBrowserEvent('notify', [
                'message' => $this->mapboxError,
                'type' => 'error',
                'timeout' => 5000,
            ]);
        }

    }
}
