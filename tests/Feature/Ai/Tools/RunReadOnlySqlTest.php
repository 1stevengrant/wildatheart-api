<?php

use Laravel\Ai\Tools\Request;
use App\Ai\Tools\RunReadOnlySql;

function runReadOnlySql(string $sql): array
{
    return json_decode(app(RunReadOnlySql::class)->handle(new Request(['sql' => $sql, 'purpose' => 'Testing'])), true);
}

it('returns rows for a valid query', function () {
    expect(runReadOnlySql('SELECT 1 AS one FROM states LIMIT 1'))->toBe([
        'rows' => [['one' => 1]],
        'row_count' => 1,
        'truncated' => false,
    ]);
});

it('returns rejections to the model instead of throwing', function () {
    expect(runReadOnlySql('DELETE FROM events'))
        ->toBe(['error' => 'Query rejected: Only SELECT queries (optionally starting with WITH) are allowed.']);
});

it('returns database errors to the model instead of throwing', function () {
    expect(runReadOnlySql('SELECT missing_column FROM events')['error'])
        ->toStartWith('Query failed:')
        ->toContain('missing_column');
});
