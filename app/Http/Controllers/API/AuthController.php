<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Traits\FirebaseAuthTrait;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Propaganistas\LaravelPhone\PhoneNumber;




class AuthController extends Controller
{

    use FirebaseAuthTrait;
    
    public function verifyPhoneAccount(Request $request)
     {

        $phone = PhoneNumber::make($request->phone)->ofCountry('BD');
        
        $user = User::where('phone', 'like', '%' . $phone . '')->first();

        if (!empty($user)) {
            return response()->json([
            "result"=>true,
            ],200);
        } else {
            return response()->json([
                "message" => __("There is no account accoutiated with provided phone number "),
            ], 400);
        }
    }


    //
    public function login(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
               'phone' => 'phone:' . setting('countryCode', "GH") . '|required',
                'password' => 'required',
            ],
            $messages = [
                'phone.exists' => __('Phone not associated with any account'),
            ]
        );

        if ($validator->fails()) {

            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }

        //
        $phone = PhoneNumber::make($request->phone)->ofCountry('BD');

        $user = User::where('phone', $phone)->first(); 

        if (!empty($request->role) && !$user->hasAnyRole($request->role)) {
            return response()->json([
                "message" => __("Unauthorized Access. Please try with an authorized credentials")
            ], 401);
        
        } else if ($request->role == "manager" && empty($user->vendor_id)) {
            return response()->json([
                "message" => __("Manager is not assigned to a vendor. Please assign manager to vendor and try again")
            ], 401);
        } else if (Auth::attempt(['phone' => $phone, 'password' => $request->password])) 
        {
            //generate tokens
            return $this->authObject($user);
        } else {
            return response()->json([
                "message" => __("Invalid credentials. Please change your password and try again")
            ], 401);
        }
    }


    public function passwordReset(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'phone' => 'phone:' . setting('countryCode', "GH") . '|required'
            ],
            $messages = [
                'phone.exists' => __('Phone not associated with any account'),
            ]
        );

        if ($validator->fails()) {

            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }

        //
        $phone = PhoneNumber::make($request->phone)->ofCountry('BD');
        $user = User::where('phone', 'like', '%' . $phone . '%')->first();
        
        if (empty($user)) {
            return response()->json([
                "message" => __("There is no account accoutiated with provided phone number ") . $phone . "",
            ], 400);
        }

        //verify firebase token
        try {

            //
            $phone = PhoneNumber::make($request->phone)->ofCountry('BD');

            if (!empty($request->firebase_id_token)) {
                $firebaseUser = $this->verifyFirebaseIDToken($request->firebase_id_token);

                //verify that the token belongs to the right user
                if ($firebaseUser->phoneNumber == $phone) {

                    //
                    $user = User::where("phone", $phone)->firstorfail();
                    $user->password = Hash::make($request->password);
                    $user->Save();

                    return response()->json([
                        "message" => __("Account Password Updated Successfully"),
                    ], 200);
                } else {
                    return response()->json([
                        "message" => __("Password Reset Failed"),
                    ], 400);
                }
            } else {
                //verify that the token belongs to the right user
                $user = User::where("phone", $phone)->firstorfail();
                $user->password = Hash::make($request->password);
                $user->Save();

                return response()->json([
                    "message" => __("Account Password Updated Successfully"),
                ], 200);
            }
        } catch (\Expection $ex) {
            return response()->json([
                "message" => $ex->getMessage(),
            ], 400);
        }
    }

    //
    public function register(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'phone' => 'phone:' . setting('countryCode', "GH") . '|unique:users',
                'password' => 'required',
                'userAddress' => 'required|string',
                'actype' => 'required|string',
                
                'acnumber' => 'required|string',

            ],
            $messages = [
                'phone.unique' => __('Phone already associated with an account'),
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }


        try {

            //
            $phone = PhoneNumber::make($request->phone)->ofCountry('BD');
            $acnumber = PhoneNumber::make($request->acnumber)->ofCountry('BD');
            // $rawPhone = PhoneNumber::make($request->phone, setting('countryCode', "GH"))->formatNational();
            // $phone = str_replace(' ', '', $rawPhone); 
            // logger("Phone", [$request->phone, $phone]);
            

            //
            $user = User::where('phone', $phone)->first();
            if (!empty($user)) {
                throw new Exception(__("Account with phone already exists"), 1);
            }


            DB::beginTransaction();
            //
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email ?? NULL;
            $user->phone = $phone;
            $user->country_code = $request->country_code ?? "";
            $user->password = Hash::make($request->password);
            $user->userAddress = $request->userAddress;
            $user->pickupHub = $request->pickupHub ?? "";
            $user->actype = $request->actype;
            $user->acnumber = $acnumber;
            $user->is_active = true;
            $user->save();

            //refer system is enabled
            $enableReferSystem = (bool) setting('enableReferSystem', "0");
            $referRewardAmount = (float) setting('referRewardAmount', "0");
            if ($enableReferSystem && !empty($request->code)) {
                //
                $referringUser = User::where('code', $request->code)->first();
                if (!empty($referringUser)) {
                    $referringUser->topupWallet($referRewardAmount);
                } else {
                    throw new Exception(__("Invalid referral code"), 1);
                }
            }

            //
            if (empty($request->role)) {
                $user->syncRoles("client");
            }

            DB::commit();
            //generate tokens
            return $this->authObject($user);
        } catch (Exception $error) {

            DB::rollback();
            return response()->json([
                "message" => $error->getMessage()
            ], 500);
        }
    }

    //
    public function profileUpdate(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'sometimes|string',
                'email' => 'unique:users,email,' . Auth::id(),
                'phone' => 'phone:' . setting('countryCode', "GH") . '|unique:users,phone,' . Auth::id(),
                'photo' => 'sometimes|nullable|image|max:2048',
                'userAddress'=> 'sometimes|string',
                
                'actype'=> 'sometimes|string',
                'acnumber'=> 'sometimes|string',
            ],
            $messages = [
                'email.unique' => __('Email already associated with an account'),
                'phone.unique' => __('Phone already associated with an account'),
                'photo.max' => __('Photo must be equal or less to 2MB'),
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }

        $phone = PhoneNumber::make($request->phone)->ofCountry('BD');
        $acnumber = PhoneNumber::make($request->acnumber)->ofCountry('BD');

        try {


            DB::beginTransaction();
            //
            $user = User::find(Auth::id());
            $user->name = $request->name ?? $user->name;
            $user->email = $request->email ?? $user->email ?? NULL;
            $user->phone = $phone ?? $user->phone;
            $user->userAddress = $request->userAddress ?? $user->userAddress;
            $user->pickupHub = $request->pickupHub ?? $user->pickupHub;
            $user->actype = $request->actype ?? $user->actype;
            $user->acnumber = $acnumber ?? $user->acnumber;
            $user->country_code = $request->country_code ?? $user->country_code;
            $user->is_online = $request->is_online ?? $user->is_online;
            $user->save();

            if ($request->photo) {
                $user->clearMediaCollection('profile');
                $user->addMedia($request->file('photo'))->toMediaCollection('profile');
            }

            DB::commit();
            //generate tokens
            return response()->json([
                "message" => __("User profile updated successful"),
                "user" => $user,
            ]);
        } catch (Exception $error) {

            logger("Profile", [$error]);
            DB::rollback();
            return response()->json([
                "message" => $error->getMessage()
            ], 500);
        }
    }

    //
    public function changePassword(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'password' => 'required',
                'new_password' => 'required|confirmed',
            ],
        );

        if ($validator->fails()) {
            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }

        //check old password 
        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json([
                "message" => __("Invalid Current Password"),
            ], 400);
        }


        try {


            DB::beginTransaction();
            //
            $user = User::find(Auth::id());
            $user->password = Hash::make($request->new_password);
            $user->save();

            DB::commit();
            //generate tokens
            return response()->json([
                "message" => __("User password updated successful"),
                "user" => $user,
            ]);
        } catch (Exception $error) {

            logger("Profile", [$error]);
            DB::rollback();
            return response()->json([
                "message" => $error->getMessage()
            ], 500);
        }
    }


    //
    public function logout(Request $request)
    {
        $user = User::find(Auth::id());
        if (!empty($user)) {
            if ($user->hasAnyRole('driver')) {
                $user->is_online = 0;
                $user->save();
            }
            Auth::logout();
        }
        return response()->json([
            "message" => "Logout successful"
        ]);
    }

    /**
     *
     * Helpers
     *
     */
    public function authObject($user)
    {

        if (!$user->is_active) {
            throw new Exception(__("User Account is inactive"), 1);
        }
        $user = User::find($user->id);
        $vendor = Vendor::find($user->vendor_id);
        $token = $user->createToken($user->name)->plainTextToken;
        return response()->json([
            "token" => $token,
            "fb_token" => $this->fbToken($user),
            "type" => "Bearer",
            "message" => __("User login successful"),
            "user" => $user,
            "vendor" => $vendor,
        ]);
    }

    public function fbToken($user)
    {

        $uId = "user_id_" . $user->id . "";
        $firebaseAuth = $this->getFirebaseAuth();
        $customToken = $firebaseAuth->createCustomToken($uId);
        $customTokenString = $customToken->toString();
        return $customTokenString;
    }
}
