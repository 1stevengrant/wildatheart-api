<?php

namespace App\Jobs\User;

use Exception;
use Throwable;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use App\Actions\User\UpdateUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Saloon\Exceptions\Request\RequestException;
use App\Http\Integrations\WeGive\WeGiveConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use App\Http\Integrations\WeGive\Requests\TagDonorsRequest;
use App\Http\Integrations\WeGive\Requests\FindOrCreateDonorsRequest;

class TagWeGiveDonorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $maxExceptions = 3;

    public function __construct(
        protected Event $event,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws Throwable
     */
    public function handle(): void
    {

        if (! $this->validateEventData()) {
            Log::error('TagWeGiveDonorJob: Missing required user data', ['event_id' => $this->event->id]);

            return;
        }

        $weGiveConnector = new WeGiveConnector;

        try {

            $donorId = $this->findOrCreateDonor($weGiveConnector);
            if (! $donorId) {
                return;
            }

            DB::transaction(function () use ($donorId) {
                UpdateUser::run(
                    $this->event->user,
                    [
                        'wegive_donor_id' => $donorId,
                    ]
                );
            });

            $this->tagDonor($weGiveConnector, $donorId);

        } catch (Exception $e) {
            Log::error('TagWeGiveDonorJob unhandled exception: ' . $e->getMessage(), [
                'event_id' => $this->event->id,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function validateEventData(): bool
    {
        return $this->event->user
            && $this->event->user->email
            && $this->event->user->given_name
            && $this->event->user->family_name
            && $this->event->user->phone
            && $this->event->we_give_tag;
    }

    /**
     * Find or create a WeGive donor
     */
    private function findOrCreateDonor(WeGiveConnector $connector): ?string
    {
        $requestData = [
            'email' => $this->event->user->email,
            'firstName' => $this->event->user->given_name,
            'lastName' => $this->event->user->family_name,
            'phone' => $this->event->user->phone,
        ];

        if ($this->event->user->primaryAddress()) {
            $requestData['address'] = $this->event->user->primaryAddress()->toArray();
        }

        $findOrCreateDonorRequest = new FindOrCreateDonorsRequest(...$requestData);

        try {
            $response = $connector->send($findOrCreateDonorRequest);

            return $response->json()['data']['id'] ?? null;
        } catch (FatalRequestException|RequestException $e) {
            Log::error('WeGive API error: ' . $e->getMessage(), [
                'event_id' => $this->event->id,
                'request' => 'FindOrCreateDonorsRequest',
            ]);

            return null;
        }
    }

    private function tagDonor(WeGiveConnector $connector, string $donorId): void
    {
        $tagDonorsRequest = new TagDonorsRequest(
            donorIds: [$donorId],
            tagIds: [$this->event->we_give_tag],
        );

        try {
            $connector->send($tagDonorsRequest);

            dispatch(new LogActivityJob(
                logName: 'wegive',
                description: 'Tagged donor',
                properties: [
                    'tag' => $this->event->we_give_tag,
                    'donor' => $donorId,
                ],
                causedBy: $this->event->user,
                performedOn: $this->event->user,
            ));
        } catch (FatalRequestException|RequestException $e) {
            Log::error('WeGive API error: ' . $e->getMessage(), [
                'event_id' => $this->event->id,
                'request' => 'TagDonorsRequest',
                'donor_id' => $donorId,
            ]);

            throw $e;
        }
    }
}
