@section('title', __('Order Payment'))
@if($selectedModel->payment_method->slug != 'offline')
    <div class="" wire:init="initPayment">
        <div class="w-11/12 p-12 mx-auto mt-20 border rounded shadow md:w-6/12 lg:w-4/12">
            <x-heroicon-o-clock class="w-12 h-12 mx-auto text-gray-400 md:h-24 md:w-24" />
            <p class="text-xl font-medium text-center">{{ __('Order Payment') }}</p>
            <p class="text-sm text-center">
                {{ __('Please wait while we process your payment') }}</p>
        </div>

        {{-- close --}}
        <p class="w-full p-4 text-sm text-center text-gray-500">
            {{ __('Do not close this window') }}</p>
    </div>

@else
    @include('livewire.payment.offline.order')
@endif


