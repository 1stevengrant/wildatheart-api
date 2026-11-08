<?php

namespace App\Mail\Fire\Schedule;

use App\Models\EmailTemplate;

class FireScheduleUnpublishedMailable extends FireScheduleMailable
{
    protected function templateSlug(): string
    {
        return EmailTemplate::FIRE_SCHEDULE_UNPUBLISHED;
    }
}
