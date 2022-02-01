<?php

namespace App\Http\Livewire;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\SmsGateway;
use Aloha\Twilio\Twilio;
use App\Services\OTPService;
use GeoSot\EnvEditor\Facades\EnvEditor;

class SMSGatewayLivewire extends BaseLivewireComponent
{

    //
    public $model = SmsGateway::class;

    //
    public $name;
    public $isActive;

    //
    public $accountId;
    public $token;
    public $fromNumber;
    //
    public $authkey;
    public $sender;
    public $route;
    public $authSecret;


    //testing
    public $phoneNumber;
    public $testMessage;


    protected $rules = [
        "name" => "required|string",
    ];


    public function render()
    {
        return view('livewire.sms-gateways');
    }

    public function initiateEdit($id)
    {
        $this->selectedModel = $this->model::find($id);
        $this->name = $this->selectedModel->name;
        $this->isActive = $this->selectedModel->is_active;

        //
        if ($this->selectedModel->slug == "twilio") {
            $this->accountId = setting("sms_gateways.twilio.accountId");
            $this->token = setting("sms_gateways.twilio.token");
            $this->fromNumber = setting("sms_gateways.twilio.fromNumber");
        }
        $this->emit('showEditModal');
    }

    public function update()
    {
        //validate
        $this->validate();

        try {

            DB::beginTransaction();
            $model = $this->selectedModel;
            $model->name = $this->name;
            $model->is_active = $this->isActive;
            $model->save();


            //
            if ($this->selectedModel->slug == "twilio") {
                setting([
                    'sms_gateways.twilio.accountId' =>  $this->accountId,
                    'sms_gateways.twilio.token' =>  $this->token,
                    'sms_gateways.twilio.fromNumber' =>  $this->fromNumber,
                ])->save();
            } 
            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Sms Gateway") . " " . __('created successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Sms Gateway") . " " . __('creation failed!'));
        }
    }



    public function testSMS()
    {

        if ($this->selectedModel->slug == "twilio") {
            $accountId = setting("sms_gateways.twilio.accountId");
            $token = setting("sms_gateways.twilio.token");
            $fromNumber = setting("sms_gateways.twilio.fromNumber");
            //
            $twilio = new Twilio($accountId, $token, $fromNumber);
            //send sms
            try {
                $twilio->message($this->phoneNumber, $this->testMessage);
                $this->showSuccessAlert("SMS sent successfully");
            } catch (\Exception $ex) {
                $this->showErrorAlert($ex->getMessage() ?? "SMS Failed to send");
            }
           
        } else {

            //send sms
            try {
                $otpService = new OTPService();
                $otpService->sendOTP($this->phoneNumber, $this->testMessage, $gateway = $this->selectedModel->slug);
                $this->showSuccessAlert("SMS sent successfully");
            } catch (\Exception $ex) {
                $this->showErrorAlert($ex->getMessage() ?? "SMS Failed to send");
            }
        }

        //
    }
}
