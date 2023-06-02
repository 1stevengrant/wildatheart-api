<x-mail::message>
Dear {{ $user->given_name }},

The lives of many rest in the courage of a few. 

You’re one of the few.

Well done!

Way to risk.

Way to love. 

We need more men like you in this hour. 

Make sure you confirm your email. We want to fuel your fire with a gift from our team. We are honored to stand alongside you in this mission for the hearts of men around the world. 

Please click on the following link to verify your email

<x-mail::button :url="route('verify.email', $verificationToken)">
Verify Email
</x-mail::button>

Let’s set the world on Fire. 

For the Kingdom,
John and Morgan

If you did not create an account or do not recognize this email, please disregard it, and no further action is required.

</x-mail::message>
