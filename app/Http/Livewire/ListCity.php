<?php

namespace App\Http\Livewire;

use App\Models\Campfire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ListCity extends Component
{
    public string $city;

    public $campfires;

    public function mount($city): void
    {
        $this->city = $city;
        $this->campfires = Campfire::query()->future()->where('city', '=', $city)->get();
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.list-city');
    }
}
