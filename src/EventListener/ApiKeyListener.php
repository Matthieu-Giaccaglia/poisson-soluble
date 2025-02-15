<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use App\Security\ApiKeySecurity;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;

#[AsEventListener]
class ApiKeyListener
{
    private $apiKeySecurity;

    public function __construct(ApiKeySecurity $apiKeySecurity)
    {
        $this->apiKeySecurity = $apiKeySecurity;
    }

    public function __invoke(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->apiKeySecurity->isValidApiKey($request)) {
            $response = new JsonResponse([
                'error' => 'Unauthorized',
                'message' => 'Invalid API Key'
            ], JsonResponse::HTTP_UNAUTHORIZED);

            $event->setResponse($response);
            return;
        }
    }
}
