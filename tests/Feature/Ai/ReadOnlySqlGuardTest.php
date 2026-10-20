<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use App\Ai\Support\ReadOnlySqlGuard;
use Illuminate\Database\QueryException;
use App\Exceptions\UnsafeQueryException;

beforeEach(function () {
    Event::unsetEventDispatcher();
    User::unsetEventDispatcher();

    $this->guard = new ReadOnlySqlGuard;
});

it('accepts select and with queries', function (string $sql) {
    expect($this->guard->validate($sql))->toBe(mb_rtrim($sql, ';'));
})->with([
    'select' => 'SELECT COUNT(*) FROM events WHERE deleted_at IS NULL',
    'lowercase with join' => 'select e.name, u.email from events e join users u on u.id = e.user_id',
    'trailing semicolon' => 'SELECT id FROM events;',
    'cte' => 'WITH recent AS (SELECT id FROM events WHERE created_at > NOW()) SELECT COUNT(*) FROM recent',
    'subquery' => 'SELECT COUNT(*) FROM (SELECT user_id FROM events GROUP BY user_id) AS hosts',
    'email literal' => "SELECT id FROM users WHERE email = 'host@example.com'",
]);

it('rejects unsafe queries', function (string $sql, string $message) {
    expect(fn () => $this->guard->validate($sql))->toThrow(UnsafeQueryException::class, $message);
})->with([
    'empty' => ['  ', 'The query is empty.'],
    'update' => ['UPDATE events SET name = 1', 'Only SELECT queries'],
    'delete' => ['DELETE FROM events', 'Only SELECT queries'],
    'drop' => ['DROP TABLE events', 'Only SELECT queries'],
    'multiple statements' => ['SELECT 1 FROM events; DELETE FROM events', 'Only a single statement'],
    'line comment' => ['SELECT id FROM events -- hi', 'Comments are not allowed.'],
    'block comment' => ['SELECT /* hi */ id FROM events', 'Comments are not allowed.'],
    'into outfile' => ["SELECT id FROM events INTO OUTFILE '/tmp/x'", 'SELECT ... INTO is not allowed.'],
    'sleep' => ['SELECT SLEEP(10) FROM events', 'That function is not allowed.'],
    'locking read' => ['SELECT id FROM events FOR UPDATE', 'Locking reads are not allowed.'],
    'system variables' => ['SELECT @@version FROM events', 'System variables are not allowed.'],
    'information schema' => ['SELECT table_name FROM information_schema.tables', 'System schemas are not allowed.'],
    'password column' => ['SELECT password FROM users', 'Secret columns cannot be queried.'],
    'two factor column' => ['SELECT two_factor_secret FROM users', 'Secret columns cannot be queried.'],
    'disallowed table' => ['SELECT * FROM captured_emails', 'Table `captured_emails` is not allowed.'],
    'disallowed joined table' => ['SELECT * FROM events JOIN sessions ON 1 = 1', 'Table `sessions` is not allowed.'],
    'comma join' => ['SELECT * FROM events e, captured_emails c', 'Use explicit JOIN syntax'],
    'schema qualified' => ['SELECT * FROM other_db.events', 'Schema qualified table'],
]);

it('runs a query and caps the rows', function () {
    $user = User::factory()->create();

    Event::factory()->fires()->count(3)->create(['user_id' => $user->id]);

    $result = $this->guard->run("SELECT id FROM events WHERE user_id = {$user->id}");

    expect($result['row_count'])->toBe(3)
        ->and($result['truncated'])->toBeFalse();
});

it('flags truncated results', function () {
    $sql = 'WITH RECURSIVE numbers AS (SELECT 1 AS n UNION ALL SELECT n + 1 FROM numbers WHERE n < 250) SELECT n FROM numbers';

    $result = $this->guard->run($sql);

    expect($result['row_count'])->toBe(ReadOnlySqlGuard::ROW_LIMIT)
        ->and($result['truncated'])->toBeTrue();
});

it('strips secret columns from select star results', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    $result = $this->guard->run("SELECT * FROM users WHERE id = {$user->id}");

    expect($result['rows'][0])
        ->toHaveKey('email')
        ->not->toHaveKey('password')
        ->not->toHaveKey('remember_token')
        ->not->toHaveKey('two_factor_secret');
});

it('runs the read only connection in a read only session', function () {
    expect(fn () => DB::connection('ai_readonly')->update('UPDATE users SET name = name WHERE id = 0'))
        ->toThrow(QueryException::class, 'READ ONLY');

    DB::connection('ai_readonly')->disconnect();
});
