<?php

namespace App\Services;

class EventCreationStepHandlerService
{
    public function markStepComplete($step, $subStep = null): void
    {
        $eventSteps = session('event_steps');

        if ($subStep) {
            $eventSteps[$step][$subStep] = true;
        } else {
            $eventSteps[$step] = true;
        }

        session(['event_steps' => $eventSteps]);

    }

    public function resetEventSteps(): void
    {
        session()->forget('event_steps');
        session([
            'event_steps' => [
                'guide' => [
                    'welcome' => false,
                    'before' => false,
                    'schedule' => false,
                    'faqs' => false,
                ],
                'mailing_address' => false,
            ],
        ]);
    }
}
