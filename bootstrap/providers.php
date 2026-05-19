<?php

use App\Providers\AppServiceProvider;
use App\Providers\Auth0ServiceProvider;
use App\Providers\CustomStatamicProvider;
use App\Providers\HorizonServiceProvider;

return [
    AppServiceProvider::class,
    Auth0ServiceProvider::class,
    CustomStatamicProvider::class,
    HorizonServiceProvider::class,
];
