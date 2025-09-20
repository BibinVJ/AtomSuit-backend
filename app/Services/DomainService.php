<?php

namespace App\Services;


class DomainService
{
    public static function normalize(string $url): string
    {
        $url = preg_replace('#^https?://#', '', $url);
        $url = preg_replace('#^www\.#', '', $url);
        $url = rtrim($url, '/');

        return $url;
    }
}
