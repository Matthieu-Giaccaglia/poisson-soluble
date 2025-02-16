<?php

namespace App\Service;

use DateTime;
use Psr\Log\LoggerInterface;

class SmsService
{

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function sendSms(string $phoneNumber, string $message): bool
    {
        $this->logger->info('{date} : Send SMS to {phoneNumber} with content {message}.', [
            'date' => (new DateTime('now'))->format('Y-m-d H:i:s'),
            'phoneNumber' => $phoneNumber,
            'message' => $message,
        ]);

        return true;
    }
}
