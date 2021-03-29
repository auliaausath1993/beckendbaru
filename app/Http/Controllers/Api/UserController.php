<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\Partners;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\MembershipRepository;
use App\Http\Repositories\PaymentRepository;
use Carbon\Carbon;

class UserController extends ApiController
{
    public function __construct(MembershipRepository $memRepo, PaymentRepository $payRepo) {
        $this->memRepo = $memRepo;
        $this->payRepo = $payRepo;
    }
    public function index() {
        $user = Auth::guard('api')->user();
        $data = Customer::find($user->customer_id);
        if(!$data){
            return $this->setStatusCode(401)->makeResponse(null, 'Failed To Retrieve Data', [], 'error');
        }

        return $this->setStatusCode(200)->makeResponse($data, 'Success Retrieve User');
    }

    public function edit_profile(Request $request)
    {
    	# code...
    	$this->validate($request, [
    		'id' => 'required',
            'email' => 'email|nullable',
            'username' => 'required',
            'phone' => 'required|numeric'
        ]);

        DB::beginTransaction();
        try {
        	$data = array(
        		'name' => $request->username,
            	'phone' => $request->phone,
            	'email' => $request->email
        	);
        	Customer::where('customer_id',$request->id)->update($data);
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

        return response()->json([
            'status_code' => 200,
            'status' => 'success',
            'message' => 'Successfully Edit Profile',
            'data' => []
        ], 200);
    }
	
	public function syaratketentuan()
    {
    	$model = new Content();
		$data = $model->getSyartaKetentuan();
		return response()->json([
            'status_code' => 200,
            'status' => 'success',
            'message' => 'Successfully Load Data',
            'data' => $data
        ], 200);
    }

    public function privasi()
    {
    	$model = new Content();
		$data = $model->getPrivasi();
		return response()->json([
            'status_code' => 200,
            'status' => 'success',
            'message' => 'Successfully Load Data',
            'data' => $data
        ], 200);
    }

    public function aboutus()
    {
    	$model = new Content();
		$data = $model->getAboutUS();
		return response()->json([
            'status_code' => 200,
            'status' => 'success',
            'message' => 'Successfully Load Data',
            'data' => $data
        ], 200);
    }
	
	public function getbooking()
    {
    	$model = new Content();
		$data = $model->getBooking1();
		return response()->json([
            'status_code' => 200,
            'status' => 'success',
            'message' => 'Successfully Load Data',
            'data' => $data
        ], 200);
    }

    public function patnerProfile() {
        $user = Auth::guard('apipartner')->user();
        $data = Partners::find($user->partner_id);
        $checkInvoice = $this->payRepo->checkStatusInvoice($data);
        $isMembership = $this->memRepo->isMembership($data->partner_id);
        $data['membership'] = false;
        if ($isMembership){
            $data['membership'] = true;
            $data['membership_nama'] = $isMembership->membership_nama;
            $data['membership_end'] = Carbon::now()->diffInDays($isMembership->membership_end, false);
        }
        if(!$data){
            return $this->setStatusCode(401)->makeResponse(null, 'Failed To Retrieve Data', [], 'error');
        }

        return $this->setStatusCode(200)->makeResponse($data, 'Success Retrieve User');
    }
	
}

