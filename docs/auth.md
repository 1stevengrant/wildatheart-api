# Auth
The auth system deviates from standard Laravel auth in that we're using Auth0 for authentication.

OTP is handled on the Auth0 side, and then we have a custom controller `App\Http\Controllers\Auth\CallbackController` that handles the callback from Auth0 and then creates a user in our database if they don't already exist.

