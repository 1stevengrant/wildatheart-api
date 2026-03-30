<x-mail::message>
# Verify Your Email

You requested to connect with a host on Wild at Heart Local.

Click the button below to verify your email and continue:

<x-mail::button :url="$verificationUrl">
Continue Connecting
</x-mail::button>

This link will expire in 1 hour.

If you did not request this, you can safely ignore this email.

Thanks,<br>
{{ config('mail.from.name') }}
</x-mail::message>
