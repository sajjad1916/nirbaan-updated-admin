<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Schema::defaultStringLength(191);
        //
        Blade::if('showPackage', function () {

            $user = \Auth::user();
            $isParcel = $user->vendor->vendor_type->is_parcel ?? false;

            //
            if ($user && ($user->hasAnyRole('admin') || ($user->hasAnyRole('manager') && $isParcel))) {
                return 1;
            }
            return 0;
        });

        try {
            if (Schema::hasTable('settings')) {
                date_default_timezone_set(setting('timeZone', 'UTC'));
                app()->setLocale(setting('localeCode', 'en'));
            } else {
                date_default_timezone_set('UTC');
                app()->setLocale('en');
            }
        } catch (\Exception $ex) {
            //
            date_default_timezone_set('UTC');
            app()->setLocale('en');
        }
    }
}
