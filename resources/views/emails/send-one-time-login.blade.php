<x-mail::message>
# Login to local.wildatheart.org

Click on the button below to sign in to your account.

This link is only valid once and will expire in 60 minutes.

<x-mail::button :url="route('onetime.login', $emailVerify->token)">
Login
</x-mail::button>

Thanks,<br>
Wild at Heart
</x-mail::message>
