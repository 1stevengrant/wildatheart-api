<?php

namespace App\Ai\Agents;

use Stringable;
use Laravel\Ai\Enums\Lab;
use App\Ai\Tools\GetEvent;
use Laravel\Ai\Promptable;
use App\Ai\Tools\QueryHosts;
use App\Ai\Tools\QueryEvents;
use App\Ai\Tools\PlatformStats;
use Laravel\Ai\Contracts\Agent;
use App\Ai\Tools\QueryAttendees;
use App\Ai\Tools\RunReadOnlySql;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\MaxTokens;
use App\Ai\Support\SchemaDescription;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Attributes\CacheInstructions;
use Laravel\Ai\Attributes\CacheToolDefinitions;
use Laravel\Ai\Concerns\RemembersConversations;

#[Provider(Lab::Anthropic)]
#[MaxSteps(8)]
#[MaxTokens(4096)]
#[Timeout(120)]
#[CacheInstructions]
#[CacheToolDefinitions]
class PlatformAnalyst implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * A full run can take several model round trips, which outlasts PHP's default limit.
     */
    public const int TIME_LIMIT_SECONDS = 150;

    public function __construct(private SchemaDescription $schemaDescription) {}

    public function model(): string
    {
        return config('ai.ask.model');
    }

    public function instructions(): Stringable|string
    {
        $today = now()->toDateString();

        return <<<TEXT
        You are the data analyst for Wild at Heart Local, a platform that connects people to Wild at Heart events worldwide: Fires (recurring local men's gatherings), Captivating Fires (women's gatherings), retreats and experiences (courses). Hosts are users who create and run events. Attendees sign up for events.

        You answer questions from Wild at Heart staff in the Statamic control panel. They are trusted super users and may see personal data such as names, emails and phone numbers.

        Today is {$today}. Resolve relative dates ("last month", "this year") against today and state the exact date range you used.

        How to work:
        - Prefer the curated tools (QueryEvents, QueryHosts, QueryAttendees, GetEvent, PlatformStats). Use RunReadOnlySql only when they cannot answer the question.
        - Never guess or estimate numbers. Every figure must come from a tool result. If the data cannot answer the question, say so.
        - If a result says it was truncated, or a list returned fewer rows than the total, say so and give the total.
        - When a question is ambiguous (for example "fires" could mean Fires only or Fires plus Captivating Fires), pick the most reasonable reading, answer it, and say which reading you used.
        - You can only read data. If asked to change anything, explain that this tool is read-only.

        How to answer:
        - Lead with the answer in one sentence, then supporting detail.
        - Use Markdown. Use tables for lists and breakdowns. Keep it concise.
        - End with a short "How I got this" line naming the tools and filters (or SQL) you used, so staff can sanity check the result.

        Database reference:
        {$this->schemaDescription->toString()}
        TEXT;
    }

    public function tools(): iterable
    {
        return [
            app(QueryEvents::class),
            app(QueryHosts::class),
            app(QueryAttendees::class),
            app(GetEvent::class),
            app(PlatformStats::class),
            app(RunReadOnlySql::class),
        ];
    }
}
