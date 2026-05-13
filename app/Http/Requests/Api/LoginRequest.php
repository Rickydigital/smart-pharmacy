<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Auth\LoginRequest as WebLoginRequest;

class LoginRequest extends WebLoginRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'device_name' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:50'],
            'revoke_existing_tokens' => ['nullable', 'boolean'],
        ]);
    }
}
