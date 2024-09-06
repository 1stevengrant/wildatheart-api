<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventController extends Controller
{
    public function __invoke()
    {
        return Event::all();
    }
}
