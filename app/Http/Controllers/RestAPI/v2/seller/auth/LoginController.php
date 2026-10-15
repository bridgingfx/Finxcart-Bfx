<?php

namespace App\Http\Controllers\RestAPI\v2\seller\auth;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\SellerWallet;
use App\Utils\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        // Vendor accounts are every seller_type except 'freelancer' (NULL for
        // self-registered, 'company'/'individual' for admin-created) — a
        // Freelancer account can share this email, so both the lookup and the
        // auth attempt must exclude it to avoid matching the wrong row.
        $seller = Seller::where(['email' => $request['email']])
            ->where(function ($query) {
                $query->whereNull('seller_type')->orWhere('seller_type', '!=', 'freelancer');
            })
            ->first();

        $data = [
            'email' => $request->email,
            'password' => $request->password,
            'seller_type' => $seller?->seller_type,
        ];

        if (isset($seller) && $seller['status'] == 'approved' && auth('seller')->attempt($data)) {
            $token = Str::random(50);
            Seller::where(['id' => auth('seller')->id()])->update(['auth_token' => $token]);
            if (SellerWallet::where('seller_id', $seller['id'])->first() == false) {
                DB::table('seller_wallets')->insert([
                    'seller_id' => $seller['id'],
                    'withdrawn' => 0,
                    'commission_given' => 0,
                    'total_earning' => 0,
                    'pending_withdraw' => 0,
                    'delivery_charge_earned' => 0,
                    'collected_cash' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            return response()->json(['token' => $token], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => translate('Invalid credential or account no verified yet')]);
            return response()->json([
                'errors' => $errors
            ], 401);
        }
    }
}
