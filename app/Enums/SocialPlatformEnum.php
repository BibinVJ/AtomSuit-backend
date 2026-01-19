<?php

namespace App\Enums;

enum SocialPlatformEnum: string
{
    case FACEBOOK = 'facebook';
    case LINKEDIN = 'linkedin';
    case X = 'x';
    case INSTAGRAM = 'instagram';
    case WEBSITE = 'website';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
