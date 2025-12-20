<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Get a setting value by key with optional default.
     *
     * @param  mixed  $default
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        return Setting::getValue($key, $default);
    }
}

if (! function_exists('set_setting')) {
    /**
     * Set a setting value.
     *
     * @param  mixed  $value
     */
    function set_setting(string $key, $value, ?string $type = null, ?string $group = null): void
    {
        Setting::setValue($key, $value, $type, $group);
    }
}

if (! function_exists('format_currency')) {
    /**
     * Format a number as currency based on settings.
     */
    function format_currency(float $amount, ?string $currency = null): string
    {
        $currency = $currency ?? setting('currency', 'USD');
        $symbol = setting('currency_symbol', '$');
        $position = setting('currency_position', 'before');
        $decimalPlaces = (int) setting('decimal_places', 2);
        $decimalSeparator = setting('decimal_separator', '.');
        $thousandSeparator = setting('thousand_separator', ',');

        $formatted = number_format($amount, $decimalPlaces, $decimalSeparator, $thousandSeparator);

        return $position === 'before'
            ? $symbol.$formatted
            : $formatted.$symbol;
    }
}
