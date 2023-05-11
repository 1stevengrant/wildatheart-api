<nav class="flex space-x-6 mb-6">
    <a href="{{ route('home') }}" class="p-4 bg-black text-white font-semibold">Home</a>
    <a href="{{ route('create') }}" class="p-4 bg-black text-white font-semibold">Host a 🔥</a>
    <a href="{{ route('attend') }}" class="p-4 bg-black text-white font-semibold">Attend a 🔥</a>
    @if(auth()->check())
        <a href="{{ route('logout') }}" class="p-4 bg-black text-white font-semibold">Logout</a>
    @else
        <a href="{{ route('login') }}" class="p-4 bg-black text-white font-semibold">Login</a>
    @endif

</nav>
