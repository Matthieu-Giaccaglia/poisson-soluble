<?php

namespace App\Message;

class SmsMessage
{
    private string $phonenumber;
    private string $message;

    public function __construct(string $phonenumber, string $message)
    {
        $this->phonenumber = $phonenumber;
        $this->message = $message;
    }

    public function getPhonenumber()
    {
        return $this->phonenumber;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
