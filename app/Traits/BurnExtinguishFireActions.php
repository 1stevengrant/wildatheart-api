<?php

namespace App\Traits;

trait BurnExtinguishFireActions
{
    public string $extinguishLink;

    public string $burnLink;

    protected function getInactiveButtonComponent(): string
    {
        return view('emails.components.button', [
            'url' => $this->extinguishLink,
            'text' => 'Extinguish My Fire',
            'color' => 'red',
        ])->render();
    }

    protected function getActiveButtonComponent(): string
    {
        return view('emails.components.button', [
            'url' => $this->burnLink,
            'text' => 'Keep My Fire Burning',
            'color' => 'green',
        ])->render();
    }
}
