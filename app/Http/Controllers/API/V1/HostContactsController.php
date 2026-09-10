<?php

namespace App\Http\Controllers\API\V1;

use App\Data\V1\HostContactData;
use App\Models\EventUserInterest;
use Illuminate\Http\JsonResponse;
use App\Queries\HostContactsQuery;
use App\Http\Controllers\Controller;
use App\Data\V1\HostContactsIndexData;

class HostContactsController extends Controller
{
    public function __construct(private readonly HostContactsQuery $contactsQuery) {}

    public function index(HostContactsIndexData $data): JsonResponse
    {
        $host = $this->user();

        $contacts = $this->contactsQuery->paginate($host, $data);

        $contacts->getCollection()->transform(
            fn (EventUserInterest $interest): HostContactData => HostContactData::fromInterest($interest)
        );

        return response()->json($contacts);
    }
}
