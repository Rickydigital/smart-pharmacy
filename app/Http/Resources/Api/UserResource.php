<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class UserResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        if (method_exists($this->resource, 'displayName')) {
            $data['display_name'] = $this->resource->displayName();
        }

        if (method_exists($this->resource, 'getAllPermissions')) {
            $data['roles'] = $this->resource->roles?->pluck('name')->values() ?? [];
            $data['permissions'] = $this->resource->getAllPermissions()->pluck('name')->values();
        }

        return $data;
    }
}
