<?php

namespace App\Http\Controllers\Internal;

use Throwable;
use App\Models\User;
use App\Models\Event;
use RuntimeException;
use App\Models\Attendee;
use App\Models\EventType;
use App\Mail\HostAddedMail;
use App\Mail\HostRemovedMail;
use Illuminate\Http\Response;
use Illuminate\Mail\Mailable;
use App\Mail\HostInvitationMail;
use App\Mail\Event\GiftRequested;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Mail\Event\AttendeeRegistered;
use App\Mail\EventAutoUnpublishedMail;
use App\Models\EventManagerInvitation;
use App\Models\EventOwnershipTransfer;
use App\Mail\Event\UserConnectedToHost;
use App\Mail\Event\AttendeeConnectedToHost;
use App\Mail\Event\EventInterestRegistered;
use App\Mail\Event\NewEventCreatedMailable;
use App\Mail\Fire\SevenDayFollowupMailable;
use App\Mail\Fire\AnnualFireCheckinMailable;
use App\Mail\OwnershipTransferCompletedMail;
use App\Mail\OwnershipTransferInvitationMail;
use App\Mail\Fire\ConfirmFireUnpublishedMailable;
use App\Mail\Fire\MonthlyFireCleanupEmailToHostMailable;

/**
 * Renders every mailable in the browser so the theme can be reviewed without
 * sending anything. Restricted to Statamic super users by the super_admin
 * middleware on the internal route group.
 *
 * Samples are read from existing records, or built unsaved where no suitable
 * record exists. Nothing here writes to the database, so previews are safe to
 * reload freely, including in production.
 *
 * Note that previews therefore render real member data (event names, host
 * names, locations). That is data a super user can already read elsewhere in
 * the internal tools, but it is worth knowing before sharing a screenshot.
 */
class MailPreviewController extends Controller
{
    public function index(): View
    {
        $groups = [];

        foreach ($this->previews() as $key => $preview) {
            $groups[$preview['group']][$key] = $preview['label'];
        }

        return view('internal.mail-previews', ['groups' => $groups]);
    }

    public function show(string $preview): Response
    {
        $previews = $this->previews();

        abort_unless(isset($previews[$preview]), 404, "No mail preview named [{$preview}].");

        try {
            $html = $previews[$preview]['make']()->render();
        } catch (Throwable $e) {
            // 500 rather than a friendly 200 so a preview that cannot render is
            // a visible failure in tests, not a page that merely looks wrong.
            return response($this->failure($previews[$preview]['label'], $e), 500);
        }

        return response($html);
    }

    /**
     * @return array<string, array{label: string, group: string, make: callable(): Mailable}>
     */
    private function previews(): array
    {
        $previews = [];

        foreach (NewEventCreatedMailable::$templateMap as $eventTypeName => $templateSlug) {
            $previews['new-event-created--' . str($eventTypeName)->slug()] = [
                'label' => $eventTypeName,
                'group' => 'Host welcome (sent when an event is created)',
                'make' => fn (): Mailable => new NewEventCreatedMailable($this->sampleEvent($eventTypeName)),
            ];
        }

        return $previews + [
            'attendee-registered' => [
                'label' => 'Attendee registered',
                'group' => 'Attendees',
                'make' => fn (): Mailable => new AttendeeRegistered($this->sampleEvent(), $this->sampleAttendee()),
            ],
            'attendee-connected-to-host' => [
                'label' => 'Attendee connected to host',
                'group' => 'Attendees',
                'make' => fn (): Mailable => new AttendeeConnectedToHost($this->sampleEvent(), $this->sampleAttendee()),
            ],
            'user-connected-to-host' => [
                'label' => 'User connected to host',
                'group' => 'Attendees',
                'make' => fn (): Mailable => new UserConnectedToHost($this->sampleEvent(), $this->sampleUser()),
            ],
            'event-interest-registered' => [
                'label' => 'Event interest registered',
                'group' => 'Attendees',
                'make' => fn (): Mailable => new EventInterestRegistered($this->sampleEvent(), $this->sampleUser()),
            ],
            'gift-requested' => [
                'label' => 'Gift requested',
                'group' => 'Attendees',
                'make' => fn (): Mailable => new GiftRequested($this->sampleEvent(EventType::FIRES)),
            ],

            'host-invitation--new-user' => [
                'label' => 'Host invitation (recipient has no account)',
                'group' => 'Hosts',
                'make' => fn (): Mailable => new HostInvitationMail($this->sampleEvent(), 'Morgan', $this->sampleInvitation()),
            ],
            'host-invitation--existing-user' => [
                'label' => 'Host invitation (recipient already has an account)',
                'group' => 'Hosts',
                'make' => fn (): Mailable => new HostInvitationMail($this->sampleEvent(), 'Morgan'),
            ],
            'host-added' => [
                'label' => 'Host added',
                'group' => 'Hosts',
                'make' => fn (): Mailable => new HostAddedMail($this->sampleEvent(), $this->sampleUser(), $this->sampleUser()),
            ],
            'host-removed' => [
                'label' => 'Host removed',
                'group' => 'Hosts',
                'make' => fn (): Mailable => new HostRemovedMail($this->sampleEvent(), $this->sampleUser(), $this->sampleUser()),
            ],

            'ownership-transfer-invitation' => [
                'label' => 'Ownership transfer invitation',
                'group' => 'Ownership transfers',
                'make' => fn (): Mailable => new OwnershipTransferInvitationMail($this->sampleEvent(), 'Morgan', $this->sampleOwnershipTransfer()),
            ],
            'ownership-transfer-completed--new-owner' => [
                'label' => 'Ownership transfer completed (to new owner)',
                'group' => 'Ownership transfers',
                'make' => fn (): Mailable => new OwnershipTransferCompletedMail($this->sampleEvent(), $this->sampleUser(), $this->sampleUser()),
            ],
            'ownership-transfer-completed--previous-owner' => [
                'label' => 'Ownership transfer completed (to previous owner)',
                'group' => 'Ownership transfers',
                'make' => fn (): Mailable => new OwnershipTransferCompletedMail($this->sampleEvent(), $this->sampleUser(), $this->sampleUser(), false),
            ],

            'annual-fire-checkin' => [
                'label' => 'Annual fire check-in',
                'group' => 'Fire lifecycle',
                'make' => fn (): Mailable => new AnnualFireCheckinMailable($this->sampleEvent(EventType::FIRES)),
            ],
            'fire-deactivating-soon' => [
                'label' => 'Fire deactivating soon (seven day follow-up)',
                'group' => 'Fire lifecycle',
                'make' => fn (): Mailable => new SevenDayFollowupMailable($this->sampleEvent(EventType::FIRES)),
            ],
            'monthly-fire-cleanup' => [
                'label' => 'Monthly fire cleanup',
                'group' => 'Fire lifecycle',
                'make' => fn (): Mailable => new MonthlyFireCleanupEmailToHostMailable($this->sampleEvent(EventType::FIRES)),
            ],
            'confirm-fire-unpublished' => [
                'label' => 'Fire unpublished confirmation',
                'group' => 'Fire lifecycle',
                'make' => fn (): Mailable => new ConfirmFireUnpublishedMailable($this->sampleEvent(EventType::FIRES)),
            ],
            'event-auto-unpublished' => [
                'label' => 'Event auto-unpublished',
                'group' => 'Fire lifecycle',
                'make' => fn (): Mailable => new EventAutoUnpublishedMail($this->sampleEvent(EventType::FIRES)),
            ],
        ];
    }

    private function sampleEvent(?string $eventTypeName = null): Event
    {
        $query = Event::query()
            ->with(['eventType', 'user'])
            ->whereNotNull('user_id')
            ->whereHas('eventType');

        if ($eventTypeName) {
            $query->whereHas('eventType', fn ($builder) => $builder->where('name', $eventTypeName));
        }

        $event = $query->first();

        if (! $event) {
            throw new RuntimeException(
                $eventTypeName
                    ? "This preview needs an event of type \"{$eventTypeName}\" with a host, and none exists locally. Run php artisan db:production-sync, or seed one."
                    : 'This preview needs any event with a host, and none exists locally. Run php artisan db:production-sync, or seed one.'
            );
        }

        return $event;
    }

    private function sampleUser(): User
    {
        return $this->sampleEvent()->user;
    }

    private function sampleAttendee(): Attendee
    {
        $attendee = Attendee::query()->first();

        if (! $attendee) {
            throw new RuntimeException('This preview needs an attendee, and none exists locally.');
        }

        return $attendee;
    }

    private function sampleInvitation(): ?EventManagerInvitation
    {
        return EventManagerInvitation::query()->first();
    }

    /**
     * The template builds a signed acceptance URL, so this cannot be null the
     * way an invitation can. Falls back to an unsaved record with a placeholder
     * ULID when none exists locally: the link in the preview will not resolve,
     * but the layout renders and no row is created.
     */
    private function sampleOwnershipTransfer(): EventOwnershipTransfer
    {
        $transfer = EventOwnershipTransfer::query()->first();

        if ($transfer) {
            return $transfer;
        }

        $event = $this->sampleEvent();

        return (new EventOwnershipTransfer)->forceFill([
            'id' => '00000000000000000000000000',
            'event_id' => $event->id,
            'email' => 'preview@example.com',
            'transferred_by_user_id' => $event->user_id,
        ]);
    }

    private function failure(string $label, Throwable $e): string
    {
        return view('internal.mail-preview-failure', [
            'label' => $label,
            'message' => $e->getMessage(),
            'exception' => $e::class,
        ])->render();
    }
}
