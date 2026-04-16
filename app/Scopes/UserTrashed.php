<?php

namespace App\Scopes;

use Statamic\Query\Scopes\Filter;

class UserTrashed extends Filter
{
    public $pinned = true;

    public static function title(): string
    {
        return __('Trashed');
    }

    public function fieldItems(): array
    {
        return [
            'trashed' => [
                'type' => 'radio',
                'default' => 'without',
                'options' => [
                    'without' => __('Without trashed'),
                    'with' => __('With trashed'),
                    'only' => __('Only trashed'),
                ],
            ],
        ];
    }

    public function autoApply(): array
    {
        return ['trashed' => 'without'];
    }

    public function apply($query, $values): void
    {
        $column = $query->getModel()->qualifyColumn('deleted_at');

        match ($values['trashed'] ?? 'without') {
            'only' => $query->whereNotNull($column),
            'with' => null,
            default => $query->whereNull($column),
        };
    }

    public function badge($values): string
    {
        return match ($values['trashed'] ?? 'without') {
            'only' => __('Only trashed'),
            'with' => __('With trashed'),
            default => __('Without trashed'),
        };
    }

    public function visibleTo($key): bool
    {
        return $key === 'runway' && ($this->context['resource'] ?? null) === 'user';
    }
}
