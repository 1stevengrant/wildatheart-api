<?php

namespace App\Http\Livewire;

use App\Models\CampFire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class FireMoreInfoModal extends Modal
{
    public string|CampFire $fire;

    public function mount(CampFire $fire): void
    {
        $this->fire = $fire;
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.fire-more-info-modal');
    }
}
