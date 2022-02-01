<?php

namespace App\Http\Livewire\Payment;

use App\Models\Order;
use App\Http\Livewire\BaseLivewireComponent;


class OrderPaymentCallbackLivewire extends BaseLivewireComponent
{


    public $code;
    public $status;
    public $hash;
    public $rep_status;
    public $transaction_id;
    public $error;
    public $errorMessage;
    protected $queryString = ['code', 'status', 'transaction_id','hash','rep_status'];



    public function mount()
    {
        $this->selectedModel = Order::where('code', $this->code)->first();
        //
        if (empty($this->selectedModel)) {
        } else {

        
        }
    }

    public function render()
    {

        //
        if (empty($this->selectedModel)) {
            return view('livewire.payment.invalid')->layout('layouts.guest');
        } else {
            return view('livewire.payment.callback')->layout('layouts.guest');
        }
    }
}
