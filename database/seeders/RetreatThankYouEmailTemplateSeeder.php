<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RetreatThankYouEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'WAH Retreat Thank You',
                'slug' => 'wah-retreat-thank-you',
                'subject' => 'Thank you for hosting a Wild at Heart Retreat',
                'hostFormUrl' => 'https://ransomedheart.typeform.com/to/BYGB5jOr',
                'attendeeFormUrl' => 'https://ransomedheart.typeform.com/to/o2iSVZjZ',
            ],
            [
                'name' => 'Captivating Retreat Thank You',
                'slug' => 'captivating-retreat-thank-you',
                'subject' => 'Thank you for hosting a Captivating Retreat',
                'hostFormUrl' => 'https://ransomedheart.typeform.com/to/jAYCEGqq',
                'attendeeFormUrl' => 'https://ransomedheart.typeform.com/to/MYeXbfhl',
            ],
            [
                'name' => '1-Day Captivating Retreat Thank You',
                'slug' => '1-day-captivating-retreat-thank-you',
                'subject' => 'Thank you for hosting a Captivating Retreat',
                'hostFormUrl' => 'https://ransomedheart.typeform.com/to/jAYCEGqq',
                'attendeeFormUrl' => 'https://ransomedheart.typeform.com/to/MYeXbfhl',
            ],
            [
                'name' => 'Becoming A King Retreat Thank You',
                'slug' => 'becoming-a-king-retreat-thank-you',
                'subject' => 'Thank you for hosting a Becoming A King Retreat',
                'hostFormUrl' => 'https://ransomedheart.typeform.com/to/SnlT2NIP',
                'attendeeFormUrl' => 'https://ransomedheart.typeform.com/to/C4ddf7NT',
            ],
            [
                'name' => 'Survival School Thank You',
                'slug' => 'survival-school-thank-you',
                'subject' => 'Thank you for hosting a Survival School',
                'hostFormUrl' => 'https://ransomedheart.typeform.com/to/ta5VGDPO',
                'attendeeFormUrl' => 'https://ransomedheart.typeform.com/to/Hpsxs7Ue',
            ],
        ];

        $rows = collect($templates)->map(fn (array $template) => [
            'is_active' => true,
            'cc_co_hosts' => true,
            'name' => $template['name'],
            'slug' => $template['slug'],
            'subject' => $template['subject'],
            'body' => $this->body($template['hostFormUrl'], $template['attendeeFormUrl']),
            'notes' => 'Sent to the host the day after a published retreat ends. Placeholders: [event_host_name], [event_name]. Renaming this template changes its slug and stops the email from sending.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('email_templates')->insertOrIgnore($rows->all());
    }

    private function body(string $hostFormUrl, string $attendeeFormUrl): string
    {
        return <<<MARKDOWN
            Hi [event_host_name],

            Thank you for hosting a [event_name] for your community. For years, these retreats were only available to people who could travel to a remote location to be with John, Stasi, and the team in person. Because of hosts like you, people everywhere now get the chance to recover their hearts close to home. You are truly part of the mission, and we're deeply grateful to have you with us.

            We know hosting takes real work: gathering everyone, preparing the space, leading through the sessions, and carrying it all in prayer. We hope you saw God move, and we'd love to hear how it went.

            Two quick requests:

            1. **Tell us about your hosting experience.** Your feedback helps us improve the resources and support we give hosts. It only takes a few minutes: [{$hostFormUrl}]({$hostFormUrl})
            2. **Share the attendee feedback form with your attendees.** Their stories and input help us understand the impact of the retreat and shape future events: [{$attendeeFormUrl}]({$attendeeFormUrl})

            Thank you again for saying yes to this. We're so grateful you partnered with us!

            With gratitude,
            The Wild at Heart Team
            MARKDOWN;
    }
}
