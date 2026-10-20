<?php

namespace App\Ai\Support;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Exceptions\UnsafeQueryException;

/**
 * Validates and runs model-written SQL against the read-only connection.
 *
 * The production connection uses a MySQL user that only has SELECT grants,
 * and every connection runs in a read-only session. These checks are the
 * application level layer on top of that, so a misconfigured connection
 * still cannot be used to write, read secrets or reach other schemas.
 */
class ReadOnlySqlGuard
{
    public const int ROW_LIMIT = 200;

    public const int MAX_VALUE_LENGTH = 500;

    /** @var array<int, string> */
    public const array ALLOWED_TABLES = [
        'events',
        'event_types',
        'users',
        'attendees',
        'addresses',
        'event_user',
        'event_user_interests',
        'states',
    ];

    /** @var array<int, string> */
    public const array SECRET_COLUMNS = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /** @var array<string, string> */
    private const array FORBIDDEN_PATTERNS = [
        '/\bINTO\b/i' => 'SELECT ... INTO is not allowed.',
        '/@@/' => 'System variables are not allowed.',
        '/\b(LOAD_FILE|SLEEP|BENCHMARK|GET_LOCK|RELEASE_LOCK|RELEASE_ALL_LOCKS)\s*\(/i' => 'That function is not allowed.',
        '/\bFOR\s+(UPDATE|SHARE)\b/i' => 'Locking reads are not allowed.',
        '/\bLOCK\s+IN\s+SHARE\s+MODE\b/i' => 'Locking reads are not allowed.',
        '/\b(information_schema|performance_schema|mysql|sys)\s*\./i' => 'System schemas are not allowed.',
    ];

    /**
     * @return array{
     *     rows: array<int, array<string, mixed>>,
     *     row_count: int,
     *     truncated: bool
     * }
     */
    public function run(string $sql): array
    {
        $sql = $this->validate($sql);

        $limit = self::ROW_LIMIT + 1;

        $rows = DB::connection(config('ai.ask.connection'))
            ->select("SELECT * FROM ({$sql}) AS ai_query LIMIT {$limit}");

        $truncated = count($rows) > self::ROW_LIMIT;

        $rows = collect($rows)
            ->take(self::ROW_LIMIT)
            ->map(fn (object $row) => $this->sanitizeRow((array) $row))
            ->all();

        return [
            'rows' => $rows,
            'row_count' => count($rows),
            'truncated' => $truncated,
        ];
    }

    public function validate(string $sql): string
    {
        $sql = mb_trim($sql);
        $sql = mb_rtrim($sql, "; \n\r\t");

        if ($sql === '') {
            throw new UnsafeQueryException('The query is empty.');
        }

        if (str_contains($sql, ';')) {
            throw new UnsafeQueryException('Only a single statement is allowed.');
        }

        if (preg_match('/--|#|\/\*/', $sql)) {
            throw new UnsafeQueryException('Comments are not allowed.');
        }

        if (! preg_match('/^(SELECT|WITH)\b/i', $sql)) {
            throw new UnsafeQueryException('Only SELECT queries (optionally starting with WITH) are allowed.');
        }

        foreach (self::FORBIDDEN_PATTERNS as $pattern => $message) {
            if (preg_match($pattern, $sql)) {
                throw new UnsafeQueryException($message);
            }
        }

        $secretColumns = implode('|', self::SECRET_COLUMNS);

        if (preg_match("/\b({$secretColumns})\b/i", $sql)) {
            throw new UnsafeQueryException('Secret columns cannot be queried.');
        }

        if (preg_match('/\bFROM\s+`?\w+`?(\s+(AS\s+)?`?\w+`?)?\s*,/i', $sql)) {
            throw new UnsafeQueryException('Use explicit JOIN syntax instead of comma separated tables.');
        }

        $this->ensureTablesAreAllowed($sql);

        return $sql;
    }

    private function ensureTablesAreAllowed(string $sql): void
    {
        preg_match_all('/(?:\bWITH(?:\s+RECURSIVE)?|,)\s*`?(\w+)`?\s*(?:\([^)]*\))?\s+AS\s*\(/i', $sql, $cteMatches);

        $cteNames = array_map('strtolower', $cteMatches[1]);

        preg_match_all('/\b(?:FROM|JOIN)\s+`?(\w+)`?(\s*\.\s*`?\w+`?)?/i', $sql, $tableMatches, PREG_SET_ORDER);

        foreach ($tableMatches as $match) {
            $table = mb_strtolower($match[1]);

            if (! empty($match[2])) {
                throw new UnsafeQueryException("Schema qualified table `{$match[0]}` is not allowed.");
            }

            if (in_array($table, $cteNames, true)) {
                continue;
            }

            if (in_array($table, self::ALLOWED_TABLES, true)) {
                continue;
            }

            throw new UnsafeQueryException(
                "Table `{$table}` is not allowed. Allowed tables: " . implode(', ', self::ALLOWED_TABLES) . '. '
                . 'If this came from EXTRACT(... FROM column) or TRIM(... FROM column), use YEAR(), MONTH() or TRIM(column) instead.'
            );
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function sanitizeRow(array $row): array
    {
        return collect($row)
            ->except(self::SECRET_COLUMNS)
            ->map(fn (mixed $value) => is_string($value) ? Str::limit($value, self::MAX_VALUE_LENGTH) : $value)
            ->all();
    }
}
