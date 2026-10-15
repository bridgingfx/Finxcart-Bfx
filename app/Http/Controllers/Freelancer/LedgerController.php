<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\CommissionLedger;
use Illuminate\Contracts\View\View;

class LedgerController extends Controller
{
    public function index(): View
    {
        $transactions = CommissionLedger::where('seller_id', auth('freelancer')->id())
            ->where('reference_type', 'freelancer_contract')
            ->with(['contract.customer', 'contract.service'])
            ->latest()
            ->paginate(15);

        return view('freelancer-views.ledger.index', compact('transactions'));
    }
}
