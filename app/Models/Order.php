<?php

namespace App\Models;

use App\Traits\FirebaseMessagingTrait;
use App\Traits\FirebaseDBTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\ModelStatus\HasStatuses;
use Illuminate\Support\Facades\Schema;

class Order extends BaseModel
{

    use FirebaseMessagingTrait, FirebaseDBTrait;
    use HasStatuses;


    protected $fillable = ["note", "reason", "total", "driver_id", "delivery_fee"];
    protected $with = ["user", 'statuses'];
    protected $appends = ["payment_link", 'formatted_date', 'type', 'formatted_type', 'can_rate', 'can_rate_driver', 'status', 'photo'];


    public function scopeFullData($query)
    {
        return $query->with([  "user", "driver", "payment_method", "vendor" => function ($query) {
            return $query->withTrashed();
        }, 'package_type']);
    }

    public function scopeMine($query)
    {
        return $query->when(Auth::user()->hasRole('manager'), function ($query) {
            return $query->where('vendor_id', Auth::user()->vendor_id);
        })->when(Auth::user()->hasRole('city-admin'), function ($query) {
            return $query->whereHas('vendor', function ($query) {
                return $query->where('creator_id', Auth::id());
            });
        });
    }



    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo('App\Models\User', 'driver_id', 'id');
    }


    public function payment_method()
    {
        return $this->belongsTo('App\Models\PaymentMethod', 'payment_method_id', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor', 'vendor_id', 'id');
    }

    public function payment()
    {
        return $this->belongsTo('App\Models\Payment', 'id', 'order_id');
    }


    //
    public function package_type()
    {
        return $this->belongsTo('App\Models\PackageType', 'package_type_id', 'id');
    }

    public function auto_assignment()
    {
        return $this->hasOne('App\Models\AutoAssignment', 'order_id', 'id')->where('status', "pending");
    }


    public function getTypeAttribute()
    {
        return $this->vendor->vendor_type->slug ?? '';
    }




    //
    public function getCanRateAttribute()
    {

        if (empty(Auth::user())) {
            return false;
        }
        //
        $vendorReview = Review::where('user_id', Auth::id())->where('order_id', $this->id)->first();
        return empty($vendorReview);
    }

    public function getCanRateDriverAttribute()
    {

        if (empty(Auth::user())) {
            return false;
        }
        //
        $driverReview = Review::where('user_id', Auth::id())->where('driver_id', $this->driver_id)->first();
        return empty($driverReview);
    }

    public function getPaymentLinkAttribute()
    {

        if ($this->payment_status == "pending") {
            return route('order.payment', ["code" => $this->code]);
        } else {
            return "";
        }
    }

    //TODO
    public function getFormattedTypeAttribute()
    {
        return Str::ucfirst($this->vendor->vendor_type->name ?? '');
    }
    public function getIsPackageAttribute()
    {
        return ($this->vendor->vendor_type->slug ?? '') == "package";
    }

    public function getIsFoodAttribute()
    {
        return in_array(($this->vendor->vendor_type->slug ?? ''), ["food", "grocery", "pharmacy"]);
    }



    //updating wallet balance is order failed and was paid via wallet
    public function refundUser()
    {
        //'pending','preparing','ready','enroute','delivered','failed','cancelled'
        if (in_array($this->status, ['failed', 'cancelled']) && in_array($this->payment_status, ['successful'])  && $this->payment_method->slug != "cash") {

            //update user wallet
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $this->user_id],
                ['balance' => 0]
            );

            //
            $wallet->balance += $this->total;
            $wallet->save();

            //save wallet transactions
            $walletTransaction = new WalletTransaction();
            $walletTransaction->wallet_id = $wallet->id;
            $walletTransaction->amount = $this->total;
            $walletTransaction->reason = "Refund";
            $walletTransaction->status = "successful";
            $walletTransaction->is_credit = 1;
            $walletTransaction->save();
        }
    }
}
