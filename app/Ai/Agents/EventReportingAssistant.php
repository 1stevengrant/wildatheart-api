<?php

namespace App\Ai\Agents;

use Stringable;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use App\Ai\Tools\CountEvents;
use Laravel\Ai\Contracts\Tool;
use App\Ai\Tools\GetEventTypes;
use Laravel\Ai\Contracts\Agent;
use App\Ai\Tools\CountAttendees;
use Laravel\Ai\Contracts\HasTools;
use App\Ai\Tools\GetEventBreakdown;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Concerns\RemembersConversations;

#[UseCheapestModel]
class EventReportingAssistant implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function provider(): Lab
    {
        return Lab::Anthropic;
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $today = now()->toDateString();

        return <<<INSTRUCTIONS
        You are an event reporting assistant for Wild at Heart, a ministry that connects community members to events worldwide.

        Today's date is {$today}. Use this for all relative date calculations (e.g. "this year", "last quarter", "last 2 weeks").

        You help admins answer questions about events and attendance. You have access to tools that can query the database.

        Event types are organized into three groups:
        - Fires (ongoing gatherings): "Fires" and "Captivating Fires"
        - Retreats (multi-day events): "Wild at Heart Retreat", "4-Day Captivating Retreat", "1-Day Captivating Retreat", "Becoming A King Retreat"
        - Experiences (shorter events): "Wild at Heart Experience", "Captivating Experience", "Becoming A King Experience"
        - There is also "Survival School" which is its own category.

        Events can be filtered by:
        - Event type (specific type or group: fire, retreat, experience)
        - Date range (start_date field)
        - Status: published (approved) or unpublished
        - Sex: men or women
        - Country or city

        When answering questions:
        - Use the tools to get accurate data. Do not guess or estimate.
        - Format numbers with commas for readability.
        - When asked about "fires", include both Fires and Captivating Fires unless specified otherwise.
        - When dates are relative (e.g. "this year", "last quarter"), calculate the actual date range based on today's date above.
        - Be concise and direct with your answers.
        - If asked for a breakdown, use the GetEventBreakdown tool.
        INSTRUCTIONS;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new GetEventTypes,
            new CountEvents,
            new CountAttendees,
            new GetEventBreakdown,
        ];
    }
}
