<?php

namespace App\Services;

use Illuminate\Support\Arr;
use App\Data\EventStepsData;

class EventCreationStepHandlerService
{
    private const array DEFAULT_EVENT_STEPS = [
        'guide' => [
            'welcome' => false,
            'before' => false,
            'schedule' => false,
            'faqs' => false,
        ],
        'mailing_address' => false,
        'event_registration' => false,
    ];

    public function markStepComplete(string $step): void
    {
        $eventSteps = $this->sessionEventSteps();

        Arr::set($eventSteps, $step, true);

        session(['event_steps' => $eventSteps]);
    }

    public function resetEventSteps(): void
    {
        session(['event_steps' => self::DEFAULT_EVENT_STEPS]);
    }

    public function eventSteps(): EventStepsData
    {
        return EventStepsData::fromSession($this->sessionEventSteps());
    }

    /** @return array<string, bool|array<string, bool>> */
    private function sessionEventSteps(): array
    {
        return array_replace_recursive(
            self::DEFAULT_EVENT_STEPS,
            session('event_steps', []),
        );
    }
}
