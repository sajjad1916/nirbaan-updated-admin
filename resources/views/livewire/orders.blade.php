@section('title', 'Orders')
<div>
     


    <x-baseview title="{{ __('Orders') }}" :showNew="false">
        @if( $stopRefresh)
            <div>
        @else 
            <div wire:poll.20000ms="refreshDataTable">
        @endif
                <livewire:tables.order-table />
        </div>
    </x-baseview>

    {{-- details moal --}}

    <div x-data="{ open: @entangle('showDetails') }">
        <x-modal-lg>

            <p class="text-xl font-semibold">{{ __('Order Details') }}</p>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <x-details.item title="{{ __('Code') }}"
                    text="#{{ $selectedModel->code ?? '' }}" />
                <x-details.item title="{{ __('Status') }}"
                    text="{{ $selectedModel->status ?? '' }}" />
                <x-details.item title="{{ __('Payment Status') }}"
                    text="{{ $selectedModel->payment_status ?? '' }}" />
                <x-details.item title="{{ __('Payment Method') }}"
                    text="{{ $selectedModel->payment_method->name ?? '' }}" />
            </div>
            <div class="grid grid-cols-1 gap-4 mt-5 border-t md:grid-cols-2 lg:grid-cols-3">
                <x-details.item
                    title="{{ $selectedModel != null && $selectedModel->is_package ? __('Sender')  : __('User') }}"
                    text="{{ $selectedModel->user->name ?? '' }}" />
                <x-details.item
                    title="{{ $selectedModel != null && $selectedModel->is_package ? __('Sender Phone')  : __('User Phone') }}"
                    text="{{ $selectedModel->user->phone ?? '' }}" />


            </div>
           
            
            <div class="grid grid-cols-1 gap-4 mt-5 border-t md:grid-cols-2 lg:grid-cols-3">

                <x-details.item title="{{ __('Vendor') }}"
                    text="{{ $selectedModel->vendor->name ?? '' }}" />
                <x-details.item title="{{ __('Vendor Address') }}"
                    text="{{ $selectedModel->vendor->address ?? '' }}" />


                <x-details.item title="{{ __('Date of order') }}"
                    text="{{ $selectedModel != null ? $selectedModel->created_at->format('M d, Y \\a\\t H:i a') : '' }}" />
                <x-details.item title="{{ __('Updated At') }}"
                text="{{ $selectedModel != null ? $selectedModel->updated_at->format('M d, Y \\a\\t H:i a') : '' }}" />
            </div>

            {{-- driver info --}}
            <div class="grid grid-cols-1 gap-4 pt-4 mt-4 border-t md:grid-cols-2 lg:grid-cols-3">
                <x-details.item title="{{ __('Driver') }}"
                    text="{{ $selectedModel->driver->name ?? '--' }}" />
                <x-details.item title="{{ __('Driver Phone') }}"
                    text="{{ $selectedModel->driver->phone ?? '--' }}" />
            </div>

            {{-- money --}}
            <div class="pt-4 border-t justify-items-end">
                <div class="flex items-center justify-end space-x-20 border-b">
                    <x-label title="{{ __('Delivery Fee') }}" />
                    <div class="w-6/12 md:w-4/12 lg:w-2/12">
                        <x-details.p
                            text="+{{ setting('currency', '$') }}{{ $selectedModel->delivery_fee ?? '' }}" />
                    </div>
                </div>
               
                <div class="flex items-center justify-end space-x-20 border-b">
                    <x-label title="{{ __('Total') }}" />
                    <div class="w-6/12 md:w-4/12 lg:w-2/12">
                        <x-details.p
                            text="{{ setting('currency', '$') }}{{ $selectedModel->total ?? '' }}" />
                    </div>
                </div>
            </div>

        </x-modal-lg>
    </div>

    {{-- edit modal --}}
    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal confirmText="{{ __('Update') }}" action="update">

            <p class="text-xl font-semibold">{{ __('Edit Order') }}</p>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-details.item title="{{ __('Code') }}"
                    text="#{{ $selectedModel->code ?? '' }}" />
                <x-details.item title="{{ __('Status') }}"
                    text="{{ $selectedModel->status ?? '' }}" />
                <x-details.item title="{{ __('Payment Status') }}"
                    text="{{ $selectedModel->payment_status ?? '' }}" />
                <x-details.item title="{{ __('Payment Method') }}"
                    text="{{ $selectedModel->payment_method->name ?? '' }}" />
                    <x-details.item title="{{ __('Delivery Charge') }}"
                    text="{{ $selectedModel->delivery_fee ?? '' }}" />
            </div>
            <div class="gap-4 mt-5 border-t">
                {{-- delivery boy --}}
                <livewire:component.autocomplete-input 
                    title="{{ __('Delivery Boy') }}" 
                    placeholder="{{ __('Search for driver') }}"
                    column="name"
                    model="User"
                    customQuery="driver"
                    initialEmit="preselectedDeliveryBoyEmit"
                    emitFunction="autocompleteDriverSelected"
                    onclearCalled="clearAutocompleteFieldsEvent"
                />
             
                <x-select title="{{ __('Status') }}" :options="$orderStatus ?? []" name="status" />
                <x-select title="{{ __('Payment Status') }}" :options="$orderPaymentStatus ?? []"
                    name="paymentStatus" />
                    <!-- <x-input title="{{ __('Delivery Charge') }}" name="delivery_fee" /> -->
                    <x-input title="{{ __('Note') }}" name="note"  />

            </div>
        </x-modal>
    </div>


    


   


    {{-- payment review moal --}}
    <div x-data="{ open: @entangle('showAssign') }">
        <x-modal confirmText="{{ __('Approve') }}" action="approvePayment">

            <p class="text-xl font-semibold">{{ __('Order Payment Proof') }}</p>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-details.item title="{{ __('Transaction Code') }}"
                    text="{{ $selectedModel->payment->ref ?? '' }}" />
                <x-details.item title="{{ __('Status') }}"
                    text="{{ $selectedModel->payment->status ?? '' }}" />
                <x-details.item title="{{ __('Payment Method') }}"
                    text="{{ $selectedModel->payment_method->name ?? '' }}" />
                <div>
                    <x-label title="{{ __('Transaction Photo') }}" />
                    <a href="{{ $selectedModel->payment->photo ?? '' }}"
                        target="_blank">
                        <img src="{{ $selectedModel->payment->photo ?? '' }}"
                            class="w-32 h-32" />
                    </a>
                </div>
            </div>
        </x-modal>
    </div>







    {{-- new form --}}
    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal-lg confirmText="{{ __('Next') }}" action="showOrderSummary" :clickAway="false">
            <p class="text-xl font-semibold">{{ __('Create Order') }}</p>
            {{-- lo --}}
            <hr class="my-4" />
            <x-select title="{{ __('Package Types') }}" :options="$packageTypes ?? []"
            name="packageTypeId" />
   
            <hr class="my-4" />

          
            
            {{-- customer infomation --}}

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-input title="{{ __('Customer Name') }}" name="customerName" />
                <x-input title="{{ __('Customer Phone') }}" name="customerPhone" />
            </div>

            <div class="flex items-center">
               
                <div class="flex-grow">
                    <x-input title="{{ __('Customer Address') }}" name="customerAddress" />
                </div>
            </div>
            <hr class="my-4" />
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-input title="{{ __('Product Weight') }}" name="weight" />
                <x-input title="{{ __('Product Price') }}" name="productPrice" />
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-select title="{{ __('Payment Method') }}" :options="$paymentMethods ?? []"
            name="paymentMethodId" />
            <x-select title="{{ __('Product Type') }}" :options="$productClasses ?? []"
            name="productClass" />

            </div>
            <div class="flex items-center">
                <div class="flex-grow">
                    <x-input title="{{ __('Coupon Code') }}" name="couponCode" />
                </div>
                <div class="w-2/12 mt-6 ml-2">
                    <p></p>
                    <x-buttons.primary wireClick="applyDiscount">
                        {{ __('APPLY') }}
                        </x-buttons.plain>
             </div>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                <x-input title="{{ __('Note') }}" name="note" />  
                
                <x-input title="{{ __('Prefered Time') }}" name="preferedTime" />  
                <x-input title="{{ __('Prefered Date') }}" datepicker type="text" name="preferedDate"/>

                  
            </div>
            <hr class="my-4" />
            <x-input title="{{ __('Delivery Fee') }}" name="delivery_fee" />

        </x-modal-lg>
    </div>

    {{--  ORDER PLACEMENT  --}}
    <div x-data="{ open: @entangle('showSummary') }">
        <x-modal-lg confirmText="{{ __('Place Order') }}" action="saveNewOrder" onCancel="$set('showSummary', false)" >
            <p class="text-xl font-semibold">{{ __('Order Summary') }}</p>
            {{--  order summary  --}}
            <div class="grid grid-cols-1 md:grid-cols-2">
                <x-details.item title="{{ __('Customer') }}" text="{{ $newOrder->user->name ?? '' }}" />
                    <x-details.item title="{{ __('Product Class') }}" text="{{ $newOrder->productClass ?? '' }}" />
                        <x-details.item title="{{ __('Product Class') }}" text="{{ $newOrder->preferedDate ?? '' }}" />
                <x-details.item title="{{ __('Note') }}" text="{{ $newOrder->note ?? '' }}" />
                <x-details.item title="{{ __('Payment Method') }}" text="{{ $newOrder->payment_method->name ?? '' }}" />
            </div>
            <hr class="my-4" />
            {{--  vendor details  --}}
            <x-details.item title="{{ __('Vendor') }}"
            text="{{ $newOrder->vendor->name ?? '' }}" />
            

            <hr class="my-4" />
<div class="">

    <div class="flex items-center justify-end space-x-20 border-b">
        <x-label title="{{ __('Subtotal') }}" />
        <div class="w-6/12 md:w-4/12 lg:w-2/12">
            <x-details.p
                text="{{ setting('currency', '$') }}{{ $newOrder->sub_total ?? '' }}" />
        </div>
    </div>
    <div class="flex items-center justify-end space-x-20 border-b">
        <x-label title="{{ __('Discount') }}" />
        <div class="w-6/12 md:w-4/12 lg:w-2/12">
            <x-details.p
                text="- {{ setting('currency', '$') }}{{ $newOrder->discount ?? '' }}" />
        </div>
    </div>
    <div class="flex items-center justify-end space-x-20 border-b">
        <x-label title="{{ __('Tax') }}" />
        <div class="w-6/12 md:w-4/12 lg:w-2/12">
            <x-details.p
                text="+ {{ setting('currency', '$') }}{{ $newOrder->tax ?? '' }}" />
        </div>
    </div>
    
    <div class="flex items-center justify-end space-x-20 border-b">
        <x-label title="{{ __('Driver Tip') }}" />
        <div class="w-6/12 md:w-4/12 lg:w-2/12">
            <x-details.p
            text="+ {{ setting('currency', '$') }}{{ $newOrder->tip ?? '0' }}" />
        </div>
    </div>
    <div class="flex items-center justify-end space-x-20 border-b">
        <x-label title="{{ __('Total') }}" />
        <div class="w-6/12 md:w-4/12 lg:w-2/12">
            <x-details.p
                text="{{ setting('currency', '$') }}{{ $newOrder->total ?? '' }}" />
        </div>
    </div>
        </x-modal-lg>
    </div>

</div>
