<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="{{ setting('localeCode', 'en') }}" dir="{{ setting('localeCode') == 'ar' ? 'rtl':'ltr' }}">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="{{ setting('favicon') }}"/>
    <title> {{ setting('websiteName', env("APP_NAME")) }} - @yield('title', "" ) </title>
    @include('layouts.partials.styles')
    @yield('styles')
  </head>
  <body>
    <div
      class="flex h-screen bg-gray-50 dark:bg-gray-900"
      :class="{ 'overflow-hidden': isSideMenuOpen }">

      <!-- Desktop sidebar -->
      @include('layouts.partials.nav.desktop')

      <!-- Mobile sidebar -->
      @include('layouts.partials.nav.mobile')

      <div class="flex flex-col flex-1 w-full">
        
        {{-- header --}}
        @livewire('header.profile')



        <main class="h-full overflow-y-auto">
            <div class="container grid px-6 py-5 mx-auto">
                {{ $slot ?? "" }}
                @yield('content')
            </div>
        </main>
      </div>
    </div>

    <script src="https://unpkg.com/@themesberg/flowbite@1.2.0/dist/datepicker.bundle.js"></script>
    @include('layouts.partials.scripts')
    @stack('scripts')
  </body>
</html>
