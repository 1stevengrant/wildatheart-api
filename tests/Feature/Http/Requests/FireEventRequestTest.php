<?php

use App\Http\Requests\FireEventRequest;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    $this->request = new FireEventRequest;
});

it('validates valid fire event data', function () {
    $validator = Validator::make([
        'name' => 'Forest Incident',
        'frequency' => 'Weekly',
        'country' => 'USA',
        'latitude' => '40.7128',
        'longitude' => '-74.0060',
    ], (new FireEventRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

it('fails validation when name ends with fire', function () {
    $validator = Validator::make([
        'name' => 'California Fire',
        'country' => 'USA',
        'latitude' => '40.7128',
        'longitude' => '-74.0060',
        'frequency' => 'Weekly',
    ], (new FireEventRequest)->rules());

    expect($validator->fails())->toBeTrue();
});

it('fails validation when name contains emoji', function () {
    $validator = Validator::make([
        'name' => 'Forest Incident 🔥',
        'country' => 'USA',
        'latitude' => '40.7128',
        'longitude' => '-74.0060',
        'frequency' => 'Weekly',
    ], (new FireEventRequest)->rules());

    expect($validator->fails())->toBeTrue();
});

it('fails validation when name is too short', function () {
    $validator = Validator::make([
        'name' => 'A',
        'country' => 'USA',
        'latitude' => '40.7128',
        'longitude' => '-74.0060',
        'frequency' => 'Weekly',
    ], (new FireEventRequest)->rules());

    expect($validator->fails())->toBeTrue();
});

it('fails validation when required fields are missing', function () {
    $validator = Validator::make([
        'name' => 'Forest Incident',
    ], (new FireEventRequest)->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('frequency'))->toBeTrue()
        ->and($validator->errors()->has('country'))->toBeTrue()
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
    ], (new FireEventRequest)->rules());

    expect($validator->fails())->toBeTrue();
});
