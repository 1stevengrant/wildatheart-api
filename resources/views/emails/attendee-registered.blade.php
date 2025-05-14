<x-mail::message>
{!! Str::markdown($emailContent) !!}
<p style="text-align: center; font-size: 10px">
<a href="{{ $keepActiveLink }}">Keep Fire Active</a> |
<a href="{{ $removeFireLink }}">Remove My Fire</a> |
<a href="{{ $manageFireLink }}">Manage My Fire</a>
</p>
</x-mail::message>
