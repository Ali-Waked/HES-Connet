<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Contracts\Agent;
use App\Ai\Tools\GetDonationsTool;
use App\Ai\Tools\GetFacilitiesTool;
use App\Ai\Tools\GetReportsTool;
use App\Ai\Tools\GetUsersTool;

class HESAssistant implements Agent
{
    public function instructions(): string
    {
        return 'You are an AI assistant for the HES (Health Ecosystem System) platform. '
            .'You help administrators and staff members with queries about the system. '
            .'You can retrieve information about users, facilities, donations, and reports. '
            .'Always provide concise, data-driven answers based on the tool results. '
            .'If you cannot find the information requested, be honest and suggest alternatives. '
            .'Format your responses in a clear, structured way using bullet points or numbered lists when appropriate.';
    }

    public function tools(): array
    {
        return [
            new GetUsersTool,
            new GetFacilitiesTool,
            new GetDonationsToolDummy,
            new GetReportsTool,
        ];
    }

    public function toToolArray(): array
    {
        return array_map(fn ($tool) => $tool->toArray(), $this->tools());
    }
}

class GetDonationsToolDummy extends GetDonationsTool {}
