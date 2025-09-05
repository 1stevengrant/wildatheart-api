<?php

namespace App\Services;

use Statamic\Entries\Entry;
use Illuminate\Http\Request;
use App\Http\Integrations\Transistor\TransistorConnector;
use App\Http\Controllers\API\V1\Content\DailyReadingController;
use App\Http\Integrations\Transistor\Requests\GetEpisodeRequest;

/**
 * This provides data in the required format for the app to render.
 * Only Daily Readings and Prayers are used at the moment.
 */
class SubsplashAppService
{
    private $baseUrl;

    public function __construct(private TransistorService $transistor)
    {
        $transistor = new TransistorService;
        $this->baseUrl = 'https://outpost.wildatheart.us';
    }

    public function getPrayerList()
    {
        // Get prayers from your database, ordered by your custom order field
        $prayers = Entry::query()->where('collection', 'prayers')->orderBy('order', 'asc')->get()->toArray();

        $items = [];
        foreach ($prayers as $prayer) {
            $items[] = [
                'title' => $prayer['title'],
                'actions' => [
                    [
                        'handler' => 'subtabs',
                        'url' => route('content.v1.subsplash.prayer', ['id' => $prayer['id']]),
                    ],
                ],
            ];
        }

        return [
            'header' => [
                'title' => 'Prayers',
                'style' => 'banner',
                'items' => [
                    [
                        'images' => [
                            [
                                'url' => 'https://b-cdn.wildatheart.org/files/prayers/wide-prayers.png',
                                'width' => 1920,
                                'height' => 692,
                            ],
                        ],
                    ],
                ],
            ],
            'items' => $items,
        ];
    }

    public function getPrayer($id)
    {
        $prayer = Entry::findOrFail($id)->toArray();
        $mp3 = null;
        if ($prayer['mp3']) {
            $bunnyCDNService = new BunnyCDNService;
            $mp3 = $bunnyCDNService->getBunnyCDNUrl($prayer['mp3']['path']);
        }

        $items = [

            [
                'title' => 'Read',
                'actions' => [
                    [
                        'handler' => 'browser',
                        'style' => 'internal',
                        'contentUrl' => config('app.frontend_url') . 'subsplash/prayers/' . $prayer['id'],
                    ],
                ],
            ],
        ];

        if ($mp3) {
            $items[] =
              [
                  'title' => 'Listen & Share',
                  'actions' => [
                      [
                          'handler' => 'detail',
                          'url' => route('content.v1.subsplash.listenPrayer', ['id' => $prayer['id']]),
                      ],
                  ],
              ];
        }

        return [
            'header' => [
                'title' => 'Prayer',
            ],
            'items' => $items,
        ];
    }

    public function getPrayerAudio($id)
    {
        $prayer = Entry::findOrFail($id)->toArray();
        $mp3 = null;
        if ($prayer['mp3']) {
            $bunnyCDNService = new BunnyCDNService;
            $mp3 = $bunnyCDNService->getBunnyCDNUrl($prayer['mp3']['path']);
        }

        return [
            'header' => [
                'title' => $prayer['title'],
            ],
            'media' => [
                [
                    'downloadable' => true,
                    'url' => $mp3 ?? '',
                    'format' => 'mp3',
                    'images' => [
                        [
                            'url' => 'https://b-cdn.wildatheart.org/files/prayers/square-prayers.png',
                            'width' => 1024,
                            'height' => 1024,
                        ],
                    ],
                ],
            ],
            'images' => [
                [
                    'url' => 'https://b-cdn.wildatheart.org/files/prayers/wide-prayers.png',
                    'width' => 1920,
                    'height' => 692,
                ],
            ],
            'actionSheet' => [
                'items' => [
                    [
                        'actions' => [
                            [
                                'handler' => 'defaultShare',
                                'url' => mb_trim(config('app.frontend_url'), '/') . $prayer['uri'],
                                'body' => 'Check this out from Wild at Heart - ' . $prayer['title'],
                            ],
                        ],
                    ],
                ],
            ],
            'body' => '',
            'subtitle' => '',
            'title' => $prayer['title'],
            'itemGroups' => [],

        ];
    }

    public function getDailyReadingToday()
    {
        return [
            'items' => [
                [
                    'title' => 'Read',
                    'actions' => [
                        [
                            'handler' => 'browser',
                            'style' => 'internal',
                            'contentUrl' => config('app.frontend_url') . 'subsplash/daily-readings',
                        ],
                    ],
                ],
                [
                    'title' => 'Listen & Share',
                    'actions' => [
                        [
                            'handler' => 'mediaDetail',
                            'url' => route('content.v1.subsplash.dailyReadingsListen'),
                        ],
                    ],
                ],
                [
                    'title' => 'Archive',
                    'actions' => [
                        [
                            'handler' => 'list',
                            'url' => route('content.v1.subsplash.dailyReadingsList'),
                        ],
                    ],
                ],
            ],
        ];

        return $this->transformDailyReading();
    }

    public function getDailyReading($id)
    {

        $reading = $this->transistor->getEpisode($id, 'wildatheart-daily');

        return [
            'items' => [
                [
                    'title' => 'Read',
                    'actions' => [
                        [
                            'handler' => 'browser',
                            'style' => 'internal',
                            'contentUrl' => config('app.frontend_url') . 'subsplash/daily-readings/' . data_get($reading, 'attributes.slug'),
                        ],
                    ],
                ],
                [
                    'title' => 'Listen & Share',
                    'actions' => [
                        [
                            'handler' => 'mediaDetail',
                            'url' => route('content.v1.subsplash.dailyReadingsListen', ['id' => data_get($reading, 'id')]),
                        ],
                    ],
                ],
                [
                    'title' => 'Archive',
                    'actions' => [
                        [
                            'handler' => 'list',
                            'url' => route('content.v1.subsplash.dailyReadingsList', ['id' => data_get($reading, 'data.id')]),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getDailyReadingList()
    {
        $readings = $this->transistor->getRecent('wildatheart-daily', 30);

        $items = [];
        foreach ($readings as $reading) {
            $items[] = [
                'title' => data_get($reading, 'attributes.title'),
                'date' => date('D, d M Y h:i:s T', strtotime(data_get($reading, 'attributes.published_at'))),
                'actions' => [
                    [
                        'handler' => 'subtabs',
                        'url' => route('content.v1.subsplash.dailyReadingById', ['id' => data_get($reading, 'id')]),
                    ],
                ],
            ];
        }

        return [
            'header' => [
                'title' => 'Archive',
                'style' => 'banner',
                'items' => [
                    [
                        'images' => [
                            [
                                'url' => 'https://b-cdn.wildatheart.org/files/daily-readings/daily-reading-banner.png',
                                'width' => 1920,
                                'height' => 692,
                            ],
                        ],
                    ],
                ],
            ],
            'items' => $items,
        ];
    }

    public function getDailyReadingAudio($id = null)
    {
        $reading = null;

        if ($id) {
            // Get a specific daily reading
            $reading = $this->transistor->getEpisode($id, 'wildatheart-daily');
        } else {
            // Get the most recent daily reading
            $dailyReadingController = app(DailyReadingController::class);
            $request = new Request;
            $request->merge(['limit' => 1]);
            $response = $dailyReadingController->getRecent($request)->first()->toArray();
            $reading = data_get($response, 'transistor_episode');
        }

        $title = data_get($reading, 'attributes.title');
        $mp3 = data_get($reading, 'attributes.media_url');
        $uri = '/daily-readings/' . data_get($reading, 'attributes.slug');

        return [
            'header' => [
                'title' => $title,
            ],
            'media' => [
                [
                    'downloadable' => true,
                    'url' => $mp3 ?? '',
                    'format' => 'mp3',
                    'images' => [
                        [
                            'url' => 'https://b-cdn.wildatheart.org/files/daily-readings/daily-reading-square.png',
                            'width' => 1024,
                            'height' => 1024,
                        ],
                    ],
                ],
            ],
            'images' => [
                [
                    'url' => 'https://b-cdn.wildatheart.org/files/daily-readings/daily-reading-wide.png',
                    'width' => 1920,
                    'height' => 692,
                ],
            ],
            'actionSheet' => [
                'items' => [
                    [
                        'actions' => [
                            [
                                'handler' => 'defaultShare',
                                'url' => mb_trim(config('app.frontend_url'), '/') . $uri,
                                'body' => 'Check this out from Wild at Heart - ' . $title,
                            ],
                        ],
                    ],
                ],
            ],
            'body' => '',
            'subtitle' => '',
            'title' => $title,
            'itemGroups' => [],
        ];
    }
}
