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
        $baseUrl = self::normalize(config('tenancy.base_domain'));
        $domain = strtolower(str_replace(' ', '-', $slug));

        if (!str_ends_with($domain, $baseUrl)) {
            $domain .= '.' . $baseUrl;
        }

        return $domain;
    }

    public function checkDomainAvailability(string $slug): void
    {
        $fullDomain = $this->buildFullDomain($slug);

        $centralDomains = array_map(
            [self::class, 'normalize'],
            config('tenancy.central_domains')
        );

        $exists = Domain::where('domain', $fullDomain)->exists();

        if ($exists || in_array($fullDomain, $centralDomains, true)) {
            throw ValidationException::withMessages([
                'domain_name' => "The domain '{$fullDomain}' is already taken.",
            ]);
        }
    }
}
