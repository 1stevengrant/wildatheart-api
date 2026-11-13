<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventStepsData extends Data
{
    public function __construct(
        public EventGuideStepsData $guide,
        public bool $mailingAddress,
        public bool $eventRegistration,
    ) {}

    /**
     * @param array{
     *     guide: array{welcome: bool, before: bool, schedule: bool, faqs: bool},
     *     mailing_address: bool,
     *     event_registration: bool
     * } $eventSteps
     */
    public static function fromSession(array $eventSteps): self
    {
        return new self(
            guide: new EventGuideStepsData(...$eventSteps['guide']),
            mailingAddress: $eventSteps['mailing_address'],
            eventRegistration: $eventSteps['event_registration'],
        );
    }
}
