<?php

namespace App\Mail\Fire\Schedule;

use App\Models\EmailTemplate;

class FireScheduleLastDateMailable extends FireScheduleMailable
{
    protected function templateSlug(): string
    {
        return EmailTemplate::FIRE_SCHEDULE_LAST_DATE;
    }
}
