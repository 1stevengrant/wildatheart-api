<?php

namespace App\Http\Controllers\API\V1\Content;

use App\DataTypeObjects\API\V1\Content\AlertResponse;
use App\Http\Controllers\Controller;
use Statamic\Entries\Entry;

class AlertEntryController extends Controller
{
  public function index()
  {
    $entries = Entry::query()
      ->where('collection', 'alerts')
      ->where('published', true)
      ->where('start_date', '<=', now()->format('Y-m-d H:i:s'))
      ->where('end_date', '>=', now()->format('Y-m-d H:i:s'))
      ->orderBy('start_date', 'desc')
      ->get();

    if ($entries->isEmpty()) {
      return AlertResponse::collection([]);
    }

    $entries = $entries->map(function ($entry) {
      $entry = $entry->toArray();
      $entry['link_url'] = $entry['link_url'] ? $entry['link_url']['url'] : null;
      return $entry;
    });


    return AlertResponse::collection($entries);
  }
}
