<?php

namespace App\Http\Controllers;
use Statamic\Entries\Entry;
use Illuminate\Http\Request;

class MediaSeriesDataController extends Controller
{
    public function show($slug)
    {
          $entry = Entry::query()
              ->where('slug', $slug)
              ->firstOrFail();

          // Return the view with the merged data
          return response()->json($entry);
    }
}
