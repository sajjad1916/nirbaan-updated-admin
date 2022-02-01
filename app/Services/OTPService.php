<?php

namespace App\Services;

use Aloha\Twilio\Twilio;
use Illuminate\Support\Facades\Http;


class OTPService
{
    public function __constuct()
    {
        //
    }


    //
    public function sendOTP($phone, $message, $gateway = null)
    {

        $enabledSmsGateway = setting('enabledSmsGateway', 'twilio');
        if ($gateway != null) {
            $enabledSmsGateway = $gateway;
        }

        //
        if ($enabledSmsGateway == "twilio") {
            $accountId = setting("sms_gateways.twilio.accountId");
            $token = setting("sms_gateways.twilio.token");
            $fromNumber = setting("sms_gateways.twilio.fromNumber");
            //
            $twilio = new Twilio($accountId, $token, $fromNumber);
            $twilio->message($phone, $message);
        
        }   
    }
}
