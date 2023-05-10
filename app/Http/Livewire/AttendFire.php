<?php

namespace App\Http\Livewire;

use App\Models\Fire;
use Livewire\Component;

class AttendFire extends Component
{
    public function render()
    {
        return view('livewire.attend-fire', [
            'fires' => Fire::future()->orderBy('state')->orderBy('date')->get()->groupBy('state'),
            'states' => Fire::future()->orderBy('state')->orderBy('date')->get()->pluck('state')->unique(),
        ]);
    }
}
