<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use Stancl\Tenancy\Database\Models\Domain;

class DomainService
{
    public static function normalize(string $url): string
    {
        $url = preg_replace('#^https?://#', '', $url);
        $url = preg_replace('#^www\.#', '', $url);
        return rtrim($url, '/');
    }

    public function buildFullDomain(string $slug): string
    {
        $baseUrl = self::normalize(config('app.url'));
        $domain = strtolower(str_replace(' ', '-', $slug));

        if (!str_ends_with($domain, $baseUrl)) {
            $domain .= '.' . $baseUrl;
        }

        return $domain;
    }

    public function checkDomainAvailability(string $slug): void
    {
        $fullDomain = $this->buildFullDomain($slug);

        $exists = Domain::where('domain', $fullDomain)->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'domain_name' => "The domain '{$fullDomain}' is already taken.",
            ]);
        }
    }
}
