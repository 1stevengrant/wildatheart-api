<?php

namespace App\Http\Livewire;

use Artesaos\SEOTools\Facades\SEOTools;
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

    public function mount()
    {
        SEOTools::setTitle('My Addresses');
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.my-addresses', [
            'addresses' => auth()->user()->addresses,
        ]);
    }
}
