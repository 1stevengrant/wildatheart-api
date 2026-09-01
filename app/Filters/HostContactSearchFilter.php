<?php

namespace App\Filters;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\EventUserInterest;
use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

final class HostContactSearchFilter implements Filter
{
    /** @param array<int, string> $eventIds */
    public function __construct(private readonly array $eventIds) {}

    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $search = Str::of(is_array($value) ? implode(',', $value) : (string) $value)
            ->squish()
            ->lower()
            ->toString();

        if ($search === '') {
            return;
        }

        $matchingUserIds = User::query()
            ->whereIn('users.id', EventUserInterest::query()
                ->select('user_id')
                ->whereIn('event_id', $this->eventIds))
            ->select(['users.id', 'users.given_name', 'users.family_name', 'users.email', 'users.phone'])
            ->cursor()
            ->filter(fn (User $contact): bool => $this->matches($contact, $search))
            ->pluck('id')
            ->all();

        $query->whereIn('event_user_interests.user_id', $matchingUserIds);
    }

    private function matches(User $contact, string $search): bool
    {
        $searchableValues = [
            $contact->given_name,
            $contact->family_name,
            $contact->email,
            $contact->phone,
        ];

        $hasPartialMatch = collect($searchableValues)
            ->filter()
            ->contains(fn (string $value): bool => Str::contains(Str::lower($value), $search));

        if ($hasPartialMatch) {
            return true;
        }

        $searchTokens = $this->tokens($search);
        $nameTokens = $this->tokens("{$contact->given_name} {$contact->family_name}");

        return collect($searchTokens)->every(
            fn (string $searchToken): bool => collect($nameTokens)->contains(
                fn (string $nameToken): bool => $this->isFuzzyMatch($searchToken, $nameToken),
            ),
        );
    }

    /** @return array<int, string> */
    private function tokens(string $value): array
    {
        return preg_split('/\s+/', Str::of($value)->squish()->lower()->toString(), flags: PREG_SPLIT_NO_EMPTY) ?: [];
    }

    private function isFuzzyMatch(string $searchToken, string $nameToken): bool
    {
        $longerLength = max(Str::length($searchToken), Str::length($nameToken));

        if ($longerLength < 4) {
            return false;
        }

        $distance = levenshtein($searchToken, $nameToken);

        return $distance <= 2 && ($distance / $longerLength) <= 0.4;
    }
}
