<?php

use App\Models\EventType;
use Illuminate\Database\Migrations\Migration;
use App\Actions\EventType\CopyLandingPageContentToEventType;

return new class extends Migration
{
    public function up(): void
    {
        EventType::query()
            ->whereNotNull('related_landing_page_id')
            ->each(fn (EventType $eventType) => CopyLandingPageContentToEventType::run($eventType));
    }
};
