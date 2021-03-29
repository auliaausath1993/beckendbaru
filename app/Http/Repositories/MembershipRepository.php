<?php

namespace App\Http\Repositories;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class MembershipRepository
{
    public function allMembership()
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
            ->where('membership_status', 1)
            ->get();
        return $data;
    }

    public function isMembership($select)
    {
        $data = DB::table('membership_user')
            ->where('users_id', $select)
            ->where('membership_user_status', 1)
            ->exists();
        if ($data){
            $data = DB::table('membership_user')
            ->where('users_id', $select)
            ->where('membership_user_status', 1)
            ->first();

            // CHECK EXPIRED USERS
            $checkExpired = Carbon::now()->diffInDays($data->membership_end, false);
            if ($checkExpired <= 0){
                DB::table('membership_user')
                ->where('users_id', $select)
                ->delete();
                return false;
            }
        }
        return $data;
    }

    public function haveInvoiceUnpaid($select)
    {
        $data = DB::table('transaction')
            ->where('users_id', $select)
            ->where('transaction_status', 'PENDING')
            ->exists();
        return $data;
    }

    public function membershipDetail($request)
    {
        $data = DB::table('membership')
            ->where('membership_code', $request->membership_code)
            ->where('membership_status', 1)
            ->first();
        return $data;
    }

}