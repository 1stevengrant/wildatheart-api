<?php

namespace App\Http\Livewire;

use App\Models\Campfire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SearchCampfires extends Component
{
    public string $searchQuery = '';

    public $campfires;

    public function updatedSearchQuery(): void
    {
        $this->campfires = Campfire::search($this->searchQuery)->get();
        ray($this->campfires);
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.search-campfires');
    }
}
