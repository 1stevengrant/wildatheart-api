<?php

use App\Models\Activity;
use StatamicRadPack\Runway\Runway;
use Spatie\Activitylog\Contracts\Activity as ActivityContract;

it('uses the application activity model for activity logging', function () {
    $activity = activity()
        ->withProperties([
            'attributes' => ['name' => 'Updated name'],
            'old' => ['name' => 'Original name'],
        ])
        ->log('Name updated');

    expect($activity)
        ->toBeInstanceOf(Activity::class)
        ->toBeInstanceOf(ActivityContract::class)
        ->and($activity->properties_json)
        ->toContain('"Updated name"')
        ->toContain('"Original name"');
});

it('configures the activity log as a read only runway resource', function () {
    $resource = Runway::findResource('activity');

    expect($resource->model())->toBeInstanceOf(Activity::class)
        ->and($resource->name())->toBe('Activity Log')
        ->and($resource->titleField())->toBe('description')
        ->and($resource->readOnly())->toBeTrue()
        ->and($resource->duplicatable())->toBeFalse()
        ->and($resource->orderBy())->toBe('created_at')
        ->and($resource->orderByDirection())->toBe('DESC')
        ->and($resource->blueprint()->fields()->has('properties_json'))->toBeTrue();
});
