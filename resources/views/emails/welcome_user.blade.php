@component('mail::message')
# Hey {{ $name }}, welcome aboard!

We're thrilled to have you join **{{ config('app.name') }}**.  
Here's what you need to get started:

---

### Your Login Details:

- **Email:** {{ $email }}
@if($password)
- **Password:** {{ $password }}
@endif

> You can change your password anytime after logging in.


If you have any questions, feel free to hit us up anytime.  
We're here to help you make the most out of {{ config('app.name') }}.

Stay awesome,  
**— The {{ config('app.name') }} Team**
@endcomponent
