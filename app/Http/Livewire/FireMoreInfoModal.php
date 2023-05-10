<?php

namespace App\Http\Livewire;

use App\Models\Fire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class FireMoreInfoModal extends Modal
{
    public int|Fire $fire;

    public function mount(Fire $fire): void
    {
        $this->fire = $fire;
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.fire-more-info-modal');
    }
}
