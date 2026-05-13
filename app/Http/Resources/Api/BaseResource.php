<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class BaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->sanitize(parent::toArray($request));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function sanitize(array $data): array
    {
        return Arr::except($data, [
            'password',
            'remember_token',
            'email_verified_at',
            'tokens',
            'personal_access_tokens',
        ]);
    }
}
