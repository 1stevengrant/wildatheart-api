<?php

namespace App\Traits;

use App\Models\EmailTemplate;

trait InteractsWithEmailTemplates
{
    protected function getEmailTemplate(string $slug): EmailTemplate
    {
        return EmailTemplate::where('slug', $slug)->firstOrFail();
    }
}
