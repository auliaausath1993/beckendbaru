<?php

namespace App\Http\Repositories;
use Illuminate\Support\Facades\DB;
use Xendit\Xendit;
use Carbon\Carbon;
class PaymentRepository
{
    public function __construct()
    {
        $this->key = env('XENDIT_KEY');
    }
    public function makeInvoice($request, $user)
    {
        Xendit::setApiKey($this->key);

        $order_code = $this->generateInvoiceCode('INV');
        // dd($order_code);
        $amount_order = $this->findMembership($request->membership_code);
        // dd($user);
      
        $params = [ 
            'external_id' => $order_code,
            'payer_email' => $user->email,
            'description' => 'Tagihan Pembayaran Pesanan '.$amount_order->membership_nama,
            'should_send_email' => true,
            // 'payment_methods'=>[$request->payment_method],
            // 'invoice_duration' => 1,
             'amount' => $amount_order->membership_harga
        ];
        $createInvoice = \Xendit\Invoice::create($params);
        if (isset($createInvoice['error_code'])) {
            return false;
        }elseif ($createInvoice) {
            $data = [
                'users_id' => $user->partner_id,
                'users_email' => $user->email,
                'membership_nama' => $amount_order->membership_nama,
                'id_invoice' => $createInvoice['id'],
                'transaction_code' => $order_code,
                'transaction_total_pay' => $createInvoice['amount'],
                'transaction_total_items' => 1,
                'membership_user_exp' => $amount_order->membership_exp,
                'transaction_status' => $createInvoice['status'],
                'transaction_payment_evidence' => $createInvoice['invoice_url'],
                'transaction_url' => $createInvoice['invoice_url'],
                'reminder' => date('Y-m-d H:i:s', strtotime('1 hour')),
                'external_id' => $createInvoice['external_id'],
                'virtual_account_number' => $createInvoice['available_banks'][0]['bank_account_number']
            ];
    
            DB::table('transaction')->insert($data);
            
            return $createInvoice;
        }else{
            return false;
        }
        
    }

    public function getListInvoice ($data)
    {
        $select =[
            'transaction.users_id',
            'transaction.id_invoice',
            'transaction.membership_nama',
            'transaction.transaction_code',
            'transaction.transaction_total_pay',
            'transaction.transaction_status',
            'transaction.transaction_url',
            'transaction.transaction_url'
        ];
        $data = DB::table('transaction')
            ->select($select)
            ->where('users_id', $data->partner_id)
            ->get();
        return $data;

    }

    public function generateInvoiceCode ($code)
    {
        $result = $code.'-'.date('s').date('y').date('i').date('m').date('h').date('d').mt_rand(1000000, 9999999);
        return $result;
    }

    public function findMembership ($params)
    {
        $select =[
            'membership.membership_nama',
            'membership.membership_code',
            'membership.membership_deskripsi',
            'membership.membership_harga',
            'membership.membership_exp'
        ];
        $data = DB::table('membership')
            ->select($select)
            ->where('membership_code', $params)
            ->first();
        return $data;
    }

    // check invoice to xendit

    public function checkStatusInvoice($params)
    {
        $transaction = DB::table('transaction')
                        ->where('users_id', $params->partner_id)
                        ->where('transaction_status', 'PENDING')
                        ->get();
        if(count($transaction) > 0)
        {
            Xendit::setApiKey($this->key);
        for ($i=0; $i < count($transaction); $i++) { 
            $getInvoice = \Xendit\Invoice::retrieve($transaction[$i]->id_invoice);
            if ($getInvoice['status'] == 'SETTLED') {
               
                DB::table('transaction')->where('transaction_code', $transaction[$i]->transaction_code)->update(['transaction_status'=>'SETTLED']);
                $paid = Carbon::parse($getInvoice['paid_at']);
                $data = [
                    'users_id' => $params->partner_id,
                    'users_email' => $params->email,
                    'membership_code' => $transaction[$i]->id_invoice,
                    'membership_nama' => $transaction[$i]->membership_nama,
                    'membership_user_exp' => $transaction[$i]->membership_user_exp,
                    'membership_user_status' => 1,
                    'payment_method' => $getInvoice['payment_method'],
                    'membership_end' => $paid->addMonths(1)
                ];
                DB::table('membership_user')->insert($data);
                
            }elseif($getInvoice['status'] == 'EXPIRED'){
                DB::table('transaction')->where('transaction_code', $transaction->transaction_code)->update(['transaction_status'=>'EXPIRED']);
            }
        }
        }

        return true;
    }

}