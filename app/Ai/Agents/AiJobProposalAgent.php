<?php

namespace App\Ai\Agents;

use App\Models\AiJobProposal;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class AiJobProposalAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    public ?string $conversationId = null;

    public function setConversationId(?string $conversationId)
    {
        $this->conversationId = $conversationId;
        return $this;
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return app(AiJobProposal::class)->getModelInstructions();
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        // Concept of conversation_id allows sending job data to a specific context
        // This will be expanded later or intercepted by the provider Gateway
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }
}
