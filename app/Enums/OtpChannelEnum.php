<?php

namespace App\Enums;

enum OtpChannelEnum: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
}
