<?php

namespace App\Models;


class AiJobProposal extends BaseModel
{

    protected $fillable = [
        'name',
        'description',
    ];


    public function getPromptText()
    {
        return "Please write a proposal for the following UpWork Job Post. Use placeholders and adapt to the context of our ongoing conversation if applicable.\n\n";
    }

    public function getModelInstructions()
    {
        return 'You are an expert freelance job proposal writer. Your goal is to write a highly converting, winning proposal for an UpWork job posting. Use placeholders and relevant information about the freelancer context that has been provided to you. Make the proposal concise and professional.';
    }
}
