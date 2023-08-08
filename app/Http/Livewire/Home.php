<?php

namespace App\Http\Livewire;

use App\Traits\LocationTrait;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Home extends Component
{
    use LocationTrait;

    public function mount(): void
    {
        $this->getLatitudeAndLongitude();
        SEOTools::setTitle('Home');
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.home');
    }
}
