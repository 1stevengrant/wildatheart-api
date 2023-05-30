<x-mail::message>
# Introduction

The body of your message.

<x-mail::button :url="route('verify.email', $verificationToken)">
Verify Email
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
