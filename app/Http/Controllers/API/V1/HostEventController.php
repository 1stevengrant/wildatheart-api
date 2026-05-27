<?php

namespace App\Http\Controllers\API\V1;

use App\Models\User;
use App\Models\Event;
use App\Data\EventData;
use App\Models\EventType;
use App\Jobs\PostToZapier;
use App\Support\EventDateHelper;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\EventManagerInvitation;
use App\Support\EventTypeDateSelector;
use App\Http\Requests\API\V1\CreateEventRequest;
use App\Http\Requests\API\V1\SaveEventDraftRequest;
use App\DataTransferObjects\GoogleSheetDataTransferObject;

class HostEventController extends Controller
{
    public function index(): JsonResponse
    {
        $baseQuery = Event::query()
            ->controllableByUser(auth()->user())
            ->with(['user', 'eventType', 'managers', 'pendingManagerInvitations'])
            ->orderByRaw('start_date < NOW()')
            ->orderBy('start_date');

        $listed = (clone $baseQuery)
            ->published()
            ->get()
            ->map(fn (Event $event) => EventData::fromModel($event))
            ->values();

        $drafts = (clone $baseQuery)
            ->unpublished()
            ->get()
            ->map(fn (Event $event) => EventData::fromModel($event))
            ->values();

        return response()->json([
            'listed' => $listed,
            'drafts' => $drafts,
        ]);
    }

    public function store(CreateEventRequest $request, EventType $eventType)
    {
        EventDateHelper::adjustEndDateForRetreat(
            request: $request,
            groupType: $eventType->group_type,
            eventTypeName: $eventType->name,
        );

        $event = auth()->user()->events()->create([
            'event_type_id' => $eventType->id,
            'name' => $request->name,
            'city' => $request->city,
            'zip' => $request->zip,
            'country' => $request->country,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'region' => $request->region,
            'district' => $request->district,
            'locality' => $request->locality,
            'neighborhood' => $request->neighborhood,
            'street_address' => $request->street_address,
            'is_private' => $request->is_private,
            'state' => $request->state,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'frequency' => EventTypeDateSelector::requiresFrequency($eventType->group_type)
                ? $request->frequency
                : null,
            'sex' => $eventType->sex,
            'number_of_items_in_material_order' => $request->number_of_items_in_material_order,
        ]);

        $mailingAddress = auth()->user()->primaryAddress();

        if (
            $eventType->hasZapier() &&
            $eventType->hasMaterialsOrder() &&
            $request->number_of_items_in_material_order != '0' &&
            $mailingAddress
        ) {
            PostToZapier::dispatch(new GoogleSheetDataTransferObject(
                webhookUrl: $eventType->zapier_webhook_url,
                eventId: $event->id,
                eventType: $eventType->name,
                email: auth()->user()->email,
                quantity: $request->number_of_items_in_material_order,
                first_name: auth()->user()->given_name,
                last_name: auth()->user()->family_name,
                address_1: $mailingAddress->street_address,
                address_2: $mailingAddress->address2,
                city: $mailingAddress->city,
                state: $mailingAddress->state,
                zip: $mailingAddress->zip,
                country: $mailingAddress->country,
                date: now()->toDateTimeString(),
            ));
        }

        return response()->json([
            'success' => true,
            'event' => $event,
        ]);
    }

    /**
     * Save an event draft. Accepts partial data; creates or updates an unpublished event.
     * Does not trigger Zapier or external integrations.
     */
    public function storeDraft(SaveEventDraftRequest $request, EventType $eventType)
    {

        $eventId = $request->validated('event_id');

        if ($eventId) {
            $event = Event::where('id', $eventId)
                ->where('user_id', auth()->id())
                ->whereNull('approved_at')
                ->first();

            if (! $event) {
                return response()->json([
                    'message' => 'Draft not found or you do not have permission to update it.',
                ], 404);
            }

            if ($request->filled('start_date')) {
                EventDateHelper::adjustEndDateForRetreat(
                    request: $request,
                    groupType: $eventType->group_type,
                    eventTypeName: $eventType->name,
                );
            }
            $event->fill($this->draftPayload($request, $eventType));
            $event->save();
            $this->syncCoHostInvitations($event, $request);
        } else {
            EventDateHelper::adjustEndDateForRetreat(
                request: $request,
                groupType: $eventType->group_type,
                eventTypeName: $eventType->name,
            );

            $event = auth()->user()->events()->create(
                array_merge(
                    $this->draftPayload($request, $eventType),
                    [
                        'event_type_id' => $eventType->id,
                        'sex' => $eventType->sex,
                        'approved_at' => null,
                    ]
                )
            );
            $this->syncCoHostInvitations($event, $request);
        }

        return response()->json([
            'success' => true,
            'event' => $event->fresh(['managers', 'pendingManagerInvitations']),
        ]);
    }

    /**
     * Build the event payload from the draft request, including only filled fields.
     */
    private function draftPayload(SaveEventDraftRequest $request, EventType $eventType): array
    {
        $payload = [
            'name' => $request->filled('name') ? $request->name : null,
            'latitude' => $request->filled('latitude') ? $request->latitude : null,
            'longitude' => $request->filled('longitude') ? $request->longitude : null,
            'start_date' => $request->filled('start_date') ? $request->start_date : null,
            'end_date' => $request->filled('end_date') ? $request->end_date : null,
            'frequency' => EventTypeDateSelector::requiresFrequency($eventType->group_type)
                ? ($request->filled('frequency') ? $request->frequency : null)
                : null,
            'is_private' => $request->boolean('is_private', false),
            'number_of_items_in_material_order' => $request->filled('number_of_items_in_material_order')
                ? $request->number_of_items_in_material_order
                : null,
            'description' => $request->filled('event_info') ? $request->event_info : null,
            'city' => $request->input('city'),
            'zip' => $request->input('zip'),
            'country' => $request->input('country'),
            'region' => $request->input('region'),
            'district' => $request->input('district'),
            'locality' => $request->input('locality'),
            'neighborhood' => $request->input('neighborhood'),
            'street_address' => $request->input('street_address'),
            'state' => $request->input('state'),
        ];

        return array_filter($payload, fn ($v) => $v !== null);
    }

    /**
     * Sync pending co-host invitations from the draft request.
     * Does not send invitation emails — that happens when publishing via the event manager flow.
     */
    private function syncCoHostInvitations(Event $event, SaveEventDraftRequest $request): void
    {
        if (! $request->has('co_host_emails')) {
            return;
        }

        $emails = collect($request->validated('co_host_emails') ?? [])
            ->map(fn (string $email) => mb_strtolower(mb_trim($email)))
            ->filter()
            ->unique()
            ->reject(fn (string $email) => $email === mb_strtolower($event->user?->email ?? ''))
            ->reject(function (string $email) use ($event) {
                $user = User::where('email', $email)->first();

                if (! $user) {
                    return false;
                }

                return $user->id === $event->user_id
                    || $event->managers()->where('user_id', $user->id)->exists();
            })
            ->values();

        $staleInvitations = $event->pendingManagerInvitations();

        if ($emails->isNotEmpty()) {
            $staleInvitations->whereNotIn('email', $emails->all());
        }

        $staleInvitations->get()->each(fn (EventManagerInvitation $invitation) => $invitation->delete());

        foreach ($emails as $email) {
            if ($event->pendingManagerInvitations()->where('email', $email)->exists()) {
                continue;
            }

            EventManagerInvitation::create([
                'event_id' => $event->id,
                'email' => $email,
                'invited_by_user_id' => auth()->id(),
            ]);
        }
    }
}
