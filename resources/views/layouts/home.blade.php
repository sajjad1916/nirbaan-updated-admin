<!DOCTYPE html>
<html lang="{{ setting('localeCode', 'en') }}" dir="{{ setting('localeCode') == 'ar' ? 'rtl':'ltr' }}">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="{{ setting('favicon') }}" />
    <title>@yield('title', "" ) - {{ setting('websiteName', env('APP_NAME')) }}</title>
    <link rel="stylesheet" href="{{asset('css/LineIcons.css')}}">
    <link rel="stylesheet" href="{{asset('css/slick.css')}}">
    <link rel="stylesheet" href="{{asset('css/magnific-popup.css')}}">
    <link rel="stylesheet" href="{{asset('css/main.css')}}">
    <script src="https://cdn.tailwindcss.com"></script>
    
{{--     
    @include('layouts.partials.styles')
    @yield('styles') --}}
</head>

<body>
    {{ $slot ?? '' }}
    @yield('content')

    {{-- footer --}}
    {{-- @include('layouts.partials.scripts')
    @stack('scripts')
  --}}
   <!--====== jquery js ======-->
  <script src="{{asset('js/vendor/modernizr-3.6.0.min.js')}}"></script>
  <script src="{{asset('js/vendor/jquery-1.12.4.min.js')}}"></script>

   <!--====== Scrolling Nav js ======-->
   <script src="{{asset('js/vendor/jquery.easing.min.js')}}"></script>
   <script src="{{asset('js/vendor/scrolling-nav.js')}}"></script>


    <!--====== Slick js ======-->
    <script src="{{asset('js/vendor/scrolling-nav.js')}}"></script>


    <!--====== Main js ======-->
    <script src="{{asset('js/vendor/main.js')}}"></script>
</body>



</html>
