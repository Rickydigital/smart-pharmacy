<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class SaleResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        if (method_exists($this->resource, 'displayCustomer')) {
            $data['display_customer'] = $this->resource->displayCustomer();
        }

        return $data;
    }
}
