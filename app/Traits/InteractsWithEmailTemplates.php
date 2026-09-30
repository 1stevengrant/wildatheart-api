<?php

namespace App\Traits;

use App\Models\EmailTemplate;
use Illuminate\Mail\Mailables\Address;

trait InteractsWithEmailTemplates
{
    protected function getEmailTemplate(string $slug): EmailTemplate
    {
        return EmailTemplate::where('slug', $slug)->firstOrFail();
    }

    /**
     * The sender set on the CMS template ($this->emailTemplate), or null to use the app's default
     * mail.from. A template may set just an address; it then keeps the default sender name.
     */
    protected function templateSender(): ?Address
    {
        $template = $this->emailTemplate ?? null;

        if (! $template instanceof EmailTemplate || blank($template->from_email)) {
            return null;
        }

        return new Address(
            $template->from_email,
            filled($template->from_name) ? $template->from_name : config('mail.from.name'),
        );
    }
}
