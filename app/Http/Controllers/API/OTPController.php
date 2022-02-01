<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OTPService;
use Illuminate\Http\Request;
use App\Models\Otp;




class OTPController extends Controller
{


    public function sendOTP(Request $request)
    {

        //is_login
        //phone

        //verifiy that the number exists 
        if (!empty($request->is_login)) {
            //
            $user = User::where('phone', $request->phone)->first();
            if (empty($user)) {
                return response()->json([
                    "message" => __('Phone number not associated with any account'),
                ], 401);
            }
        }


        ////verification code
        $code = rand(111111, 999999);
        //create or update otp record
        $otp = Otp::updateOrCreate(
            ["phone" => $request->phone],
            ["code" => $code]
        );

        //send the verification code
        $message = "[" . setting('appName', env('APP_NAME')) . "] " . __("Verification Code") . ": " . $code . ".";

        try {
            $otpService = new OTPService();
            $otpService->sendOTP($request->phone, $message);
            return response()->json([
                "message" => __('OTP sent successfully'),
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                "message" => __('OTP failed to send to provided phone number'),
            ], 400);
        }
    }

    public function verifyOTP(Request $request)
    {

        //is_login
        //phone
        //code 

        //
        $otp = Otp::where(
            [
                "phone" => $request->phone,
                "code" => $request->code,
            ]
        )->first();


            //invlaid
        if (empty($otp)) {
            //
            return response()->json([
                "message" => __('Invalid OTP'),
            ], 400);
        }
        
        //
        $otp->delete();
        if(empty($request->is_login)){
            return response()->json([
                "message" => __('OTP verification successful'),
            ], 200);
        }else{
            //
            $user = User::where('phone', $request->phone)->first();
            $authController = new AuthController();
            return $authController->authObject($user);
        }


        
    }
}
