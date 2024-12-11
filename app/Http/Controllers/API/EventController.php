<?php

namespace App\Http\Controllers\API;

use App\Models\Event;
use App\Http\Controllers\Controller;

class EventController extends Controller
{
    public function __invoke()
    {
        return Event::all();
    }
}
