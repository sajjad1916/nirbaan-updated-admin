<?php

namespace App\Http\Livewire\Payment;

use App\Models\Order;
use App\Http\Livewire\BaseLivewireComponent;
use Exception;

class OrderPaymentLivewire extends BaseLivewireComponent
{


    public $code;
    public $error;
    public $errorMessage;
    public $done = false;
    public $currency;
    public $paymentStatus;
    protected $queryString = ['code'];
    //
    public $paymentCode;
    public $customView;


    public function render()
    {

        $this->selectedModel = Order::where('code', $this->code)->first();
        $this->paymentStatus = $this->selectedModel->payment_status ?? "";

        //
        if (empty($this->selectedModel)) {
            return view('livewire.payment.invalid')->layout('layouts.auth');
        } else if (!in_array($this->paymentStatus, ['pending', 'review'])) {
            return view('livewire.payment.processed')->layout('layouts.auth');
        } else {
            return view('livewire.payment.order', [
                "order" => $this->selectedModel,
            ])->layout('layouts.guest');
        }
    }


    
    public function initPayment()
    {

        $paymentMethodSlug = $this->selectedModel->payment_method->slug;

    }

   
    public function saveOfflinePayment()
    {
        $this->validate(
            [
                "paymentCode" => "required",
                "photo" => "required|image|max:4096",
            ]
        );


        try {

            \DB::beginTransaction();
            $payment = new Payment();
            $payment->order_id = $this->selectedModel->id;
            $payment->ref = $this->paymentCode;
            $payment->status = "review";
            $payment->save();

         
            $this->selectedModel->payment_status = "review";
            $this->selectedModel->save();

            if ($this->photo) {

                $payment->addMedia($this->photo->getRealPath())->toMediaCollection();
                $this->photo = null;
            }

            \DB::commit();
            $this->errorMessage = __("Payment info uploaded successfully. You will be notified once approved");
        } catch (Exception $error) {
            \DB::rollback();
            $this->error = true;
            $this->errorMessage = $error->getMessage() ?? __("Payment info uploaded failed!");
        }

        $this->done = true;
    }
}
