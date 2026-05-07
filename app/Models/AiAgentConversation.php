<?php

namespace App\Models;


class AiAgentConversation extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function messages()
    {
        return $this->hasMany(AiAgentConversationMessage::class, 'conversation_id');
    }
}
