<?php

namespace App\Http\Livewire;

use App\Models\CampFire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AttendFire extends Component
{
    protected $listeners = [
        'registeredForFire' => '$refresh',
        'fireDeleted' => '$refresh',
        'fireUpdated' => '$refresh',
    ];

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $fires = CampFire::query()->future()->orderBy('state')->orderBy('date')->get();

        return view('livewire.attend-fire', [
            'fires' => $fires->groupBy('state'),
            'states' => $fires->pluck('state')->unique(),
        ]);
    }
}
