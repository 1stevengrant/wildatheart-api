<?php

namespace App\Http\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MyAddresses extends Component
{
    protected $listeners = [
        'addressAdded' => '$refresh',
        'addressUpdated' => '$refresh',
        'addressDeleted' => '$refresh',
    ];

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.my-addresses', [
            'addresses' => auth()->user()->addresses,
        ]);
    }
}
