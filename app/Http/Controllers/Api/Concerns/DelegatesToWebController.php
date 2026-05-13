<?php

namespace App\Http\Controllers\Api\Concerns;

trait DelegatesToWebController
{
    protected function fromWeb(string $method): mixed
    {
        return $this->callWeb($this->webController, $method);
    }
}
