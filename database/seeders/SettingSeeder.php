<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Company Information
            [
                'key' => 'company_name',
                'value' => 'Your Company Name',
                'type' => 'string',
                'group' => 'company',
                'description' => 'Company name displayed throughout the application',
            ],
            [
                'key' => 'company_legal_name',
                'value' => 'Your Company Legal Name LLC',
                'type' => 'string',
                'group' => 'company',
                'description' => 'Legal company name for documents and contracts',
            ],
            [
                'key' => 'company_address',
                'value' => '123 Business Street, Suite 100',
                'type' => 'string',
                'group' => 'company',
                'description' => 'Company street address',
            ],
            [
                'key' => 'company_city',
                'value' => 'Business City',
                'type' => 'string',
                'group' => 'company',
                'description' => 'Company city',
            ],
            [
                'key' => 'company_state',
                'value' => 'State',
                'type' => 'string',
                'group' => 'company',
                'description' => 'Company state/province',
            ],
            [
                'key' => 'company_zip',
                'value' => '12345',
                'type' => 'string',
                'group' => 'company',
                'description' => 'Company postal/zip code',
            ],
            [
                'key' => 'company_country',
                'value' => 'United States',
                'type' => 'string',
                'group' => 'company',
                'description' => 'Company country',
            ],
            [
                'key' => 'company_phone',
                'value' => '+1 (555) 123-4567',
                'type' => 'string',
                'group' => 'company',
                'description' => 'Company main phone number',
            ],
            [
                'key' => 'company_email',
                'value' => 'info@yourcompany.com',
                'type' => 'string',
                'group' => 'company',
                'description' => 'Company main email address',
            ],
            [
                'key' => 'company_website',
                'value' => 'https://www.yourcompany.com',
                'type' => 'string',
                'group' => 'company',
                'description' => 'Company website URL',
            ],
            [
                'key' => 'company_tax_id',
                'value' => '12-3456789',
                'type' => 'string',
                'group' => 'company',
                'description' => 'Company tax identification number',
            ],
            [
                'key' => 'company_registration_number',
                'value' => 'REG123456789',
                'type' => 'string',
                'group' => 'company',
                'description' => 'Company registration number',
            ],

            // General Application Settings
            [
                'key' => 'timezone',
                'value' => config('app.timezone', 'UTC'),
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default timezone for the application',
            ],
            [
                'key' => 'language',
                'value' => 'en',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default application language',
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
                'key' => 'datetime_format',
                'value' => 'Y-m-d H:i:s',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default datetime format (PHP date format)',
            ],
            [
                'key' => 'week_start',
                'value' => '1',
                'type' => 'integer',
                'group' => 'general',
                'description' => 'First day of week (0=Sunday, 1=Monday)',
            ],

            // Financial Settings
            [
                'key' => 'currency',
                'value' => 'USD',
                'type' => 'string',
                'group' => 'financial',
                'description' => 'Default currency code (ISO 4217)',
            ],
            [
                'key' => 'currency_symbol',
                'value' => '$',
                'type' => 'string',
                'group' => 'financial',
                'description' => 'Currency symbol to display',
            ],
            [
                'key' => 'currency_position',
                'value' => 'before',
                'type' => 'string',
                'group' => 'financial',
                'description' => 'Currency symbol position (before/after)',
            ],
            [
                'key' => 'decimal_separator',
                'value' => '.',
                'type' => 'string',
                'group' => 'financial',
                'description' => 'Decimal separator for currency display',
            ],
            [
                'key' => 'thousand_separator',
                'value' => ',',
                'type' => 'string',
                'group' => 'financial',
                'description' => 'Thousand separator for currency display',
            ],
            [
                'key' => 'decimal_places',
                'value' => '2',
                'type' => 'integer',
                'group' => 'financial',
                'description' => 'Number of decimal places for currency',
            ],
            [
                'key' => 'fiscal_year_start',
                'value' => '01-01',
                'type' => 'string',
                'group' => 'financial',
                'description' => 'Fiscal year start date (MM-DD format)',
            ],

            // Invoice Settings
            [
                'key' => 'invoice_prefix',
                'value' => 'INV-',
                'type' => 'string',
                'group' => 'invoicing',
                'description' => 'Invoice number prefix',
            ],
            [
                'key' => 'invoice_number_format',
                'value' => '{prefix}{year}{month}{number:4}',
                'type' => 'string',
                'group' => 'invoicing',
                'description' => 'Invoice number format template',
            ],
            [
                'key' => 'invoice_due_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'invoicing',
                'description' => 'Default invoice due days',
            ],
            [
                'key' => 'invoice_terms',
                'value' => 'Payment is due within 30 days of invoice date.',
                'type' => 'text',
                'group' => 'invoicing',
                'description' => 'Default invoice terms and conditions',
            ],
            [
                'key' => 'invoice_footer',
                'value' => 'Thank you for your business!',
                'type' => 'text',
                'group' => 'invoicing',
                'description' => 'Default invoice footer text',
            ],

            // Appearance Settings
            [
                'key' => 'theme',
                'value' => 'light',
                'type' => 'string',
                'group' => 'appearance',
                'description' => 'Default theme (light/dark)',
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
            [
                'key' => 'success_color',
                'value' => '#10b981',
                'type' => 'string',
                'group' => 'appearance',
                'description' => 'Success semantic color (hex)',
            ],
            [
                'key' => 'error_color',
                'value' => '#ef4444',
                'type' => 'string',
                'group' => 'appearance',
                'description' => 'Error/Danger semantic color (hex)',
            ],
            [
                'key' => 'warning_color',
                'value' => '#f59e0b',
                'type' => 'string',
                'group' => 'appearance',
                'description' => 'Warning semantic color (hex)',
            ],
            [
                'key' => 'info_color',
                'value' => '#3b82f6',
                'type' => 'string',
                'group' => 'appearance',
                'description' => 'Information semantic color (hex)',
            ],

            // Notification Settings
            [
                'key' => 'notifications_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Enable system notifications',
            ],
            [
                'key' => 'email_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Enable email notifications',
            ],
            [
                'key' => 'sms_notifications',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Enable SMS notifications',
            ],

            // Business Settings
            [
                'key' => 'business_hours_start',
                'value' => '09:00',
                'type' => 'string',
                'group' => 'business',
                'description' => 'Business hours start time (HH:MM format)',
            ],
            [
                'key' => 'business_hours_end',
                'value' => '17:00',
                'type' => 'string',
                'group' => 'business',
                'description' => 'Business hours end time (HH:MM format)',
            ],
            [
                'key' => 'business_days',
                'value' => json_encode([1, 2, 3, 4, 5]),
                'type' => 'json',
                'group' => 'business',
                'description' => 'Business days (1=Monday, 7=Sunday)',
            ],

            // Social Media Links
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
            [
                'key' => 'youtube_url',
                'value' => null,
                'type' => 'string',
                'group' => 'social',
                'description' => 'YouTube channel URL',
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
