<?php

namespace Atanunu\XpressWallet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamAcceptInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invitation_id' => 'required|string',
            'code' => 'required|string',
        ];
    }
}
