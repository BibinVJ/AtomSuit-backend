<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class CentralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'app_name',
                'value' => config('app.name', 'Atomsuit'),
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application name displayed across the platform',
            ],
            [
                'key' => 'app_tagline',
                'value' => 'Comprehensive ERP Solution',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Short tagline or description of your application',
            ],
            [
                'key' => 'timezone',
                'value' => config('app.timezone', 'UTC'),
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default timezone for the application',
            ],
            [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default date format (PHP date format)',
            ],
            [
                'key' => 'time_format',
                'value' => 'H:i:s',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default time format (PHP date format)',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Enable/disable maintenance mode for the platform',
            ],

            // Appearance Settings
            [
                'key' => 'logo',
                'value' => 'logos/logo.png',
                'type' => 'file',
                'group' => 'appearance',
                'description' => 'Main logo of the application',
            ],
            [
                'key' => 'logo_dark',
                'value' => 'logos/logo.png',
                'type' => 'file',
                'group' => 'appearance',
                'description' => 'Logo for dark mode',
            ],
            [
                'key' => 'favicon',
                'value' => 'logos/icon.png',
                'type' => 'file',
                'group' => 'appearance',
                'description' => 'Favicon for the application',
            ],
            [
                'key' => 'primary_color',
                'value' => '#3b82f6',
                'type' => 'string',
                'group' => 'appearance',
                'description' => 'Primary brand color (hex)',
            ],
            [
                'key' => 'secondary_color',
                'value' => '#6366f1',
                'type' => 'string',
                'group' => 'appearance',
                'description' => 'Secondary brand color (hex)',
            ],

            // Payment/Currency Settings
            [
                'key' => 'currency',
                'value' => 'USD',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Default currency code (ISO 4217)',
            ],
            [
                'key' => 'currency_symbol',
                'value' => '$',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Currency symbol to display',
            ],
            [
                'key' => 'currency_position',
                'value' => 'before',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Currency symbol position (before/after)',
            ],
            [
                'key' => 'decimal_separator',
                'value' => '.',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Decimal separator for currency display',
            ],
            [
                'key' => 'thousand_separator',
                'value' => ',',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Thousand separator for currency display',
            ],
            [
                'key' => 'decimal_places',
                'value' => '2',
                'type' => 'integer',
                'group' => 'payment',
                'description' => 'Number of decimal places for currency',
            ],

            // Email Settings
            [
                'key' => 'mail_from_name',
                'value' => config('app.name', 'Atomsuit'),
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default sender name for emails',
            ],
            [
                'key' => 'mail_from_address',
                'value' => config('mail.from.address', 'noreply@atomsuit.com'),
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default sender email address',
            ],
            [
                'key' => 'support_email',
                'value' => 'support@atomsuit.com',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Support email address displayed to users',
            ],

            // Registration Settings
            [
                'key' => 'registration_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'registration',
                'description' => 'Enable/disable new tenant registrations',
            ],
            [
                'key' => 'email_verification_required',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'registration',
                'description' => 'Require email verification on registration',
            ],
            [
                'key' => 'default_trial_days',
                'value' => '14',
                'type' => 'integer',
                'group' => 'registration',
                'description' => 'Default trial period in days for new tenants',
            ],

            // SEO Settings
            [
                'key' => 'meta_title',
                'value' => config('app.name', 'Atomsuit') . ' - ERP Solution',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Default meta title for pages',
            ],
            [
                'key' => 'meta_description',
                'value' => 'Comprehensive multi-tenant ERP solution for businesses',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Default meta description',
            ],
            [
                'key' => 'meta_keywords',
                'value' => 'ERP, business management, accounting, inventory',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Default meta keywords (comma-separated)',
            ],

            // Social Links
            [
                'key' => 'facebook_url',
                'value' => null,
                'type' => 'string',
                'group' => 'social',
                'description' => 'Facebook profile/page URL',
            ],
            [
                'key' => 'twitter_url',
                'value' => null,
                'type' => 'string',
                'group' => 'social',
                'description' => 'Twitter/X profile URL',
            ],
            [
                'key' => 'linkedin_url',
                'value' => null,
                'type' => 'string',
                'group' => 'social',
                'description' => 'LinkedIn profile/page URL',
            ],
            [
                'key' => 'instagram_url',
                'value' => null,
                'type' => 'string',
                'group' => 'social',
                'description' => 'Instagram profile URL',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
