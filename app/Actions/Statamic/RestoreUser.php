<?php

namespace App\Actions\Statamic;

use App\Models\User;
use Statamic\Actions\Action;

class RestoreUser extends Action
{
    public static function title(): string
    {
        return __('Restore');
    }

    public function visibleTo($item): bool
    {
        return $item instanceof User && $item->trashed();
    }

    public function visibleToBulk($items): bool
    {
        return $items
            ->map(fn ($item) => $this->visibleTo($item))
            ->filter(fn ($isVisible) => $isVisible === true)
            ->count() === $items->count();
    }

    public function authorize($user, $item): bool
    {
        return $user->can('restore', $item);
    }

    public function buttonText(): string
    {
        /* @translation */
        return 'Restore|Restore :count users?';
    }

    public function confirmationText(): string
    {
        /* @translation */
        return 'Are you sure you want to restore this user?|Are you sure you want to restore these :count users?';
    }

    public function bypassesDirtyWarning(): bool
    {
        return true;
    }

    public function run($items, $values): string
    {
        $failures = $items->reject(fn (User $user) => $user->restore());
        $total = $items->count();

        if ($failures->isNotEmpty()) {
            $success = $total - $failures->count();

            if ($total === 1) {
                throw new \Exception(__('User could not be restored'));
            }
            if ($success === 0) {
                throw new \Exception(__('Users could not be restored'));
            }
            throw new \Exception(__(':success/:total users were restored', ['total' => $total, 'success' => $success]));
        }

        return trans_choice('User restored|Users restored', $total);
    }
}
