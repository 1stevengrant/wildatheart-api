<x-mail::message>
Great! One last step to complete your registration.

Click on this link and let's light your Fire.

<x-mail::button :url="route('verify.email', $verificationToken)">
Verify Email
</x-mail::button>

If you did not create an account or do not recognize this email, please disregard it, and no further action is required.
</x-mail::message>
