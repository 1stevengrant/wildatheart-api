<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EventOwnershipTransfer;
use Illuminate\Container\Attributes\CurrentUser;
use App\Actions\Event\AcceptEventOwnershipTransfer;
use App\Exceptions\EventOwnershipTransferException;

class ConfirmOwnershipTransferController extends Controller
{
    /**
     * Process the ownership transfer acceptance
     */
    public function __invoke(Request $request, EventOwnershipTransfer $transfer, #[CurrentUser] User $user)
    {
        try {
            $event = AcceptEventOwnershipTransfer::run($transfer, $user);
        } catch (EventOwnershipTransferException $exception) {
            return to_route('home')->with([
                'notifications' => [
                    'message' => $exception->getMessage(),
                    'type' => 'error',
                ],
            ]);
        }

        return to_route('my.events.edit', $event)->with([
            'notifications' => [
                'message' => "You are now the owner of {$event->name}!",
                'type' => 'success',
            ],
        ]);
    }
}
