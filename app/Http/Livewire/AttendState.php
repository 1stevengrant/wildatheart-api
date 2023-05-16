<?php

namespace App\Http\Livewire;

use App\Models\CampFire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AttendState extends Component
{
    public string $state;

    public $fires;

    public function mount($state): void
    {
        $this->state = $state;
        $this->fires = CampFire::query()->future()->where('state', '=', $state)->get();
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.attend-state');
    }
}
