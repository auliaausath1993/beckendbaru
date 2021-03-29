<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Partners;
use Illuminate\Http\Request;
use App\Helpers\Otp;
use App\Http\Controllers\Api\ApiController;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends ApiController
{
    public function login(Request $request) {
        $this->validate($request, [
            'phone' => 'numeric|exists:customers,phone|required'
        ]);

        $validTime = 5;

        $expiredTime = Carbon::now()->addMinute($validTime);

        $randOtp = rand(1111,9999);

        $check = Customer::where('phone', $request->phone)
            ->where('otp_expired', '<', Carbon::now()->addSeconds($validTime*60+30))
            ->first();

        if($check !== null) {
            // $otpService = new Otp();
            // $otp = $otpService->generateOtp($validTime);
            // $otpService->sendOtp($request->phone, $otp['otp']);
            $customer = Customer::where('phone', $request->phone)->first();

            $update = Customer::find($customer->customer_id);
            $update->otp = $randOtp;
            $update->otp_expired = $expiredTime;
            $update->save();

            $response = Http::post('https://app.wapibot.com/api/send/text', [
                "apikey" => "49bbca103bc98c2d2eb5bf3eb7c11e6df3b35437",
                "to" => $request->phone,
                "message"  => "Kode OTP anda ".$randOtp
            ]);
        }

        return $this->setStatusCode(200)->makeResponse(null, 'User Found');
    }

    public function logout(Request $request) {
            $token = $request->token;
            JWTAuth::parseToken()->invalidate( $token );
            //remove token if exists from db
            DB::statement("UPDATE customers SET token = '' where token = '$token'");
            return $this->setStatusCode(200)->makeResponse(null, 'User Logged out');
    }
    public function register(Request $request) {

        $this->validate($request, [
            'email' => 'email|nullable|unique:customers,email',
            'name' => 'required',
            'phone' => 'required|numeric|unique:customers,phone'
        ]);
		
		$randOtp = rand(1111,9999);

        $validTime = 5;

        $expiredTime = Carbon::now()->addMinute($validTime);

        DB::beginTransaction();

        try {
            // $otpService = new Otp();
            // $otp = $otpService->generateOtp();
            // $otpService->sendOtp($request->phone, $otp['otp']);
            $data = new Customer();

            $data->customer_id = Uuid::uuid4();
            $data->name = $request->name;
            $data->phone = $request->phone;
            $data->email = $request->email;
            $data->password = rand(111111, 999999);
            $data->otp = $randOtp;
            $data->otp_expired = $expiredTime;
            $data->status = 'active';
            $data->save();
        }catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 422,
                'status' => 'error',
                'message' => 'Unknown Error',
                'data' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        $response = Http::post('https://app.wapibot.com/api/send/text', [
                "apikey" => "49bbca103bc98c2d2eb5bf3eb7c11e6df3b35437",
                "to" => $request->phone,
                "message"  => "Kode OTP anda ".$randOtp
            ]);

        return response()->json([
            'status_code' => 200,
            'status' => 'success',
            'message' => 'Successfully register',
            'data' => []
        ], 200);

    }

    public function register_partner(Request $request) {

        $this->validate($request, [
            'email' => 'email|nullable|unique:customers,email',
            'name' => 'required',
            'phone' => 'required|numeric|unique:customers,phone'
        ]);
		
		$randOtp = rand(1111,9999);

        $validTime = 5;

        $expiredTime = Carbon::now()->addMinute($validTime);

        DB::beginTransaction();

        try {
            // $otpService = new Otp();
            // $otp = $otpService->generateOtp();
            // $otpService->sendOtp($request->phone, $otp['otp']);
            $initUUID = Uuid::uuid4();

            $data = new Partners();
            $data->partner_id = $initUUID;
            $data->name = $request->name;
            $data->phone = $request->phone;
            $data->email = $request->email;
            $data->password = rand(111111, 999999);
            $data->otp = $randOtp;
            $data->otp_expired = $expiredTime;
            $data->status = 'active';
            $data->save();

            $data = [
                'users_id' =>  $initUUID,
                'users_email' => $request->email,
                'membership_code' => 'FREE_MEMBERSHIP',
                'membership_nama' => 'MEMBERSHIP FREE',
                'membership_user_exp' => 60,
                'membership_user_status' => 1,
                'payment_method' => 'FREE_MEMBERSHIP',
                'membership_end' => Carbon::now()->addMonths(2)
            ];

            DB::table('membership_user')->insert($data);

        }catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 422,
                'status' => 'error',
                'message' => 'Unknown Error',
                'data' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        $response = Http::post('https://app.wapibot.com/api/send/text', [
                "apikey" => "49bbca103bc98c2d2eb5bf3eb7c11e6df3b35437",
                "to" => $request->phone,
                "message"  => "Kode OTP anda ".$randOtp
            ]);

        return response()->json([
            'status_code' => 200,
            'status' => 'success',
            'message' => 'Successfully register',
            'data' => []
        ], 200);

    }


    public function patner(Request $request) {
        $this->validate($request, [
            'phone' => 'numeric|exists:Partners,phone|required'
        ]);

        $user = Partners::where(['phone' => $request->phone])->first();

        if($user !== null) {
            $randOtp = rand(1111,9999);
            $validTime = 5;
            $expiredTime = Carbon::now()->addMinute($validTime);
            
            // $otpService = new Otp();
            // $otpService->sendOtp($request->phone, $randOtp);
            // $patner = Partners::where('phone', $request->phone)->first();

            $update = Partners::find($user->partner_id);
            $update->otp = $randOtp;
            $update->otp_expired = $expiredTime;
            $update->save();
            

            return $this->setStatusCode(200)->makeResponse($randOtp, 'Patner Found');
        }
        else
        {
            return $this->setStatusCode(200)->makeResponse(null, 'User Not Found',[], 'Error');
        }
    }


    public function otpConfirmation(Request $request) {
        $this->validate($request, [
            'phone' => 'numeric|exists:customers,phone',
            'otp' => 'numeric|required|digits:4'
        ]);

        $user = Customer::where(['phone' => $request->phone, 'otp' => $request->otp])
                ->where('otp_expired', '>=', Carbon::now())
                ->first();
        $c_id = Customer::where(['phone' => $request->phone])->get();

        if(!$user){
            return $this->setStatusCode(401)->makeResponse(null, 'Unauthorized Otp Code!');
        }

        if(!$userToken = JWTAuth::fromUser($user)){
            return $this->setStatusCode(401)->makeResponse(null, 'Unauthorized Otp Code!');
        }
        //if correct user token, we want to store that token in the db after deleting the previous one
        $token = $user->token;
        if ($token!="") {
            try {
                JWTAuth::parseToken()->invalidate( $token );
            } catch(\Tymon\JWTAuth\Exceptions\JWTException $e){
            }
        }

        $affected = DB::update("update customers set token = '$userToken' where phone = ? and otp = ?", [$request->phone,$request->otp]);
        // return $this->respondWithToken($userToken);
        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'data' => $c_id
        ], 200);
    }

    public function patnerOtpConfirmation(Request $request) {
        $this->validate($request, [
            'phone' => 'numeric|exists:Partners,phone',
            'otp' => 'numeric|required|digits:4'
        ]);

        $user = Partners::where(['phone' => $request->phone, 'otp' => $request->otp])
                ->where('otp_expired', '>=', Carbon::now())
                ->first();
        $p_id = Partners::where(['phone' => $request->phone])->get();

        if(!$user){
            return $this->setStatusCode(401)->makeResponse(null, 'Unauthorized Otp Code!');
        }

        if(!$userToken = JWTAuth::fromUser($user)){
            return $this->setStatusCode(401)->makeResponse(null, 'Unauthorized Otp Code!');
        }
        //if correct user token, we want to store that token in the db after deleting the previous one
        $token = $user->token;
        if ($token!="") {
            try {
                JWTAuth::parseToken()->invalidate( $token );
            } catch(\Tymon\JWTAuth\Exceptions\JWTException $e){
            }
        }

        $affected = DB::update("update partners set token = '$userToken' where phone = ? and otp = ?", [$request->phone,$request->otp]);
        // return $this->respondWithToken($userToken);
        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'data' => $p_id
        ], 200);
    }
}
