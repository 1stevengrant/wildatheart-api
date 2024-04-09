<?php

namespace App\Traits;

trait InteractsWithEmailTemplates
{
    protected function getEmailTemplate(string $slug): \App\Models\EmailTemplate
    {
        return \App\Models\EmailTemplate::where('slug', $slug)->firstOrFail();
    }
}
