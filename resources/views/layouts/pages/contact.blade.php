@extends('layouts.auth')
@section('title', __('Contact Us'))
@section('content')
<div class="w-10/12 mx-auto my-10">
    {!! setting('contactInfo', "") !!}
</div>
@endsection
