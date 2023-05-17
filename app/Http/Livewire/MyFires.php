<?php

namespace App\Http\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MyFires extends Component
{
    protected $listeners = [
        'fireDeleted' => '$refresh',
        'fireUpdated' => '$refresh',
    ];

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.my-fires', [
            'fires' => auth()->user()->campFires()->orderBy('date')->get(),
        ]);
    }
}
