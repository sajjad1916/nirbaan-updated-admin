<?php

namespace App\Observers;

use App\Models\Order;
use App\Mail\OrderUpdateMail;
use App\Models\AutoAssignment;
use App\Models\PackageTypePricing;
use App\Services\OrderEarningService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{


    public function creating(Order $model)
    {
        // logger("Pending Order", [$model]);
        $model->code = Str::random(10);
        $model->verification_code = Str::random(5);
        if (empty($model->user_id)) {
            $model->user_id = Auth::id();
        }
    }

    public function created(Order $model)
    {
        //sending notifications base on status change of the order
        // logger("called updated order ===> YES");
        // logger("order created called", [$model->id]);
        $model->sendOrderStatusChangeNotification($model, true);
        $this->sendOrderUpdateMail($model);
        $this->autoMoveToReady($model);
        $this->autoMoveToPreparing($model);
        $this->clearAutoAssignment($model);
    }


    public function updated(Order $model)
    {
        //sending notifications base on status change of the order
        // logger("order updated called started", [$model->id]);
        //driver id changed
        if ($model->isDirty('driver_id')) {
            // $model->sendOrderNotificationToDriver($model);
        }
        //
        $model->refresh();
        // $model->sendOrderStatusChangeNotification($model);
        // $this->sendOrderUpdateMail($model);
        $orderEarningService = new OrderEarningService();
        $orderEarningService->updateEarning($model);

        $model->refundUser();
        $this->autoMoveToReady($model);
        $this->autoMoveToPreparing($model);
        $this->clearAutoAssignment($model);


        // logger("order updated called ended");
    }

    //
    public function sendOrderUpdateMail($model)
    {
        //only delivered
        if (in_array($model->status, ['delivered'])) {
            //send mail
            try {
                \Mail::to($model->user->email)
                    ->cc([$model->vendor->email])
                    ->send(new OrderUpdateMail($model));
            } catch (\Exception $ex) {
                // logger("Mail Error", [$ex]);
                logger("Mail Error");
            }
        }
    }

    public function autoMoveToReady(Order $order)
    {

        //
        $packageTypePricing = PackageTypePricing::where([
            "vendor_id" => $order->vendor_id,
            "package_type_id" => $order->package_type_id,
        ])->first();
        //
        if (
            in_array($order->status, ["pending", "preparing"])
            && ($packageTypePricing->auto_assignment ?? 0)
            && $order->payment_status == "successful"
        ) {
            // logger("Auto move to ready kicked in");
            $order->setStatus("ready");
        }
    }

    public function autoMoveToPreparing(Order $order)
    {

        if (
            in_array($order->status, ["pending"])
            && ($order->vendor->auto_accept ?? 0)
            && $order->payment_status == "successful"
        ) {
            $order->setStatus("preparing");
        }
    }

    public function clearAutoAssignment(Order $order)
    {
        //
        $order->refresh();
        if (in_array($order->status, ["ready", "enroute"])) {
            $autoAssignments = AutoAssignment::where('order_id', $order->id)->get();
            if (count($autoAssignments) > 0) {
                AutoAssignment::where('order_id', $order->id)->delete();
            }
        }
    }
}
