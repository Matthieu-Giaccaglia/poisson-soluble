<?php

namespace App\Service;


class HelperService
{
    
    public function isValidPhone($phone): bool
    {
        return preg_match('/^0[1-9]\d{8}$/', $phone);
    }

    public function isValidInsee(string $insee): bool
    {
        return preg_match('/^\d{5}$/', $insee);
    }
}