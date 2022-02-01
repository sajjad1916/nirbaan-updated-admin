<?php

namespace App\Http\Livewire\Payment;

use App\Http\Livewire\BaseLivewireComponent;
use App\Models\PaymentMethod;



class WalletTopUpLivewire extends BaseLivewireComponent
{
 

    public $code;
    public $error;
    public $errorMessage;
    public $done = false;
    public $currency;
    public $paymentStatus;
    public $selectedPaymentMethod;
    protected $queryString = ['code'];
    //
    public $paymentCode;


    public function mount()
    {
        $this->selectedModel = WalletTransaction::with('wallet.user', 'payment_method')->where('ref', $this->code)->first();
    }

    public function render()
    {
        //
        if (!in_array($this->selectedModel->status, ['pending'])) {
            return view('livewire.payment.processed')->layout('layouts.guest');
        } else {
            return view('livewire.payment.wallet', [
                "transaction" => $this->selectedModel,
                "paymentMethods" => PaymentMethod::active()->topUp()->get(),
                
            ])->layout('layouts.guest');
        }
    }

    //
    public function initPayment($id)
    {

        $this->selectedPaymentMethod = PaymentMethod::find($id);
        $paymentMethodSlug = $this->selectedPaymentMethod->slug;

        
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
            $this->selectedModel->session_id = $this->paymentCode;
            //payment status
            $this->selectedModel->status = "review";
            $this->selectedModel->payment_method_id = $this->selectedPaymentMethod->id;
            $this->selectedModel->save();

            if ($this->photo) {

                $this->selectedModel->addMedia($this->photo->getRealPath())->toMediaCollection();
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
