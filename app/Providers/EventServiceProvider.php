<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Issue;
use App\Models\Attendee;
use Statamic\Events\EntrySaved;
use App\Observers\EventObserver;
use App\Observers\IssueObserver;
use Statamic\Events\EntrySaving;
use Illuminate\Auth\Events\Login;
use Statamic\Events\EntryCreated;
use Statamic\Events\EntryDeleted;
use App\Observers\AttendeeObserver;
use Statamic\Events\GlobalVariablesSaved;
use App\Listeners\LogStatamicEntryChanges;
use App\Listeners\UpdateLastLoginDateTime;
use App\Listeners\SaveSubsplashItemEntryWithId;
use App\Listeners\ClearEventTypeCacheOnEntrySavedListener;
use App\Listeners\ClearNavigationCacheOnGlobalSavedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            UpdateLastLoginDateTime::class,
        ],
        EntrySaved::class => [
            SaveSubsplashItemEntryWithId::class,
            ClearEventTypeCacheOnEntrySavedListener::class,
        ],
        EntrySaving::class => [
            LogStatamicEntryChanges::class,
        ],
        EntryCreated::class => [
            LogStatamicEntryChanges::class,
        ],
        EntryDeleted::class => [
            LogStatamicEntryChanges::class,
        ],
        GlobalVariablesSaved::class => [
            ClearNavigationCacheOnGlobalSavedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }

    protected $observers = [
        Event::class => [EventObserver::class],
        Issue::class => [IssueObserver::class],
        Attendee::class => [AttendeeObserver::class],
    ];
}
