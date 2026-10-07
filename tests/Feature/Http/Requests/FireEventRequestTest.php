<?php

use App\Models\EventType;
use App\Data\FireEventData;
use Illuminate\Support\Facades\Validator;

it('validates valid fire event data', function () {
    $validator = Validator::make([
        'name' => 'Forest Incident',
        'frequency' => 'Weekly',
        'country' => 'USA',
        'latitude' => '40.7128',
        'longitude' => '-74.0060',
    ], FireEventData::rules());

    expect($validator->passes())->toBeTrue();
});

it('fails validation when name ends with fire', function () {
    $validator = Validator::make([
        'name' => 'California Fire',
        'country' => 'USA',
        'latitude' => '40.7128',
        'longitude' => '-74.0060',
        'frequency' => 'Weekly',
    ], FireEventData::rules());

    expect($validator->fails())->toBeTrue();
});

it('fails validation when name contains emoji', function () {
    $validator = Validator::make([
        'name' => 'Forest Incident 🔥',
        'country' => 'USA',
        'latitude' => '40.7128',
        'longitude' => '-74.0060',
        'frequency' => 'Weekly',
    ], FireEventData::rules());

    expect($validator->fails())->toBeTrue();
});

it('fails validation when name is too short', function () {
    $validator = Validator::make([
        'name' => 'A',
        'country' => 'USA',
        'latitude' => '40.7128',
        'longitude' => '-74.0060',
        'frequency' => 'Weekly',
    ], FireEventData::rules());

    expect($validator->fails())->toBeTrue();
});

it('fails validation when required fields are missing', function () {
    $validator = Validator::make([
        'name' => 'Forest Incident',
    ], FireEventData::rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('frequency'))->toBeTrue()
        ->and($validator->errors()->has('latitude'))->toBeTrue()
        ->and($validator->errors()->has('longitude'))->toBeTrue();
});

it('fails validation when name is longer than 255 characters', function () {
    $validator = Validator::make([
        'name' => str_repeat('a', 256),
        'country' => 'USA',
        'latitude' => '40.7128',
        'longitude' => '-74.0060',
        'frequency' => 'Weekly',
    ], FireEventData::rules());

    expect($validator->fails())->toBeTrue();
});

it('accepts is_private boolean', function () {
    $validator = Validator::make([
        'name' => 'Forest Incident',
        'frequency' => 'Weekly',
        'latitude' => '40.7128',
        'longitude' => '-74.0060',
        'is_private' => true,
    ], FireEventData::rules());

    expect($validator->passes())->toBeTrue();
});

it('fails validation when name is longer than the fire name limit', function () {
    $validator = Validator::make([
        'name' => str_repeat('a', EventType::MAX_FIRE_NAME_LENGTH + 1),
        'country' => 'USA',
        'latitude' => '40.7128',
        'longitude' => '-74.0060',
        'frequency' => 'Weekly',
    ], FireEventData::rules());

    expect($validator->errors()->has('name'))->toBeTrue();
});
