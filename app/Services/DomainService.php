<?php

namespace App\Services;

use App\Models\Tenant;
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

        if (! str_ends_with($domain, $baseUrl)) {
            $domain .= '.'.$baseUrl;
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

    /**
     * Add a custom domain to a tenant.
     * Custom domains are stored as-is (e.g. app.clientname.com).
     */
    public function addCustomDomain(Tenant $tenant, string $domain): Domain
    {
        $domain = strtolower(self::normalize($domain));

        // Check against central domains
        $centralDomains = array_map(
            [self::class, 'normalize'],
            config('tenancy.central_domains')
        );

        if (in_array($domain, $centralDomains, true)) {
            throw ValidationException::withMessages([
                'domain' => "The domain '{$domain}' is reserved.",
            ]);
        }

        // Check if domain already exists
        if (Domain::where('domain', $domain)->exists()) {
            throw ValidationException::withMessages([
                'domain' => "The domain '{$domain}' is already in use.",
            ]);
        }

        return $tenant->domains()->create(['domain' => $domain]);
    }

    /**
     * Remove a custom domain from a tenant.
     */
    public function removeCustomDomain(Tenant $tenant, string $domain): void
    {
        $domainRecord = $tenant->domains()->where('domain', $domain)->first();

        if (! $domainRecord) {
            throw ValidationException::withMessages([
                'domain' => "The domain '{$domain}' does not belong to this tenant.",
            ]);
        }

        // Don't allow removing the primary subdomain
        $primaryDomain = $this->buildFullDomain($tenant->domain_name ?? $tenant->name);
        if ($domainRecord->domain === $primaryDomain) {
            throw ValidationException::withMessages([
                'domain' => 'Cannot remove the primary subdomain.',
            ]);
        }

        $domainRecord->delete();
    }
}
