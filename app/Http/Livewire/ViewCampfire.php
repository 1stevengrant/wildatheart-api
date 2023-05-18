<?php

namespace App\Http\Livewire;

use App\Models\Campfire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ViewCampfire extends Component
{
    public Campfire $campfire;

    public function mount(Campfire $campfire): void
    {
        $this->campfire = $campfire;
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.view-campfire');
    }
}
