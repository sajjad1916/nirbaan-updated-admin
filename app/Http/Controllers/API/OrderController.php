<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\User;
use App\Models\Order;
use App\Models\DeliveryZone;
use App\Models\PaymentMethod;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{

    //
    public function index(Request $request)
    {

        //
        $driverId = $request->driver_id;
        $vendorId = $request->vendor_id;
        $status = $request->status;
        $type = $request->type;
        $vendorTypeId = $request->vendor_type_id;


        $orders = Order::fullData()
            ->when(!empty($vendorId), function ($query) use ($vendorId) {
                return $query->orWhere('vendor_id', $vendorId);
            })
            ->when(!empty($driverId), function ($query) use ($driverId) {
                return $query->orWhere('driver_id', $driverId);
            })
            ->when(empty($vendorId) && empty($driverId), function ($query) {
                return $query->where('user_id', Auth::id());
            })
            ->when(!empty($status), function ($query) use ($status) {
                // return $query->where('status', $status);
                return $query->currentStatus($status);
            })
            ->when($type == "history", function ($query) {
                // return $query->whereIn('status', ['failed', 'cancelled', 'delivered']);
                return $query->currentStatus(['failed', 'cancelled', 'delivered']);
            })
            ->when($type == "assigned", function ($query) {
                // return $query->whereNotIn('status', ['failed', 'cancelled', 'delivered']);
                return $query->otherCurrentStatus(['failed', 'cancelled', 'delivered']);
            })
            ->when($vendorTypeId, function ($query) use ($vendorTypeId) {
                return $query->whereHas("vendor", function ($query) use ($vendorTypeId) {
                    return $query->where('vendor_type_id', $vendorTypeId);
                });
            })
            ->orderBy('created_at', 'DESC')->paginate();
        return $orders;
    }

    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'package_type_id' => 'required|exists:package_types,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'preferedTime' => 'sometimes|nullable|string',
            'preferedDate' => 'sometimes|nullable|string',
            'customerName' => 'sometimes|nullable|string',
            'customerPhone' => 'sometimes|nullable|string',
            'customerAddress' => 'sometimes|nullable|string',
            'weight' => 'sometimes|nullable|numeric',
            'productPrice' => 'sometimes|nullable|numeric',
            'merchantAddress' => 'sometimes|nullable|string',
            'productClass'=>"sometimes|nullable|string",
            'delivery_fee' => 'required|numeric',
            'total' => 'required|numeric',
        ]);

        if ($validator->fails()) {

            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }


        //saving to database
        try {

            DB::beginTransaction();
            $order = new order();
          //DON'T TRANSLATE
          $order->vendor_id = 1;
          $order->payment_method_id = $request->payment_method_id;
          $order->note = $request->note ?? '';
          //
          $order->package_type_id = $request->package_type_id;
          $order->pickup_date = $request->pickup_date ?? "";
          $order->pickup_time = $request->pickup_time ?? "";
          // TODO take extra infos
          $order->weight = $request->weight ?? 0;
          $order->productPrice = $request->productPrice ?? 0;
          $order->merchantAddress = $request->merchantAddress ?? "None";
          $order->customerName = $request->customerName ?? "None";
          $order->preferedTime = $request->preferedTime ?? "None";
          $order->preferedDate = $request->preferedDate ?? "None";
          $order->customerPhone = $request->customerPhone ?? "None";
          $order->customerAddress = $request->customerAddress ?? "None";
          $order->productClass = $request->productClass ?? "None";
          $order->deliveryHub  = $request->deliveryHub ?? "None";
          $order->tax = $request->tax ?? 0;
          $order->delivery_fee = $request->delivery_fee;
          $order->total = $request->total;
          $order->save();
          $order->setStatus($this->getNewOrderStatus($request));



            //
            $paymentMethod = PaymentMethod::find($request->payment_method_id);
            $paymentLink = "";
            $message = "";

            if ($paymentMethod->is_cash) {

                //wallet check 
                if ($paymentMethod->slug == "wallet") {
                    //
                    $wallet = Wallet::mine()->first();
                    if (empty($wallet) || $wallet->balance < $request->total) {
                        throw new \Exception(__("Wallet Balance is less than order total amount"), 1);
                    } else {
                        //
                        $wallet->balance -= $request->total;
                        $wallet->save();

                        //RECORD WALLET TRANSACTION
                        $this->recordWalletDebit($wallet, $request->total);
                    }
                }

                $order->payment_status = "pending";
                $message = __("Order placed successfully. Relax while the vendor process your order");
            } else {
                $message = __("Order placed successfully. Please follow the link to complete payment.");
                // $paymentLink = route('order.payment', ["code" => $order->code]);
            }

            //
            $order->save();

            //
            DB::commit();

            return response()->json([
                "message" => $message,
                // "link" => $paymentLink,
            ], 200);
        } catch (\Exception $ex) {
            \Log::info([
                "Error" => $ex->getMessage(),
                "Line" => $ex->getLine(),
            ]);
            DB::rollback();
            return response()->json([
                "message" => $ex->getMessage()
            ], 400);
        }
    }

    public function OrderCode(Request $request, $code){
        
        return Order::fulldata()->where('code',$code)->first();

    }

    public function show(Request $request, $id)
    {
        //
        return Order::fullData()->where('id', $id)->first();
        $user = User::find(Auth::id());
        if (!$user->hasAnyRole('client')) {
            return Order::fullData()->where('id', $id)->first();
        } else {
            return Order::fullData()->where('user_id', Auth::id())->where('id', $id)->first();
        }
    }

    public function deliveryZone(Request $request){
        return DeliveryZone::all();
    }

    public function OrderStatuses(Request $request, $status){
        $total = array();
        
        $user = User::find(Auth::id());
       if($user->hasAnyRole('client')){
             $result = Order::fullData()
                ->when(!empty($user), function ($query) {
                 return $query->where('user_id', Auth::id());
             })   
                 ->orderBy('created_at', 'DESC')->paginate();
             
                foreach($result as $r){
                    if($r->status === $status){
                      $data = json_decode($r,true);
                      $total[]=$data;
                     
                          
                    }     
                }

                return $total;
                
         
      }else{
             return null;
         }
     }

     public function OrderStatus(Request $request, $status){
        $count = 0;
        $user = User::find(Auth::id());
        if($user->hasAnyRole('client')){
            $result = Order::get()->where('user_id',Auth::id());
            
            foreach($result as $r){
                if($r->status === $status){
                  $data = json_decode($r,true);
                  $count ++;    
                }     
            }

            return $count;

        }else{
            return null;
        }
    }
    //
    public function update(Request $request, $id)
    {
        //
        $user = User::find(Auth::id());
        $driver = User::find($request->driver_id);
        $order = Order::find($id);
        $enableDriverWallet = (bool) setting('enableDriverWallet', "0");
        $driverWalletRequired = (bool) setting('driverWalletRequired', "0");

        if ($user->hasAnyRole('client') && $user->id != $order->user_id && !in_array($request->status, ['pending', 'cancelled'])) {
            return response()->json([
                "message" => "Order doesn't belong to you"
            ], 400);
        }
        //wallet system
        else if ($request->status == "shipment" && !empty($request->driver_id) && $enableDriverWallet) {

            //
            $driverWallet = $driver->wallet;
            if (empty($driverWallet)) {
                $driverWallet = $driver->updateWallet(0);
            }

            //allow if wallet has enough balance
            if ($driverWalletRequired) {
                if ($order->total > $driverWallet->balance) {
                    return response()->json([
                        "message" => __("Order not assigned. Insufficient wallet balance")
                    ], 400);
                }
            } else if ($order->payment_method->slug == "cash" && $order->total > $driverWallet->balance) {
                return response()->json([
                    "message" => __("Insufficient wallet balance, Wallet balance is less than order total amount")
                ], 400);
            } else if ($order->payment_method->slug != "cash" && $order->delivery_fee > $driverWallet->balance) {
                return response()->json([
                    "message" => __("Insufficient wallet balance, Wallet balance is less than order delivery fee")
                ], 400);
            }
        }


        //
        try {

            //fetch order
            DB::beginTransaction();
            $order = Order::find($id);
            ////prevent driver from accepting a cancelled order
            if (empty($order)) {
                throw new Exception(__("Order could not be found"));
            } else if (!empty($request->driver_id) && in_array($order->status, ["cancelled", "delivered", "failed"])) {
                throw new Exception(__("Order has already been") . " " . $order->status);
            } else if (empty($order) || (!empty($request->driver_id) && !empty($order->driver_id))) {
                throw new Exception(__("Order has been accepted already by another delivery boy"));
            }

            //
            if (!empty($request->driver_id)) {
                $order->driver_id = $request->driver_id;
                $order->save();
            }
            $order->update($request->all());




            //
            if (!empty($request->status)) {
                $order->setStatus($request->status);
            }

            DB::commit();

            return response()->json([
                "message" => __("Order placed ") . __($order->status) . "",
                "order" => Order::fullData()->where("id", $id)->first(),
            ], 200);
        } catch (\Exception $ex) {

            DB::rollback();
            return response()->json([
                "message" => $ex->getMessage()
            ], 400);
        }
    }


    //
    public function getNewOrderStatus(Request $request)
    {

        $orderDate = Carbon::parse("" . $request->pickup_date . " " . $request->pickup_time . "");
        $hoursDiff = Carbon::now()->diffInHours($orderDate);

        if ($hoursDiff > setting('minScheduledTime', 2)) {
            return "scheduled";
        } else {
            return "pending";
        }
    }

    public function recordWalletDebit($wallet, $amount)
    {
        $walletTransaction = new WalletTransaction();
        $walletTransaction->wallet_id = $wallet->id;
        $walletTransaction->amount = $amount;
        $walletTransaction->reason = __("New Order");
        $walletTransaction->status = "successful";
        $walletTransaction->is_credit = 0;
        $walletTransaction->save();
    }
}
