<?php

namespace App\Http\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MyCampfires extends Component
{
    protected $listeners = [
        'campfireDeleted' => '$refresh',
        'campfireUpdated' => '$refresh',
    ];

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.my-campfires', [
            'campfires' => auth()->user()->campfires()->orderBy('date')->get(),
        ]);
    }
}
