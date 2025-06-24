@component('mail::message')
# Your OTP

Here is your OTP for {{ ucfirst(str_replace('_', ' ', $purpose->value)) }}:

## {{ $otp }}

This OTP will expire in 10 minutes.

Thanks,  
{{ config('app.name') }}
@endcomponent
