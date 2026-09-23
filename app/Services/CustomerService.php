<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Chatting;
use App\Models\CustomerWallet;
use App\Models\CustomerWalletHistory;
use App\Models\Order;
use App\Models\ProductCompare;
use App\Models\RefundRequest;
use App\Models\RestockProductCustomer;
use App\Models\Review;
use App\Models\ShippingAddress;
use App\Models\SupportTicket;
use App\Models\SupportTicketConv;
use App\Models\WalletTransaction;
use App\Models\Wishlist;
use App\Traits\FileManagerTrait;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    use FileManagerTrait;

    /**
     * @return array[f_name: mixed, l_name: mixed, email: mixed, phone: mixed, country: mixed, city: mixed, zip: mixed, street_address: mixed, password: string]
     */
    public function getCustomerData(object $request):array
    {
        return [
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'country' => $request['country']??null,
            'city' => $request['city']??null,
            'zip' => $request['zip_code']??null,
            'street_address' =>$request['address']??null,
            'password' => bcrypt($request['password'] ?? 'password')
        ];
    }

    public function deleteImage(object|null $data): bool
    {
        if ($data && $data['image']) {
            $this->delete('profile/' . $data['image']);
        };
        return true;
    }

    /**
     * @return array[f_name: mixed, l_name: mixed, email: mixed, phone: mixed, image: string, password: string]
     */
    public function getUpdateData(object $request, object $customer): array
    {
        $data = [
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'password' => $request['password'] ? bcrypt($request['password']) : $customer['password'],
        ];
        if ($request->file('image')) {
            $data['image'] = $customer['image']
                ? $this->update('profile/', $customer['image'], 'webp', $request['image'])
                : $this->upload('profile/', 'webp', $request['image']);
        }
        return $data;
    }

    /**
     * Hard-deletes a customer and every row that references them, matching the
     * same manual-cascade-in-a-transaction approach VendorController::destroy()
     * already uses for sellers — this codebase has no soft-delete convention and
     * none of these tables have DB-level FK constraints (verified: only the
     * newer freelancer_* and shipment_items tables cascade automatically), so
     * orphaned rows are the default outcome unless each one is deleted
     * explicitly here, in child-before-parent order.
     *
     * File cleanup (profile image, refund proof images) is intentionally kept
     * outside the DB transaction and wrapped in its own try/catch — a missing or
     * unreadable file on disk must never abort the row deletion itself, since
     * the row data is what actually needs to be gone.
     *
     * Freelancer contracts/messages/deliverables are NOT handled here: those
     * tables DO have real cascadeOnDelete() constraints, so the DB removes those
     * rows automatically the moment the customer row is deleted. Their uploaded
     * attachment files on disk are not cleaned up by this method — a pre-existing
     * gap in that subsystem, out of scope for this customer-delete flow.
     */
    public function deleteWithRelatedData(object $customer): void
    {
        $customerId = $customer->id;

        // Snapshot file references before the rows that hold them are deleted,
        // so cleanupCustomerFiles() still knows what to remove from disk afterward.
        $refundImages = [];
        foreach (RefundRequest::where('customer_id', $customerId)->get() as $refundRequest) {
            foreach ((array)($refundRequest->images ?? []) as $image) {
                $imageName = is_array($image) ? ($image['image_name'] ?? null) : $image;
                if ($imageName) {
                    $refundImages[] = $imageName;
                }
            }
        }

        DB::transaction(function () use ($customerId) {
            $orderIds = Order::where('customer_id', $customerId)->pluck('id');

            if ($orderIds->isNotEmpty()) {
                $orderDetailIds = DB::table('order_details')->whereIn('order_id', $orderIds)->pluck('id');
                DB::table('shipment_items')->whereIn('order_detail_id', $orderDetailIds)->delete();
                DB::table('shipments')->whereIn('order_id', $orderIds)->orWhereIn('source_order_id', $orderIds)->delete();

                DB::table('order_status_histories')->whereIn('order_id', $orderIds)->delete();
                DB::table('order_delivery_verifications')->whereIn('order_id', $orderIds)->delete();
                DB::table('order_expected_delivery_histories')->whereIn('order_id', $orderIds)->delete();
                DB::table('offline_payments')->whereIn('order_id', $orderIds)->delete();
                DB::table('deliveryman_notifications')->whereIn('order_id', $orderIds)->delete();
                DB::table('admin_wallet_histories')->whereIn('order_id', $orderIds)->delete();
                DB::table('commission_ledger')->whereIn('order_id', $orderIds)->delete();
                DB::table('transactions')->whereIn('order_id', $orderIds)->delete();
                DB::table('refund_transactions')->whereIn('order_id', $orderIds)->delete();
                DB::table('order_transactions')->whereIn('order_id', $orderIds)->delete();
                DB::table('order_details')->whereIn('order_id', $orderIds)->delete();
            }

            DB::table('orders')->where('customer_id', $customerId)->delete();

            RefundRequest::where('customer_id', $customerId)->delete();
            Review::where('customer_id', $customerId)->delete();
            Wishlist::where('customer_id', $customerId)->delete();
            Cart::where('customer_id', $customerId)->delete();
            ProductCompare::where('user_id', $customerId)->delete();
            ShippingAddress::where('customer_id', $customerId)->delete();
            Chatting::where('user_id', $customerId)->delete();
            RestockProductCustomer::where('customer_id', $customerId)->delete();

            $ticketIds = SupportTicket::where('customer_id', $customerId)->pluck('id');
            if ($ticketIds->isNotEmpty()) {
                SupportTicketConv::whereIn('support_ticket_id', $ticketIds)->delete();
            }
            SupportTicket::where('customer_id', $customerId)->delete();

            WalletTransaction::where('user_id', $customerId)->delete();
            CustomerWalletHistory::where('customer_id', $customerId)->delete();
            CustomerWallet::where('customer_id', $customerId)->delete();

            DB::table('users')->where('id', $customerId)->delete();
        });

        $this->cleanupCustomerFiles($customer, $refundImages);
    }

    /**
     * Best-effort disk cleanup, run after the DB rows are already gone — any
     * failure here (missing file, storage error) is caught and ignored so it
     * can never turn a successful delete into a reported error.
     */
    private function cleanupCustomerFiles(object $customer, array $refundImages = []): void
    {
        try {
            $this->deleteImage(data: $customer);
        } catch (\Throwable $e) {
            \Log::warning('[CustomerService] Failed to delete customer profile image: ' . $e->getMessage());
        }

        foreach ($refundImages as $imageName) {
            try {
                $this->delete('refund/' . $imageName);
            } catch (\Throwable $e) {
                \Log::warning('[CustomerService] Failed to delete refund request image: ' . $e->getMessage());
            }
        }
    }
}
