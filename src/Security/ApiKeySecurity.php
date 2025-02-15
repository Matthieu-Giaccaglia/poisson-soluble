<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;

class ApiKeySecurity
{
    private string $validApiKey;

    public function __construct(string $validApiKey)
    {
        $this->validApiKey = $validApiKey;
    }

    public function isValidApiKey(Request $request): bool
    {
        $apiKey = $request->headers->get('X-API-KEY');
        if ($apiKey !== $this->validApiKey) {
            return false;
        }

        return true;
    }
}
