<?php

arch('no debugging leftovers ship to production')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'die', 'exit'])
    ->not->toBeUsed();

arch('security preset')
    ->preset()
    ->security()
    ->ignoring('md5')      // used only for cache keys & Gravatar hashing, never crypto
    ->ignoring('uniqid')   // navigation item key fallbacks, never security tokens
    ->ignoring('mt_rand')                            // cosmetic map-pin jitter, never security tokens
    ->ignoring('App\Console\Commands\DbSyncCommand'); // dev-only mysqldump shell-out

arch('models stay free of the HTTP layer')
    ->expect('App\Models')
    ->not->toUse(['App\Http', 'Illuminate\Http\Request']);

arch('actions are framework-agnostic')
    ->expect('App\Actions')
    ->not->toUse('App\Http')
    // TODO: These actions type-hint event request objects directly. Pass an
    // array/Data object instead so the actions no longer depend on App\Http.
    ->ignoring([
        'App\Actions\Event\PublishEventDraft',
        'App\Actions\Event\SaveEventDraft',
    ]);

arch('actions use a laravel-actions trait')
    ->expect('App\Actions')
    ->toUseTrait('Lorisleiva\Actions\Concerns\AsAction')
    ->ignoring('App\Actions\ZendeskCreateTicket') // uses AsJob instead
    ->ignoring('App\Actions\Statamic');           // Statamic\Actions\Action subclasses

arch('jobs are queueable')
    ->expect('App\Jobs')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

arch('data objects extend Spatie Data')
    ->expect('App\Data')
    ->toExtend('Spatie\LaravelData\Data')
    ->ignoring('App\Data\Concerns'); // shared traits, not Data classes

arch('enums are backed')
    ->expect('App\Enums')
    ->toBeEnums();
