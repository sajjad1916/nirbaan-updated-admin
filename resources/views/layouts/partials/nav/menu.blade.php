<ul class="mt-6">


    {{-- dashboard --}}
    <x-menu-item title="{{ __('Dashboard') }}" route="dashboard">
        <x-heroicon-o-template class="w-5 h-5" />
    </x-menu-item>

    @role('admin')
        {{-- Vendor Types --}}
        <x-menu-item title="{{ __('Vendor Types') }}" route="vendor.types">
            <x-heroicon-o-color-swatch class="w-5 h-5" />
        </x-menu-item>

    {{-- Vendors --}}
    <x-menu-item title="{{ __('Vendors') }}" route="vendors">
        <x-heroicon-o-shopping-cart class="w-5 h-5" />
    </x-menu-item>
    @endrole
    @role('manager')
        <x-menu-item title="{{ __('Delivery Boys') }}" route="drivers">
            <x-heroicon-o-user-group class="w-5 h-5" />
        </x-menu-item>
    @endhasanyrole

    @role('admin')
        <x-menu-item title="{{ __('Reviews') }}" route="reviews">
            <x-heroicon-o-thumb-up class="w-5 h-5" />
        </x-menu-item>
    @endrole

    <x-menu-item routePath="order/*" title="{{ __('Orders') }}" route="orders">
            <x-heroicon-o-shopping-bag class="w-5 h-5" />
        </x-menu-item>


    {{-- Package --}}
    @showPackage
        <x-group-menu-item routePath="package/*" title="{{ __('Package Delivery') }}"
            icon="heroicon-o-globe">

            @hasanyrole('city-admin|admin')
                <x-menu-item title="{{ __('Package Types') }}" route="package.types">
                    <x-heroicon-o-archive class="w-5 h-5" />
                </x-menu-item>

                <x-menu-item title="{{ __('Countries') }}" route="package.countries">
                    <x-heroicon-o-globe class="w-5 h-5" />
                </x-menu-item>

                <x-menu-item title="{{ __('States') }}" route="package.states">
                    <x-heroicon-o-globe-alt class="w-5 h-5" />
                </x-menu-item>

                <x-menu-item title="{{ __('Cities') }}" route="package.cities">
                    <x-heroicon-o-map class="w-5 h-5" />
                </x-menu-item>
        @endhasanyrole

            {{-- manager package delivery options --}}
            @role('manager')
                <x-menu-item title="{{ __('Pricing') }}" route="package.pricing">
                    <x-heroicon-o-currency-dollar class="w-5 h-5" />
                </x-menu-item>

                <x-menu-item title="{{ __('Cities') }}" route="package.cities.my">
                    <x-heroicon-o-location-marker class="w-5 h-5" />
                </x-menu-item>

                <x-menu-item title="{{ __('States') }}" route="package.states.my">
                    <x-heroicon-o-globe-alt class="w-5 h-5" />
                </x-menu-item>

                <x-menu-item title="{{ __('Countries') }}" route="package.countries.my">
                    <x-heroicon-o-globe class="w-5 h-5" />
                </x-menu-item>

            @endhasanyrole

        </x-group-menu-item>

    @endshowPackage


    @hasanyrole('city-admin|admin')

        <x-group-menu-item routePath="coupons*" title="{{ __('Coupons') }}"
            icon="heroicon-o-receipt-tax">

            <x-menu-item title="{{ __('Coupons') }}" route="coupons">
                <x-heroicon-o-receipt-tax class="w-5 h-5" />
            </x-menu-item>
            <x-menu-item title="{{ __('Coupon Report') }}" route="coupons.report">
                <x-heroicon-o-chart-pie class="w-5 h-5" />
            </x-menu-item>
        </x-group-menu-item>

    @endhasanyrole

    {{-- Users --}}
    @hasanyrole('city-admin|admin')
        <x-menu-item title="{{ __('Users') }}" route="users">
            <x-heroicon-o-user-group class="w-5 h-5" />
        </x-menu-item>
    @endhasanyrole

    @hasanyrole('admin')

        {{-- wallet transactions --}}
        <x-menu-item title="{{ __('Wallet Transactions') }}" route="wallet.transactions">
            <x-heroicon-o-collection class="w-5 h-5" />
        </x-menu-item>

    @endhasanyrole
    @hasanyrole('city-admin|admin')
    {{-- Earings --}}
    <x-group-menu-item routePath="earnings/*" title="{{ __('Earnings') }}" icon="heroicon-o-cash">
        
            <x-menu-item title="{{ __('Vendor Earnings') }}" route="earnings.vendors">
                <x-heroicon-o-shopping-bag class="w-5 h-5" />
            </x-menu-item>
        

        <x-menu-item title="{{ __('Driver Earnings') }}" route="earnings.drivers">
            <x-heroicon-o-truck class="w-5 h-5" />
        </x-menu-item>

        <x-menu-item title="{{ __('Driver Remittance') }}" route="earnings.remittance">
            <x-heroicon-o-calculator class="w-5 h-5" />
        </x-menu-item>

    </x-group-menu-item>
    @endhasanyrole

    @hasanyrole('city-admin|admin')
    {{-- Payouts --}}
    <x-group-menu-item routePath="payouts*" title="{{ __('Payouts') }}"
        icon="heroicon-o-clipboard-check">
        
            <x-menu-item title="{{ __('Vendor Payouts') }}" route="payouts"
                rawRoute="{{ route('payouts', ['type' => 'vendors']) }}">
                <x-heroicon-o-shopping-bag class="w-5 h-5" />
            </x-menu-item>
        
        <x-menu-item title="{{ __('Driver Payouts') }}" route="payouts"
            rawRoute="{{ route('payouts', ['type' => 'drivers']) }}">
            <x-heroicon-o-truck class="w-5 h-5" />
        </x-menu-item>

    </x-group-menu-item>
    @endhasanyrole

    {{-- Payment methods --}}
    @hasanyrole('manager')
        <x-menu-item title="{{ __('Payment Methods') }}" route="payment.methods.my">
            <x-heroicon-o-cash class="w-5 h-5" />
        </x-menu-item>
    @endhasanyrole


    @hasanyrole('admin')
        {{-- notifications --}}
        <x-menu-item title="{{ __('Notifications') }}" route="notification.send">
            <x-heroicon-o-bell class="w-5 h-5" />
        </x-menu-item>
        <x-group-menu-item routePath="operations/*" title="{{ __('Operations') }}"
            icon="heroicon-o-server">

           
            {{-- logs --}}
            <x-menu-item title="{{ __('Logs') }}" route="logs" ex="true">
                <x-heroicon-o-shield-exclamation class="w-5 h-5" />
            </x-menu-item>
            
            {{-- cron job --}}
            <x-menu-item title="{{ __('CRON JOB') }}" route="configure.cron.job">
                <x-heroicon-o-calendar class="w-5 h-5" />
            </x-menu-item>
            {{-- cron job --}}
            <x-menu-item title="{{ __('Auto-Assignments') }}" route="auto.assignments">
                <x-heroicon-o-clipboard-check class="w-5 h-5" />
            </x-menu-item>

        </x-group-menu-item>


        {{-- Settings --}}
        <x-group-menu-item routePath="setting/*" title="{{ __('Settings') }}" icon="heroicon-o-cog">

            {{-- Currencies --}}
            <x-menu-item title="{{ __('Currencies') }}" route="currencies">
                <x-heroicon-o-currency-dollar class="w-5 h-5" />
            </x-menu-item>

            {{-- Payment methods --}}
            <x-menu-item title="{{ __('Payment Methods') }}" route="payment.methods">
                <x-heroicon-o-cash class="w-5 h-5" />
            </x-menu-item>

            {{-- Settings --}}
            <x-menu-item title="{{ __('SMS Gateways') }}" route="sms.settings">
                <x-heroicon-o-inbox class="w-5 h-5" />
            </x-menu-item>

            <hr/>
           

            {{-- Settings --}}
            <x-menu-item title="{{ __('General Settings') }}" route="settings">
                <x-heroicon-o-cog class="w-5 h-5" />
            </x-menu-item>

             {{-- App Settings --}}
             <x-menu-item title="{{ __('Mobile App Settings') }}" route="settings.app">
                <x-heroicon-o-device-mobile class="w-5 h-5" />
            </x-menu-item>

             {{-- Web Settings --}}
             <x-menu-item title="{{ __('Website Settings') }}" route="settings.website">
                <x-heroicon-o-globe-alt class="w-5 h-5" />
            </x-menu-item>
            
            {{-- Mail Settings --}}
            <x-menu-item title="{{ __('Server Settings') }}" route="settings.server">
                <x-heroicon-o-server class="w-5 h-5" />
            </x-menu-item>
        </x-group-menu-item>
    @endhasanyrole

</ul>
