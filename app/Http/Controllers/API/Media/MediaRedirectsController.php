<?php

namespace App\Http\Controllers\API\Media;

use Illuminate\Http\Response;
use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;

class MediaRedirectsController extends Controller
{
    public function index(): Response
    {
        $mediaItems = EntryQueryBuilder::mediaItems()->get();

        $output = '';
        foreach ($mediaItems as $item) {
            if ($item->drupal_url) {
                $output .= "{$item->drupal_url} {$item->uri} 301\n";
            }
        }

        return response($output)->header('Content-Type', 'text/plain');
    }
}
