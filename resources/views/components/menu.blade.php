<nav class="flex space-x-6 mb-6">
    <a href="{{ route('home') }}" class="p-4 bg-black text-white font-semibold">Home</a>
    <a href="{{ route('host') }}" class="p-4 bg-black text-white font-semibold">Host a 🔥</a>
    <a href="{{ route('attend') }}" class="p-4 bg-black text-white font-semibold">Attend a 🔥</a>
    @if(auth()->check())
        <a class="p-4 bg-black text-white font-semibold">
            My RSVPs
        </a>
        <a class="p-4 bg-black text-white font-semibold">
            My Fires
        </a>
        <form method="POST"
              class="flex"
              action="{{ route('logout') }}">
            @csrf
            <a :href="route('logout')"
               onclick="event.preventDefault();
               this.closest('form').submit();"
               class="p-4 bg-black text-white font-semibold">
                Logout
            </a>
        </form>
    @else
        <a href="{{ url('login/auth0') }}" class="p-4 bg-black text-white font-semibold">Login</a>
    @endif


</nav>
