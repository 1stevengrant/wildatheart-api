<?php

namespace App\Services;

use App\Services\TransistorService;
use Statamic\Entries\Entry;

/**
 * This provides data in the required format for the app to render.
 * Only Daily Readings and Prayers are used at the moment.
 */
class SubsplashAppService
{
  private $baseUrl;

  function __construct(private TransistorService $transistor)
  {
    $transistor = new TransistorService();
    $this->baseUrl = "https://outpost.wildatheart.us";
  }

  public function getPrayerList()
  {
    // Get prayers from your database, ordered by your custom order field
    $prayers = Entry::query()->where("collection", "prayers")->orderBy('order_by')->get()->toArray();

    return $this->transformPrayerList($prayers, $this->baseUrl);
  }

  public function getPrayer($id)
  {
    $prayer = Entry::findOrFail($id)->toArray();

    return $this->transformPrayer($prayer, $this->baseUrl);
  }

  public function getPrayerAudio($id)
  {
    $prayer = Entry::findOrFail($id)->toArray();

    return $this->transformPrayerAudio($prayer, $this->baseUrl);
  }

  public function getPrayerRead($id)
  {
    $prayer = Entry::findOrFail($id)->toArray();

    return $this->transformPrayerRead($prayer, $this->baseUrl);
  }

  public function getDailyReadingList()
  {
    $readings = $this->transistor->getRecent('wildatheart-daily', 30);
    return $this->transformDailyReadingList($readings, $this->baseUrl);
  }

  // public function getDailyReading($id)
  // {
  //   $reading = DailyReading::findOrFail($id)->toArray();

  //   return $this->transformDailyReading($reading, $this->baseUrl);
  // }
  /**
   * Transform a prayer into the required JSON format
   * 
   * @param array $prayer Prayer data from your database
   * @param string $baseUrl Your application's base URL
   * @return array
   */
  public function transformPrayer($prayer, $baseUrl)
  {

    // dd($prayer);
    $mp3 = null;
    if ($prayer['mp3']) {
      $bunnyCDNService = new BunnyCDNService;
      $mp3 = $bunnyCDNService->getBunnyCDNUrl($prayer['mp3']['path']);
    }

    return [
      'header' => [
        'title' => 'Prayer'
      ],
      'items' => [
        [
          'title' => 'Read',
          'actions' => [
            [
              'handler' => 'browser',
              'style' => 'internal',
              'contentUrl' => config("app.frontend_url") . "subsplash/prayers/" . $prayer['id']
            ]
          ]
        ],
        [
          'title' => 'Listen & Share',
          'actions' => [
            [
              'handler' => 'detail',
              'url' => route('content.v1.subsplash.listenPrayer', ['id' => $prayer['id']])
            ]
          ]
        ]
      ]
    ];
    return [

      'media' => [
        [
          'downloadable' => true,
          'url' => $mp3 ?? '',
          'format' => 'mp3',
          'images' => [
            [
              'url' => 'https://b-cdn.wildatheart.org/files/prayers/wide-prayers.png'
            ]
          ]
        ]
      ],
      'images' => [
        [
          'url' => 'https://b-cdn.wildatheart.org/files/prayers/wide-prayers.png',
          "width" => 1920,
          "height" => 692
        ],
        "actionSheet" => [
          "items" => [
            [
              "actions" => [
                [
                  "handler" => "defaultShare",
                  "body" => "Check this out from Wild at Heart - " . $prayer['title']
                ]
              ]
            ]
          ]
        ],
        "body" => "",
        "subtitle" => "",
        "title" => $prayer['title'],
        "itemGroups" => []

      ]
    ];
  }

  public function transformPrayerAudio($prayer, $baseUrl)
  {

    // dd($prayer);
    $mp3 = null;
    if ($prayer['mp3']) {
      $bunnyCDNService = new BunnyCDNService;
      $mp3 = $bunnyCDNService->getBunnyCDNUrl($prayer['mp3']['path']);
    }

    return [
      "header" => [
        'title' => $prayer['title'],
      ],
      'media' => [
        [
          'downloadable' => true,
          'url' => $mp3 ?? '',
          'format' => 'mp3',
          'images' => [
            [
              'url' => 'https://b-cdn.wildatheart.org/files/prayers/wide-prayers.png'
            ]
          ]
        ]
      ],
      'images' => [
        [
          'url' => 'https://b-cdn.wildatheart.org/files/prayers/wide-prayers.png',
          "width" => 1920,
          "height" => 692
        ],
      ],
      "actionSheet" => [
        "items" => [
          [
            "actions" => [
              [
                "handler" => "defaultShare",
                "url" => trim(config('app.frontend_url'), "/") . $prayer['uri'],
                "body" => "Check this out from Wild at Heart - " . $prayer['title']
              ]
            ]
          ]
        ]
      ],
      "body" => "",
      "subtitle" => "",
      "title" => $prayer['title'],
      "itemGroups" => []

    ];
  }

  public function transformPrayerRead($prayer, $baseUrl)
  {
    return $prayer['content'];
  }

  /**
   * Transform a list of prayers into the required JSON format
   * 
   * @param array $prayers Array of prayer data from your database
   * @param string $baseUrl Your application's base URL
   * @return array
   */
  public function transformPrayerList($prayers, $baseUrl)
  {
    $items = [];
    foreach ($prayers as $prayer) {
      $items[] = [
        'title' => $prayer['title'],
        'actions' => [
          [
            'handler' => 'subtabs',
            'url' => route('content.v1.subsplash.prayer', ['id' => $prayer['id']])
          ]
        ]
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
                'height' => 692
              ]
            ]
          ]
        ]
      ],
      'items' => $items
    ];
  }

  /**
   * Transform a daily reading into the required JSON format
   * 
   * @param array $reading Daily reading data from your database
   * @param string $baseUrl Your application's base URL
   * @return array
   */
  public function transformDailyReading($reading, $baseUrl)
  {
    return [
      'title' => $reading['title'],
      'media' => [
        [
          'downloadable' => true,
          'url' => $reading['audio_url'] ?? '',
          'format' => 'mp3',
          'images' => [
            [
              'url' => $baseUrl . '/assets/images/wah/wah-daily-reading-wide@1x.jpg'
            ]
          ]
        ]
      ],
      'images' => [
        [
          'url' => $baseUrl . '/assets/images/wah/wah-daily-reading-wide@3x.jpg'
        ],
        [
          'url' => $baseUrl . '/assets/images/wah/wah-daily-reading-wide@2x.jpg'
        ],
        [
          'url' => $baseUrl . '/assets/images/wah/wah-daily-reading-wide@1x.jpg'
        ]
      ]
    ];
  }

  /**
   * Transform a list of daily readings into the required JSON format
   * 
   * @param array $readings Array of daily reading data from your database
   * @param string $baseUrl Your application's base URL
   * @return array
   */
  public function transformDailyReadingList($readings, $baseUrl)
  {
    $items = [];
    foreach ($readings as $reading) {
      $items[] = [
        'title' => $reading['title'],
        'date' => date("D, d M Y h:i:s T", strtotime($reading['created_at'])),
        'actions' => [
          [
            'handler' => 'subtabs',
            'url' => $baseUrl . '/daily-reading/' . $reading['id']
          ]
        ]
      ];
    }

    return [
      'header' => [
        'title' => 'Daily Readings',
        'style' => 'banner',
        'items' => [
          [
            'images' => [
              [
                'url' => $baseUrl . '/assets/images/wah/wah-daily-reading-banner@3x.jpg',
                'width' => 1920,
                'height' => 692
              ]
            ]
          ]
        ]
      ],
      'items' => $items
    ];
  }

  /**
   * Transform content tabs for a prayer or daily reading
   * 
   * @param array $content Content data from your database
   * @param string $baseUrl Your application's base URL
   * @param string $type Either 'prayer' or 'daily-reading'
   * @return array
   */
  public function transformContentTabs($content, $baseUrl, $type)
  {
    $items = [
      [
        'title' => 'Read',
        'actions' => [
          [
            'handler' => 'browser',
            'style' => 'internal',
            'contentUrl' => $baseUrl . '/' . $type . '/' . $content['id'] . '/read'
          ]
        ]
      ]
    ];

    // Only add audio tab if audio URL exists
    if (!empty($content['audio_url'])) {
      $items[] = [
        'title' => 'Listen & Share',
        'actions' => [
          [
            'handler' => 'detail',
            'url' => $baseUrl . '/' . $type . '/' . $content['id'] . '/audio'
          ]
        ]
      ];
    }

    return [
      'header' => [
        'title' => ucfirst($type)
      ],
      'items' => $items
    ];
  }
}
