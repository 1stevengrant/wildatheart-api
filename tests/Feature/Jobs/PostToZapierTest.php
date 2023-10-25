<?php

use App\Jobs\PostToZapier;

it('posts the fire details to zapier and adds the fire to the spreadsheet', function () {
    \Illuminate\Support\Facades\Queue::fake();
    \Illuminate\Support\Facades\Http::fake();

    $data = [
        'email' => 'test@test.com',
        'quantity' => 1,
        'given_name' => 'Steven',
        'family_name' => 'Grant',
        'street_address' => '123 Main St',
        'address_2' => 'Apt 1',
        'city' => 'Anytown',
        'state' => 'NY',
        'zip' => '12345',
        'country' => 'US',
        'date' => now(),
    ];

    PostToZapier::dispatch($data);

    \Illuminate\Support\Facades\Queue::assertPushed(PostToZapier::class);

    $this->app['env'] = 'production';

    $job = new PostToZapier($data);
    $job->handle();

    Http::assertSent(function ($request) use ($data) {
        return $request->hasHeader('Content-Type', 'application/json') &&
            $request->url() === config('services.zapier.fire_webhook_url') &&
            $request['email'] === $data['email'] &&
            $request['quantity'] === $data['quantity'] &&
            $request['first_name'] === $data['given_name'] &&
            $request['last_name'] === $data['family_name'] &&
            $request['address_1'] === $data['street_address'] &&
            $request['address_2'] === $data['address_2'] &&
            $request['city'] === $data['city'] &&
            $request['state'] === $data['state'] &&
            $request['zip'] === $data['zip'] &&
            $request['country'] === $data['country'];
    });
});
