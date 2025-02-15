<?php

namespace App\MessageHandler;

use App\Service\SmsService;
use App\Message\SmsMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class SmsHandler
{
    private $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function __invoke(SmsMessage $message)
    {
        $phoneNumber = $message->getPhoneNumber();
        $message = $message->getMessage();

        $this->smsService->sendSms($phoneNumber, $message);
    }
}
