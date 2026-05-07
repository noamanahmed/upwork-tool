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

class AiJobProposalAgent implements Agent
{
    use Promptable;

    public ?string $conversationId = null;
    public ?string $instructionsText = null;

    public function setConversationId(?string $conversationId)
    {
        $this->conversationId = $conversationId;
        return $this;
    }

    public function setInstructions(?string $instructionsText)
    {
        $this->instructionsText = $instructionsText;
        return $this;
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return $this->instructionsText ?? app(AiJobProposal::class)->getModelInstructions();
    }

}
