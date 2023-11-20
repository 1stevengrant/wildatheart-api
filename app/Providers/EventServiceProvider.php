<?php

namespace App\Providers;

use App\Listeners\UpdateLastLoginDateTime;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Issue;
use App\Observers\AttendeeObserver;
use App\Observers\EventObserver;
use App\Observers\IssueObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            UpdateLastLoginDateTime::class,
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
