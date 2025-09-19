<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TenantSelector
{
    /**
     * Resolve tenant IDs based on IDs or emails.
     * Validates that all provided IDs or emails exist.
     *
     * @param string|array|null $ids
     * @param string|array|null $emails
     * @return array<int>
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function resolve(null|string|array $ids, null|string|array $emails): array
    {
        /** @var Collection $tenants */
        $tenants = Tenant::query();

        if ($ids) {
            $idsArray = is_array($ids) ? $ids : array_map('trim', explode(',', $ids));
            $tenants->whereIn('id', $idsArray);

            $foundIds = $tenants->pluck('id')->toArray();
            $missingIds = array_diff($idsArray, $foundIds);

            if (!empty($missingIds)) {
                throw ValidationException::withMessages([
                    'ids' => ['Tenant(s) not found for IDs: ' . implode(',', $missingIds)]
                ]);
            }

            return $foundIds;
        }

        if ($emails) {
            $emailsArray = is_array($emails) ? $emails : array_map('trim', explode(',', $emails));
            $tenants->whereIn('email', $emailsArray);

            $foundIds = $tenants->pluck('id')->toArray();
            $missingEmails = array_diff($emailsArray, Tenant::whereIn('id', $foundIds)->pluck('email')->toArray());

            if (!empty($missingEmails)) {
                throw ValidationException::withMessages([
                    'emails' => ['Tenant(s) not found for emails: ' . implode(',', $missingEmails)]
                ]);
            }

            return $foundIds;
        }

        // If no filters provided, return all tenant IDs
        return Tenant::pluck('id')->toArray();
    }
}
