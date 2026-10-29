<?php

use App\Providers\AppServiceProvider;
use App\Providers\Auth0ServiceProvider;
use App\Providers\CustomStatamicProvider;

return [
    AppServiceProvider::class,
    Auth0ServiceProvider::class,
    CustomStatamicProvider::class,
];
