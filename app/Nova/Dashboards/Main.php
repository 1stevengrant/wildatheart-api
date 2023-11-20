<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\AttendeesPerDay;
use App\Nova\Metrics\EventsPerDay;
use App\Nova\Metrics\UsersPerDay;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            new UsersPerDay(),
            new EventsPerDay(),
            new AttendeesPerDay(),
        ];
    }
}
