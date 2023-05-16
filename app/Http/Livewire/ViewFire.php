<?php

namespace App\Http\Livewire;

use App\Models\CampFire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ViewFire extends Component
{
    public CampFire $fire;

    public function mount(CampFire $campFire): void
    {
        $this->fire = $campFire;
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.view-fire');
    }
}
