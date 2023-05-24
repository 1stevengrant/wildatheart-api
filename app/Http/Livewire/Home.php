<?php

namespace App\Http\Livewire;

use App\Models\Campfire;
use GeoIp2\Database\Reader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Home extends Component
{
    public $latitude;
    public $longitude;
    public int $campfireCount;

    public function mount(): void
    {
        $this->getLatitudeAndLongitude();
        $this->campfireCount = Campfire::count();
    }

    public function getLatitudeAndLongitude(): void
    {
        $cityDbReader = new Reader(resource_path('location/GeoLite2-City.mmdb'));
        $record = $cityDbReader->city(config('app.env') === 'local' ? '81.104.2.147' : request()->ip());
        $this->latitude = $record->location->latitude;
        $this->longitude = $record->location->longitude;
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.home');
    }
}
