<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\CommissionLedger;
use App\Models\SellerWallet;
use Illuminate\Contracts\View\View;

class WalletController extends Controller
{
    public function index(): View
    {
        $sellerId = auth('freelancer')->id();

        $wallet = SellerWallet::firstOrCreate(
            ['seller_id' => $sellerId],
            [
                'total_earning' => 0,
                'withdrawn' => 0,
                'commission_given' => 0,
                'pending_withdraw' => 0,
                'delivery_charge_earned' => 0,
                'collected_cash' => 0,
                'total_tax_collected' => 0,
            ]
        );

        $transactions = CommissionLedger::where('seller_id', $sellerId)
            ->with('order')
            ->latest()
            ->paginate(15);

        return view('freelancer-views.wallet.index', compact('wallet', 'transactions'));
    }
}
