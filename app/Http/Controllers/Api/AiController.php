<?php

namespace App\Http\Controllers\Api;

class AiController extends ApiController
{
    public function index(): mixed
    {
        return $this->success([
            'enabled' => false,
            'status' => 'placeholder',
            'message' => 'AI assistant API is ready for future backend integration.',
        ]);
    }

    public function conversations(): mixed
    {
        return $this->success([
            'conversations' => [],
            'status' => 'placeholder',
        ]);
    }
}
