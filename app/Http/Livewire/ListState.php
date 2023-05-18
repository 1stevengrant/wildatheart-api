<?php

namespace App\Http\Livewire;

use App\Models\Campfire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ListState extends Component
{
    public string $state;

    public $campfires;

    public function mount($state): void
    {
        $this->state = $state;
        $this->campfires = Campfire::query()->future()->where('state', '=', $state)->get();
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.list-state');
    }
}
