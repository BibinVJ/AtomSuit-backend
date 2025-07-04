@component('mail::message')
# Your OTP

Here is your OTP for {{ $purpose->label() }}:

## {{ $otp }}

This OTP will expire in 10 minutes.

Thanks,  
{{ config('app.name') }}
@endcomponent
