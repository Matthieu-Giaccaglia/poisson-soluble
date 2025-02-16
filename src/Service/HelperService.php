<?php

namespace App\Service;

class HelperService
{
    /**
     * Valid a french phone number like :
     *  - 0601020304
     *  - +33601020304
     */
    public function isValidPhone(string $phone): bool
    {
        return (bool) preg_match('/^(?:(?:\+)33|0)[1-9]\d{8}$/', $phone);
    }

    /**
     * Valid a city insee code.
     */
    public function isValidInsee(string $insee): bool
    {
        return (bool) preg_match('/^\d{5}$/', $insee);
    }
}
