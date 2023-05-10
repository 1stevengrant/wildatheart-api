<?php

namespace App\Http\Livewire;

use App\Models\Fire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AttendFire extends Component
{
    protected $listeners = [
        'registeredForFire' => '$refresh',
        'fireDeleted' => '$refresh',
    ];

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $fires = Fire::query()->future()->orderBy('state')->orderBy('date')->get();

        return view('livewire.attend-fire', [
            'fires' => $fires->groupBy('state'),
            'states' => $fires->pluck('state')->unique(),
        ]);
    }
}
