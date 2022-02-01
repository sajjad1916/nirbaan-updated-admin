<div class="px-4">
    <div class="flex items-center w-full mb-2 text-2xl font-semibold">
        {{ $title ?? 'List' }}
        @if ($showNew ?? false)
        <div class="mx-auto"></div>
            <x-buttons.new title="{{ $actionTitle ?? ''  }}" />
        @endif
    </div>
    {{-- list --}}
    {{ $slot }}


</div>
{{-- loading --}}
<div wire:loading class="fixed top-0 bottom-0 left-0 z-50 w-full h-full ">
    <div class="fixed top-0 bottom-0 left-0 w-full h-full"></div>
    <div class="fixed top-0 bottom-0 left-0 flex items-center justify-center w-full h-full">
        {{-- <img src="{{ asset('images/loading.svg') }}" class="" /> --}}
    </div>
</div>
