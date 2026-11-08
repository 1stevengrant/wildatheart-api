<?php

namespace App\Mail\Fire\Schedule;

use App\Models\EmailTemplate;

class FireScheduleAddMoreDatesMailable extends FireScheduleMailable
{
    protected function templateSlug(): string
    {
        return EmailTemplate::FIRE_SCHEDULE_ADD_MORE_DATES;
    }
}
