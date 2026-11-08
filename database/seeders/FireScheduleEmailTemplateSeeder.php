<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Placeholder copy for the Fire schedule emails, editable in the CP. Inserted by a
 * migration so the mailables never hit a missing template, and re-run after the
 * email template SQL dump in local and test seeding. Existing rows are left alone.
 */
class FireScheduleEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $templates = collect([
            [
                'name' => 'Fire schedule dates added',
                'subject' => 'New dates added for [event_name]',
                'body' => "Hi [event_host_name],\n\nWe've added the next dates for [event_name]:\n\n[dates_list]\n\nIf any of these don't work, you can change them:\n\n[button_edit_schedule]\n\nIf you've stopped hosting, you can take your Fire off the map:\n\n[button_inactive]",
            ],
            [
                'name' => 'Fire schedule add more dates',
                'subject' => 'Add more dates for [event_name]',
                'body' => "Hi [event_host_name],\n\nThe last date on the schedule for [event_name] is [last_date]. Add more dates to keep your Fire on the map.\n\n[button_edit_schedule]",
            ],
            [
                'name' => 'Fire schedule last date',
                'subject' => '[event_name] has one week of dates left',
                'body' => "Hi [event_host_name],\n\nThe last date for [event_name] is [last_date]. If you don't add more dates, we'll take your Fire off the map after that.\n\n[button_edit_schedule]",
            ],
            [
                'name' => 'Fire schedule unpublished',
                'subject' => '[event_name] is no longer on the map',
                'body' => "Hi [event_host_name],\n\nThe last date for [event_name] was [last_date], so we've taken your Fire off the map. Add new dates and publish it again whenever you're ready.\n\n[button_edit_schedule]",
            ],
        ])->map(fn (array $template) => [
            ...$template,
            'slug' => Str::slug($template['name']),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('email_templates')->insertOrIgnore($templates->all());
    }
}
