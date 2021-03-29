<?php

namespace App\Http\Controllers\Api\Payment;
use App\Models\Partners;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Repositories\PaymentRepository;

class PaymentController extends ApiController
{
    public function __construct(PaymentRepository $payRepo) {
        $this->payRepo = $payRepo;
    }

    public function make_invoice(Request $request)
    {
        $user = Auth::guard('apipartner')->user();
        $data = Partners::find($user->partner_id);

        $data = $this->payRepo->makeInvoice($request, $data);

        if ($data) {
            return response()->json([
                'status_code' => 200,
                'status' => 'success',
                'message' => 'Successfully Create Invoice',
                'data' => []
            ], 200);
        } else {
            return response()->json([
                'status_code' => 422,
                'status' => 'error',
                'message' => 'Unknown Error',
                'data' => [],
            ], 422);
        }
    }
    public function data_invoice()
    {
        $user = Auth::guard('apipartner')->user();
        $data = Partners::find($user->partner_id);

        $checkStatus = $this->payRepo->checkStatusInvoice($data);
        $data = $this->payRepo->getListInvoice($data);

        return response()->json([
            'status_code' => 200,
            'status' => 'success',
            'message' => 'Successfully',
            'data' => $data
        ], 200);
    }
    
}