<?php

namespace App\Http\Livewire;

use App\Actions\Address\CreateAddress;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SendGift extends Component
{
    public bool $differentMailingAddress = false;

    public string $mapboxPlaceName = '';

    public function handle(): void
    {
        if ($this->differentMailingAddress) {

            $this->validate([
                'mapboxPlaceName' => 'required',
            ]);

            CreateAddress::run([
                'mapbox_place_name' => $this->mapboxPlaceName,
            ]);
        }
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.send-gift')
            ->layout('layouts.base');
    }
}
