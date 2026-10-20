<?php

namespace App\Ai\Tools;

use Stringable;
use Laravel\Ai\Tools\Request;
use Laravel\Ai\Contracts\Tool;
use App\Ai\Support\ReadOnlySqlGuard;
use Illuminate\Database\QueryException;
use App\Exceptions\UnsafeQueryException;
use App\Ai\Tools\Concerns\EncodesToolResults;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class RunReadOnlySql implements Tool
{
    use EncodesToolResults;

    public function __construct(private ReadOnlySqlGuard $guard) {}

    public function description(): Stringable|string
    {
        return 'Run a single read-only MySQL SELECT (or WITH ... SELECT) against the allowlisted tables. Use only when the other tools cannot answer the question. '
            . 'Results are capped at ' . ReadOnlySqlGuard::ROW_LIMIT . ' rows. Use explicit JOINs, alias duplicate column names, no comments or semicolons.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'sql' => $schema->string()->description('The SELECT statement to run.')->required(),
            'purpose' => $schema->string()->description('One sentence describing what the query answers. Logged for auditing.')->required(),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $request->validate([
            'sql' => ['required', 'string', 'max:5000'],
            'purpose' => ['required', 'string', 'max:500'],
        ]);

        try {
            return $this->encode($this->guard->run($request->string('sql')->value()));
        } catch (UnsafeQueryException $exception) {
            return $this->encode(['error' => "Query rejected: {$exception->getMessage()}"]);
        } catch (QueryException $exception) {
            return $this->encode(['error' => 'Query failed: ' . ($exception->errorInfo[2] ?? $exception->getMessage())]);
        }
    }
}
