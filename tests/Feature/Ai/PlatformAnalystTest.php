<?php

use App\Models\User;
use App\Ai\Tools\GetEvent;
use App\Jobs\LogActivityJob;
use App\Ai\Tools\QueryEvents;
use Laravel\Ai\Tools\Request;
use App\Ai\Tools\RunReadOnlySql;
use App\Ai\Agents\PlatformAnalyst;
use Laravel\Ai\Events\ToolInvoked;
use Illuminate\Support\Facades\Bus;
use App\Listeners\LogPlatformAnalystSqlQueries;

it('uses the configured anthropic model and every tool', function () {
    config(['ai.ask.model' => 'claude-test-model']);

    $agent = app(PlatformAnalyst::class);

    expect($agent->model())->toBe('claude-test-model')
        ->and(collect($agent->tools())->map(fn ($tool) => $tool::class)->all())->toContain(QueryEvents::class, GetEvent::class, RunReadOnlySql::class);
});

it('includes the schema and never the secret columns in its instructions', function () {
    $instructions = (string) app(PlatformAnalyst::class)->instructions();

    expect($instructions)
        ->toContain('Today is ' . now()->toDateString())
        ->toContain('- events(id char')
        ->toContain('Fires (slug: fires, type: gathering)')
        ->not->toContain('password')
        ->not->toContain('two_factor_secret')
        ->not->toContain('captured_emails');
});

it('logs read only sql calls to the activity log', function () {
    Bus::fake([LogActivityJob::class]);

    $user = User::factory()->create(['super' => true]);
    $agent = app(PlatformAnalyst::class)->forUser($user);
    $tool = app(RunReadOnlySql::class);
    $arguments = ['sql' => 'SELECT 1 AS one FROM states LIMIT 1', 'purpose' => 'Testing the audit log'];

    (new LogPlatformAnalystSqlQueries)->handle(new ToolInvoked(
        invocationId: 'invocation',
        toolInvocationId: 'tool-invocation',
        agent: $agent,
        tool: $tool,
        arguments: $arguments,
        result: $tool->handle(new Request($arguments)),
        time: 1.0,
    ));

    Bus::assertDispatched(LogActivityJob::class, fn (LogActivityJob $job) => $job->logName === 'platform-analyst'
        && $job->causedBy->is($user)
        && $job->properties['sql'] === $arguments['sql']
        && $job->properties['purpose'] === 'Testing the audit log'
        && $job->properties['row_count'] === 1);
});

it('does not log other tools', function () {
    Bus::fake([LogActivityJob::class]);

    (new LogPlatformAnalystSqlQueries)->handle(new ToolInvoked(
        invocationId: 'invocation',
        toolInvocationId: 'tool-invocation',
        agent: app(PlatformAnalyst::class),
        tool: app(GetEvent::class),
        arguments: ['name' => 'Fire'],
        result: '{}',
        time: 1.0,
    ));

    Bus::assertNotDispatched(LogActivityJob::class);
});
