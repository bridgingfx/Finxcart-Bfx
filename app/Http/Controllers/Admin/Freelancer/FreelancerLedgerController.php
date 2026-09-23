<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\BaseController;
use App\Models\CommissionLedger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FreelancerLedgerController extends BaseController
{
    public function index(?Request $request, string $type = null): View
    {
        $searchValue = $request['searchValue'];
        $deliveryStatus = $request['delivery_status'] ?? '';

        $transactions = CommissionLedger::where('reference_type', 'freelancer_contract')
            ->with(['seller.shop', 'contract.customer', 'contract.service'])
            ->when($deliveryStatus, function ($query) use ($deliveryStatus) {
                $query->whereHas('contract', function ($contractQuery) use ($deliveryStatus) {
                    $contractQuery->where('delivery_status', $deliveryStatus);
                });
            })
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->whereHas('contract', function ($contractQuery) use ($searchValue) {
                    $contractQuery->where('scope', 'like', "%{$searchValue}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($searchValue) {
                            $customerQuery->where('f_name', 'like', "%{$searchValue}%")
                                ->orWhere('l_name', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('service', function ($serviceQuery) use ($searchValue) {
                            $serviceQuery->where('title', 'like', "%{$searchValue}%");
                        });
                })
                    ->orWhereHas('seller', function ($sellerQuery) use ($searchValue) {
                        $sellerQuery->where('f_name', 'like', "%{$searchValue}%")
                            ->orWhere('l_name', 'like', "%{$searchValue}%")
                            ->orWhere('email', 'like', "%{$searchValue}%");
                    });
            })
            ->orderByDesc('id')
            ->paginate(getWebConfig(name: 'pagination_limit'))
            ->appends($request->query());

        return view('admin-views.freelancer.ledger.index', compact('transactions'));
    }
}
