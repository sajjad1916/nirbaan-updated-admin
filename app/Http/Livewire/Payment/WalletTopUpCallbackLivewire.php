<?php

namespace App\Http\Livewire\Payment;

use App\Http\Livewire\BaseLivewireComponent;
use App\Models\WalletTransaction;


class WalletTopUpCallbackLivewire extends BaseLivewireComponent
{


    
    public $code;
    public $status;
    public $transaction_id;
    public $hash;
    public $rep_status;
    public $error;
    public $errorMessage;
    protected $queryString = ['code', 'status', 'transaction_id','hash','rep_status'];


    public function mount()
    {
        $this->selectedModel = WalletTransaction::with('wallet.user', 'payment_method')->where('ref', $this->code)->first();
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

            return view('livewire.payment.wallet_callback')->layout('layouts.guest');
        }
    }
}
