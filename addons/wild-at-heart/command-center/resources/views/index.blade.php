@extends('statamic::layout')

@section('title', 'Command Center')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1>Command Center</h1>
    </div>

    <feature-flags></feature-flags>
@endsection
