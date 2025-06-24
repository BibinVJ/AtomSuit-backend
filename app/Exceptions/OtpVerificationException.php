<?php

namespace App\Exceptions;

use Exception;

class OtpVerificationException extends Exception
{
    public function __construct(string $message = 'Invalid or expired OTP')
    {
        parent::__construct($message);
    }
}
