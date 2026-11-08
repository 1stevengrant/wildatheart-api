<?php

namespace App\Mail\Fire\Schedule;

use App\Models\Event;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\URL;

class FireScheduleDatesAddedMailable extends FireScheduleMailable
{
    /**
     * @param  array<int, string>  $dateLines  Formatted dates, one per line
     */
    public function __construct(Event $event, string $lastDate, public array $dateLines)
    {
        parent::__construct($event, $lastDate);
    }

    protected function templateSlug(): string
    {
        return EmailTemplate::FIRE_SCHEDULE_DATES_ADDED;
    }

    protected function replacements(): array
    {
        return [
            ...parent::replacements(),
            '[dates_list]' => collect($this->dateLines)->map(fn (string $line) => "- {$line}")->implode("\n"),
            '[button_inactive]' => view('emails.components.button', [
                'url' => URL::signedRoute('fire.extinguish', ['event' => $this->event]),
                'text' => 'Extinguish My Fire',
                'color' => 'red',
            ])->render(),
        ];
    }
}
