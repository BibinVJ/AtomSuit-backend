<?php

namespace App\Helpers;

class UrlHelper
{
    public static function normalizeWebsiteUrl(string $url): string
    {
        $url = preg_replace('#^https?://#', '', $url);
        $url = preg_replace('#^www\.#', '', $url);

        return 'https://www.'.$url;
    }
}
