<?php

namespace App\Http\Livewire;

use App\Models\CampFire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class DeleteFire extends Modal
{
    public int|CampFire $fire;

    public function mount(CampFire $fire): void
    {
        $this->fire = $fire;
    }

    public function handle(): void
    {
        $this->fire->delete();

        $this->emit('fireDeleted');
        $this->close();
        $this->dispatchBrowserEvent('notify', '🔥 deleted successfully!');
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.delete-fire');
    }
}
