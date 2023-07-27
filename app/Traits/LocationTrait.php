<?php

namespace App\Traits;

use GeoIp2\Database\Reader;

trait LocationTrait
{
    public $latitude;

    public $longitude;

    public function getLatitudeAndLongitude(): void
    {
        $cityDbReader = new Reader(resource_path('location/GeoLite2-City.mmdb'));
        $record = $cityDbReader->city(! app()->isProduction() ? '148.72.164.216' : request()->ip());
        $this->latitude = $record->location->latitude;
        $this->longitude = $record->location->longitude;
    }
}
