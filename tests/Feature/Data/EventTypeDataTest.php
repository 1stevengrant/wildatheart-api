<?php

use App\Models\Event;
use App\Models\EventType;
use App\Data\V1\EventData;
use App\Data\EventTypeData;
use App\Data\EventType\FaqData;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Data\EventType\GuidePageData;
use App\Data\EventType\KeyDetailData;
use App\Enums\EventGuideBlockTypeEnum;
use App\Data\EventType\ActionButtonData;
use App\Data\EventType\ContentBlockData;
use Spatie\Activitylog\Facades\Activity;
use App\Data\EventType\AccordionItemData;
use App\Data\EventType\ScheduleBlockData;
use App\Enums\EventScheduleBlockTypeEnum;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
    Activity::disableLogging();
});

function statamicEventTypeContent(): array
{
    return [
        'key_details' => [
            ['id' => 'kd1', 'icon' => '<svg></svg>', 'headline' => 'Fires are not religious', 'description' => 'Leave credentials behind.'],
        ],
        'photos' => ['event_types/fires/image.jpg', 'event_types/fires/image1.jpg'],
        'event_faqs' => [
            ['id' => 'faq1', 'question' => 'What about meals?', 'answer' => 'Keep it simple.'],
        ],
        'event_schedule' => [
            ['id' => 'sch1', 'type' => 'text', 'enabled' => true, 'text_block' => '## Schedule'],
            ['id' => 'sch2', 'type' => 'video', 'enabled' => true, 'embed_code' => '<iframe></iframe>', 'video_url' => 'https://vimeo.com/1'],
        ],
        'event_guide_pages' => [
            [
                'id' => 'page1',
                'type' => 'page',
                'enabled' => true,
                'page_title' => 'Welcome',
                'page_content' => [
                    ['id' => 'b1', 'type' => 'body_set', 'enabled' => true, 'body' => 'Start here.'],
                    ['id' => 'b2', 'type' => 'video_set', 'enabled' => true, 'video' => 'https://vimeo.com/2'],
                    ['id' => 'b3', 'type' => 'image_set', 'enabled' => false, 'image' => 'event_types/fires/image.jpg'],
                    [
                        'id' => 'b4',
                        'type' => 'accordion_set',
                        'enabled' => true,
                        'accordion' => [
                            [
                                'id' => 'a1',
                                'accordion_box_title' => 'Choose your date',
                                'accordion_box_content' => [
                                    ['id' => 'c1', 'type' => 'text_set', 'enabled' => true, 'body' => 'Four days.'],
                                    ['id' => 'c2', 'type' => 'video_set', 'enabled' => true, 'video' => 'https://vimeo.com/3'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'action_buttons' => [
            ['id' => 'btn1', 'button_label' => 'Downloads', 'button_url' => 'https://example.com/downloads', 'open_in_new_tab' => true],
        ],
    ];
}

it('builds typed collections for the event type content fields', function () {
    $eventType = EventType::factory()->create(statamicEventTypeContent());

    $data = EventTypeData::fromModel($eventType->fresh());

    expect($data->key_details)->toHaveCount(1)
        ->and($data->key_details[0])->toBeInstanceOf(KeyDetailData::class)
        ->and($data->key_details[0]->headline)->toBe('Fires are not religious')
        ->and($data->photos)->toBe(['event_types/fires/image.jpg', 'event_types/fires/image1.jpg'])
        ->and($data->event_faqs[0])->toBeInstanceOf(FaqData::class)
        ->and($data->event_faqs[0]->question)->toBe('What about meals?')
        ->and($data->event_schedule[0])->toBeInstanceOf(ScheduleBlockData::class)
        ->and($data->event_schedule[0]->type)->toBe(EventScheduleBlockTypeEnum::Text)
        ->and($data->event_schedule[1]->type)->toBe(EventScheduleBlockTypeEnum::Video)
        ->and($data->event_schedule[1]->video_url)->toBe('https://vimeo.com/1')
        ->and($data->action_buttons[0])->toBeInstanceOf(ActionButtonData::class)
        ->and($data->action_buttons[0]->open_in_new_tab)->toBeTrue()
        ->and($data->action_buttons[0]->button_entry)->toBeNull();

    $page = $data->event_guide_pages[0];

    expect($page)->toBeInstanceOf(GuidePageData::class)
        ->and($page->page_title)->toBe('Welcome')
        ->and($page->page_content)->toHaveCount(4)
        ->and($page->page_content[0])->toBeInstanceOf(ContentBlockData::class)
        ->and($page->page_content[0]->type)->toBe(EventGuideBlockTypeEnum::BodySet)
        ->and($page->page_content[2]->enabled)->toBeFalse()
        ->and($page->page_content[3]->accordion[0])->toBeInstanceOf(AccordionItemData::class)
        ->and($page->page_content[3]->accordion[0]->accordion_box_content[0]->type)->toBe(EventGuideBlockTypeEnum::TextSet)
        ->and($page->page_content[3]->accordion[0]->accordion_box_content[1]->video)->toBe('https://vimeo.com/3');
});

it('serialises the typed content back to the keys the frontends read', function () {
    $eventType = EventType::factory()->create(statamicEventTypeContent());

    $array = EventTypeData::fromModel($eventType->fresh())->toArray();

    expect($array['key_details'][0])->toMatchArray(['id' => 'kd1', 'headline' => 'Fires are not religious'])
        ->and($array['event_faqs'][0])->toBe(['id' => 'faq1', 'question' => 'What about meals?', 'answer' => 'Keep it simple.'])
        ->and($array['event_schedule'][0]['type'])->toBe('text')
        ->and($array['event_schedule'][0]['text_block'])->toBe('## Schedule')
        ->and($array['event_guide_pages'][0]['page_content'][0]['type'])->toBe('body_set')
        ->and($array['event_guide_pages'][0]['page_content'][3]['accordion'][0]['accordion_box_title'])->toBe('Choose your date')
        ->and($array['action_buttons'][0])->toMatchArray(['button_label' => 'Downloads', 'button_url' => 'https://example.com/downloads', 'open_in_new_tab' => true]);
});

it('drops the legacy duplicated fields from key details rows', function () {
    $eventType = EventType::factory()->create([
        'key_details' => [[
            'id' => 'legacy',
            'type' => 'event-type-detail-item',
            'icon' => '<svg></svg>',
            'headline' => 'Headline',
            'description' => 'Description',
            'fields' => ['icon' => '<svg></svg>', 'headline' => 'Headline', 'description' => 'Description'],
        ]],
    ]);

    $array = EventTypeData::fromModel($eventType->fresh())->toArray();

    expect($array['key_details'][0])->toBe([
        'id' => 'legacy',
        'icon' => '<svg></svg>',
        'headline' => 'Headline',
        'description' => 'Description',
    ]);
});

it('keeps untouched content fields null', function () {
    $eventType = EventType::factory()->create([
        'key_details' => null,
        'photos' => null,
        'event_faqs' => null,
        'event_schedule' => null,
        'event_guide_pages' => null,
        'action_buttons' => null,
    ]);

    $data = EventTypeData::fromModel($eventType->fresh());

    expect($data->key_details)->toBeNull()
        ->and($data->photos)->toBeNull()
        ->and($data->event_faqs)->toBeNull()
        ->and($data->event_schedule)->toBeNull()
        ->and($data->event_guide_pages)->toBeNull()
        ->and($data->action_buttons)->toBeNull();
});

it('builds typed collections on the v1 event type data', function () {
    $eventType = EventType::factory()->create(statamicEventTypeContent());

    $data = App\Data\V1\EventTypeData::fromEventType($eventType->fresh());

    expect($data->key_details[0])->toBeInstanceOf(KeyDetailData::class)
        ->and($data->event_faqs[0])->toBeInstanceOf(FaqData::class)
        ->and($data->event_schedule[0])->toBeInstanceOf(ScheduleBlockData::class)
        ->and($data->toArray()['event_faqs'][0]['answer'])->toBe('Keep it simple.');
});

it('exposes the event type photos as a list of asset paths on the v1 event data', function () {
    $eventType = EventType::factory()->create(statamicEventTypeContent());
    $event = Event::factory()->create(['event_type_id' => $eventType->id]);

    $data = EventData::fromEvent($event->fresh(['user', 'eventType']));

    expect($data->eventTypePhotos)->toBe(['event_types/fires/image.jpg', 'event_types/fires/image1.jpg']);
});
