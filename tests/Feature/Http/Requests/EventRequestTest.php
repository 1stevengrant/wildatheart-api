<?php

use App\Http\Requests\EventRequest;
use App\Models\EventType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->create();
    $this->eventType = EventType::where('name', '=', EventType::WAH_BASIC)->first();

    $this->validEventData = [
        'name' => 'My Event',
        'city' => 'Springfield',
        'zip' => '62701',
        'country' => 'US',
        'latitude' => 39.78,
        'longitude' => -89.65,
        'region' => 'Midwest',
        'district' => 'Central',
        'locality' => 'Springfield',
        'neighborhood' => 'Downtown',
        'street_address' => '123 Main St',
        'is_private' => false,
        'state' => 'IL',
        'start_date' => '2022-01-01',
        'end_date' => '2022-01-02',
        'number_of_items_in_material_order' => 1,
    ];
});

test('event request validates required country field', function () {
    $invalidData = $this->validEventData;
    unset($invalidData['country']);

    $request = new EventRequest;
    $validator = Validator::make($invalidData, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('country'))->toBeTrue();
});

test('event request validates required latitude field', function () {
    $invalidData = $this->validEventData;
    unset($invalidData['latitude']);

    $request = new EventRequest;
    $validator = Validator::make($invalidData, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('latitude'))->toBeTrue();
});

test('event request validates required longitude field', function () {
    $invalidData = $this->validEventData;
    unset($invalidData['longitude']);

    $request = new EventRequest;
    $validator = Validator::make($invalidData, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('longitude'))->toBeTrue();
});

test('event request validates required start_date field', function () {
    $invalidData = $this->validEventData;
    unset($invalidData['start_date']);

    $request = new EventRequest;
    $validator = Validator::make($invalidData, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('start_date'))->toBeTrue();
});

test('event request validates required number_of_items_in_material_order field', function () {
    $invalidData = $this->validEventData;
    unset($invalidData['number_of_items_in_material_order']);

    $request = new EventRequest;
    $validator = Validator::make($invalidData, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('number_of_items_in_material_order'))->toBeTrue();
});

test('event request passes with all required fields', function () {
    $request = new EventRequest;
    $validator = Validator::make($this->validEventData, $request->rules());

    expect($validator->fails())->toBeFalse();
});

test('event request validates latitude is numeric', function () {
    $invalidData = $this->validEventData;
    $invalidData['latitude'] = 'not-a-number';

    $request = new EventRequest;
    $validator = Validator::make($invalidData, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('latitude'))->toBeTrue();
});

test('event request validates longitude is numeric', function () {
    $invalidData = $this->validEventData;
    $invalidData['longitude'] = 'not-a-number';

    $request = new EventRequest;
    $validator = Validator::make($invalidData, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('longitude'))->toBeTrue();
});

test('event request validates number_of_items_in_material_order is numeric', function () {
    $invalidData = $this->validEventData;
    $invalidData['number_of_items_in_material_order'] = 'not-a-number';

    $request = new EventRequest;
    $validator = Validator::make($invalidData, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('number_of_items_in_material_order'))->toBeTrue();
});

test('event request validates start_date is a valid date', function () {
    $invalidData = $this->validEventData;
    $invalidData['start_date'] = 'not-a-date';

    $request = new EventRequest;
    $validator = Validator::make($invalidData, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('start_date'))->toBeTrue();
});
