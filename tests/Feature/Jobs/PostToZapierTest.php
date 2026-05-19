<?php

use App\Jobs\PostToZapier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use App\DataTransferObjects\GoogleSheetDataTransferObject;

it('posts the event details to zapier and adds the event to the spreadsheet', function () {
    Queue::fake();
    Http::fake();

    $dataTransferObject = new GoogleSheetDataTransferObject(
        webhookUrl: 'https://hooks.zapier.com/hooks/catch/123456/abcdef',
        eventId: '123',
        eventType: 'event',
        email: 'test@test.com',
        quantity: 1,
        first_name: 'Steven',
        last_name: 'Grant',
        address_1: '123 Main St',
        address_2: 'Apt 1',
        city: 'Anytown',
        state: 'NY',
        zip: '12345',
        country: 'US',
        date: now(),
    );

    PostToZapier::dispatch($dataTransferObject);

    Queue::assertPushed(PostToZapier::class);
});
