<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Http\Request;

class HostEventTypeMailingAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(EventType $eventType)
    {
        return inertia('Event/MailingAddressEventTypePage', [
            'eventType' => $eventType,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventType $eventType, Request $request)
    {
        //
    }
}
