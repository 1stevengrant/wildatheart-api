<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Str;
use App\Data\SocialMetaData;
use App\Enums\EventTypeEnum;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;

class SeoService
{
    /**
     * The metadata most recently rendered server-side, shared with Inertia so
     * the client can keep the document's social tags in sync during
     * client-side navigation (when the server-rendered tags are never reissued).
     */
    protected ?SocialMetaData $sharedMeta = null;

    public function setMetadata(string $title, string $description, ?string $image, string $url): void
    {
        $this->setSEOMeta($title, $description, $url);
        $this->setOpenGraph($title, $description, $url, $image);
        $this->setTwitterCard($title, $description, $image);
        $this->setJsonLd($title, $description, $url, $image);

        $this->recordSharedMeta($title, $description, $url, $image);
    }

    public function setEventMetadata(Event $event): void
    {
        $title = $this->buildEventTitle($event);
        $description = preg_replace('/\s+/', ' ', $event->eventType->card_teaser_text ?? '');
        $image = openGraphImage($event->eventType->image);
        $url = url()->current();

        $this->setSEOMeta($title, $description, $url);
        $this->setEventOpenGraph($title, $description, $url, $image);
        $this->setTwitterCard($title, $description, $image);
        $this->setEventJsonLd($event, $title, $description, $url, $image);

        $this->recordSharedMeta($title, $description, $url, $image);
    }

    public function getSharedMeta(): ?SocialMetaData
    {
        return $this->sharedMeta;
    }

    protected function recordSharedMeta(string $title, string $description, string $url, ?string $image): void
    {
        $this->sharedMeta = new SocialMetaData(
            title: $title,
            description: $description,
            url: $url,
            image: $image,
        );
    }

    protected function setSEOMeta(string $title, string $description, string $url): void
    {
        SEOMeta::setTitle($title)
            ->setDescription($description)
            ->setCanonical($url);
    }

    protected function setOpenGraph(string $title, string $description, string $url, ?string $image): void
    {
        OpenGraph::setTitle($title)
            ->setUrl($url)
            ->setDescription($description)
            ->addProperty('type', 'website')
            ->addImage($image)
            ->addProperty('locale', 'en-US');
    }

    protected function setEventOpenGraph(string $title, string $description, string $url, ?string $image): void
    {
        OpenGraph::setTitle($title)
            ->setUrl($url)
            ->setDescription($description)
            ->addProperty('type', 'website')
            ->addImage($image, $this->openGraphImageAttributes($image))
            ->addProperty('locale', 'en-US')
            ->addProperty('site_name', 'Wild at Heart');
    }

    protected function setTwitterCard(string $title, string $description, ?string $image): void
    {
        TwitterCard::setType('summary_large_image')
            ->setTitle($title)
            ->addImage($image)
            ->setDescription($description)
            ->setSite('@wildatheart');
    }

    /**
     * @return array{secure_url: string, type: string, width: int, height: int}
     */
    protected function openGraphImageAttributes(string $image): array
    {
        return [
            'secure_url' => $image,
            'type' => 'image/jpeg',
            'width' => 1200,
            'height' => 630,
        ];
    }

    protected function setJsonLd(string $title, string $description, string $url, ?string $image): void
    {
        JsonLd::setTitle($title)
            ->setDescription($description)
            ->addImage($image)
            ->setUrl($url);

        JsonLd::generate();
    }

    protected function setEventJsonLd(Event $event, string $title, string $description, string $url, ?string $image): void
    {
        $jsonLdType = match ($event->eventType->type) {
            EventTypeEnum::Gathering => 'SocialEvent',
            EventTypeEnum::Course => 'EducationEvent',
            default => 'Event',
        };

        JsonLd::setType($jsonLdType)
            ->setTitle($title)
            ->setDescription($description)
            ->addImage($image)
            ->setUrl($url)
            ->addValue('eventAttendanceMode', 'https://schema.org/OfflineEventAttendanceMode')
            ->addValue('organizer', [
                '@type' => 'Person',
                'name' => $event->host_name,
            ])
            ->addValue('location', [
                '@type' => 'Place',
                'name' => $event->location,
                'address' => $event->location,
            ]);

        if ($event->start_date) {
            JsonLd::addValue('startDate', $event->start_date->toIso8601String());
        }

        if ($event->end_date) {
            JsonLd::addValue('endDate', $event->end_date->toIso8601String());
        }

        JsonLd::generate();
    }

    private function buildEventTitle(Event $event): string
    {
        $eventTypeName = $event->eventType->name;
        $date = $event->start_date?->format('M j, Y');

        $title = match ($event->eventType->type) {
            EventTypeEnum::Gathering => "Join {$event->host_name}'s " . Str::singular($eventTypeName),
            default => "Join $event->host_name's $eventTypeName",
        };

        if ($date && $event->eventType->type !== EventTypeEnum::Gathering) {
            $title .= " | $date";
        }

        return $title;
    }
}
