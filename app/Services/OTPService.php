<?php

namespace App\Services;

use Aloha\Twilio\Twilio;
use Illuminate\Support\Facades\Http;
use Loafer\GatewayApi\GatewayApi;

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
        } else if ($enabledSmsGateway == "gatewayapi") {

            //            
            $sender = setting("sms_gateways.gatewayapi.sender");
            $apiToken = setting("sms_gateways.gatewayapi.token");


            //Send an SMS using Gatewayapi.com
            $url = "https://gatewayapi.com/rest/mtsms";

            //Set SMS recipients and content
            $recipients = [$phone];
            $json = [
                'sender' => '' . $sender . '',
                'message' => $message,
                'recipients' => [],
            ];
            foreach ($recipients as $msisdn) {
                $json['recipients'][] = ['msisdn' => $msisdn];
            }

            //Using the built-in 'curl' library
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            curl_setopt($ch, CURLOPT_USERPWD, $apiToken . ":");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //get response
            $output = curl_exec($ch);
            $isError = 0;
            $errorMessage = true;
            logger("output", [$output]);
            //Print error if any
            if (curl_errno($ch)) {
                $isError = true;
                $errorMessage = curl_error($ch);
            }
            curl_close($ch);
            if ($isError) {
                throw new \Exception($errorMessage, 1);
            }
        } else if ($enabledSmsGateway == "msg91") {
            $authKey = setting("sms_gateways.msg91.authkey");
            $sender = setting("sms_gateways.msg91.sender");
            $routeNo = setting("sms_gateways.msg91.route");

            //Preparing post parameters
            $postData = array(
                'authkey' => $authKey,
                'mobiles' => $phone,
                'message' => $message,
                'sender' => $sender,
                'route' => $routeNo,
                'response' => "json"
            );

            $url = "https://control.msg91.com/sendhttp.php";

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData
            ));


            //Ignore SSL certificate verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


            //get response
            $output = curl_exec($ch);
            $isError = 0;
            $errorMessage = true;
            logger("output", [$output]);
            //Print error if any
            if (curl_errno($ch)) {
                $isError = true;
                $errorMessage = curl_error($ch);
            }
            curl_close($ch);
            if ($isError) {
                throw new \Exception($errorMessage, 1);
            }
        } else if ($enabledSmsGateway == "termii") {
            $authkey = setting("sms_gateways.termii.authkey");
            $sender = setting("sms_gateways.termii.sender");

            $response = Http::post('https://termii.com/api/sms/send?to=' . $phone . '&from=' . $sender . '&sms=' . $message . '&type=plain&channel=generic&api_key=' . $authkey . '');

            if (!$response->successful()) {
                throw new \Exception($response->body(), 1);
            }
        } else if ($enabledSmsGateway == "africastalking") {

            //
            $authKey = setting("sms_gateways.africastalking.authkey");
            $sender = setting("sms_gateways.africastalking.sender");
            $username = setting("sms_gateways.africastalking.token");

            $url = "https://api.africastalking.com/version1/messaging";
            if (!\App::environment('production')) {
                $url = "https://api.sandbox.africastalking.com/version1/messaging";
            }

            $ch = curl_init();
            $postData = "username=" . $username . "&to=" . $phone . "&message=" . curl_escape($ch, $message) . "";
            if (!empty($sender)) {
                $postData .= "&from=" . $sender . "";
            }

            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json",
                    "Content-Type: application/x-www-form-urlencoded",
                    "apiKey: " . $authKey . "",
                )
            ));


            //Ignore SSL certificate verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


            //get response
            $output = curl_exec($ch);
            $isError = 0;
            $errorMessage = true;
            logger("output", [$output]);
            //Print error if any
            if (curl_errno($ch)) {
                $isError = true;
                $errorMessage = curl_error($ch);
            }
            curl_close($ch);
            if ($isError) {
                throw new \Exception($errorMessage, 1);
            }
        } else if ($enabledSmsGateway == "hubtel") {
            $username = setting("sms_gateways.hubtel.authkey");
            $password = setting("sms_gateways.hubtel.token");
            $sender = setting("sms_gateways.hubtel.sender");

            $response = Http::get(
                "https://smsc.hubtel.com/v1/messages/send?clientsecret={" . $password . "}&clientid={" . $username . "}&from={" . $sender . "}&to=" . $phone . "&content=" . urlencode($message) . ""
            );

            if (!$response->successful()) {
                throw new \Exception($response->body(), 1);
            }
        }
    }
}
