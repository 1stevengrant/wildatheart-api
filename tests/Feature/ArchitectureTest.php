<?php

arch('php presets')
    ->preset()
    ->php()
    ->ignoring('Ghijk');

arch('security presets')
    ->preset()
    ->security()
    ->ignoring([
        'Ghijk',
        // md5() of email for Gravatar URL; Gravatar's API requires md5.
        'App\Models\User',
        // md5() used for non-cryptographic cache keys.
        'App\Http\Controllers\Internal\InternalEventSearchController',
        'App\Services\Subsplash\SubsplashApiClient',
        'App\Services\EventCacheService',
        // uniqid() / mt_rand() used for non-security purposes (UI IDs, map offsets).
        'App\Data\Navigation\DropdownLinkData',
        'App\Data\Navigation\NavItemData',
        'App\Data\Navigation\DesktopNavItemData',
        'App\Services\EventOffsettingService',
        // tempnam() used for local mysqldump error capture in a dev-only command.
        'App\Console\Commands\DbSyncCommand',
    ]);

arch('no debug statements left in app code')
    ->expect(['dd', 'dump', 'var_dump', 'die', 'exit', 'print_r'])
    ->not->toBeUsed();

arch('env() is only called from config files')
    ->expect('env')
    ->not->toBeUsed();

arch('models live under App\\Models and extend an Eloquent base')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring([
        'App\Models\User',
        'App\Models\BaseModel',
    ]);

arch('controllers live under App\\Http\\Controllers and extend the base Controller')
    ->expect('App\Http\Controllers')
    ->toExtend('App\Http\Controllers\Controller')
    ->ignoring([
        'App\Http\Controllers\Controller',
        'App\Http\Controllers\Drupal\Concerns\LogsDrupalRedirect',
        'App\Http\Controllers\API\V1\Content\Concerns\ResolvesLivePreview',
    ]);

arch('actions use the AsAction trait')
    ->expect('App\Actions')
    ->toUse('Lorisleiva\Actions\Concerns\AsAction');

arch('queued jobs implement ShouldQueue')
    ->expect('App\Jobs')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

arch('data objects extend Spatie\\LaravelData\\Data')
    ->expect('App\Data')
    ->classes()
    ->toExtend('Spatie\LaravelData\Data')
    ->ignoring('App\Data\Concerns');

arch('enums are confined to App\\Enums')
    ->expect('App\Enums')
    ->toBeEnums();

arch('observers are suffixed with Observer')
    ->expect('App\Observers')
    ->toHaveSuffix('Observer');

arch('policies are suffixed with Policy')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy');

arch('form requests extend FormRequest')
    ->expect('App\Http\Requests')
    ->classes()
    ->toExtend('Illuminate\Foundation\Http\FormRequest')
    ->ignoring('App\Http\Requests\Concerns');

arch('mailables extend Mailable')
    ->expect('App\Mail')
    ->toExtend('Illuminate\Mail\Mailable')
    ->ignoring('App\Mail\Transport');
