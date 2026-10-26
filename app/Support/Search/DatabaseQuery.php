<?php

namespace App\Support\Search;

use Statamic\Search\QueryBuilder;
use Illuminate\Support\Collection;

class DatabaseQuery extends QueryBuilder
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getSearchResults($query): Collection
    {
        return $this->index->lookup($query);
    }
}
