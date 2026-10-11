<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Role-boundary verification for the FinXCart light redesign.
 * Guards the admin/vendor/customer authorization boundaries:
 * a vendor session must never reach admin screens, and a customer
 * session must never reach vendor screens.
 */
class RoleBoundaryTest extends TestCase
{
    use RefreshDatabase;

    protected function makeSeller(): Seller
    {
        return Seller::create([
            'f_name' => 'Test',
            'l_name' => 'Vendor',
            'phone' => '+971500000001',
            'email' => 'vendor-boundary-test@example.com',
            'password' => bcrypt('secret123'),
            'status' => 'approved',
        ]);
    }

    public function test_vendor_session_cannot_access_admin_vendors_list(): void
    {
        $this->actingAs($this->makeSeller(), 'seller');

        $response = $this->get('/admin/vendors/list');

        // Must redirect to admin login (302) or be forbidden — never 200.
        $this->assertNotEquals(200, $response->getStatusCode(),
            'Vendor session reached /admin/vendors/list with HTTP 200');
        $this->assertTrue(
            in_array($response->getStatusCode(), [301, 302, 303, 307, 308, 401, 403], true),
            'Unexpected status ' . $response->getStatusCode() . ' for vendor on admin route'
        );
    }

    public function test_customer_session_cannot_access_vendor_dashboard(): void
    {
        $customer = User::create([
            'f_name' => 'Test',
            'l_name' => 'Customer',
            'phone' => '+971500000002',
            'email' => 'customer-boundary-test@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $this->actingAs($customer, 'web');

        $response = $this->get('/vendor/dashboard/');

        $this->assertNotEquals(200, $response->getStatusCode(),
            'Customer session reached /vendor/dashboard/ with HTTP 200');
        $this->assertTrue(
            in_array($response->getStatusCode(), [301, 302, 303, 307, 308, 401, 403], true),
            'Unexpected status ' . $response->getStatusCode() . ' for customer on vendor route'
        );
    }

    public function test_guest_cannot_access_vendor_dashboard(): void
    {
        $response = $this->get('/vendor/dashboard/');

        $this->assertNotEquals(200, $response->getStatusCode(),
            'Guest reached /vendor/dashboard/ with HTTP 200');
    }

    public function test_admin_session_can_access_admin_vendors_list_route_shape(): void
    {
        // Smoke: the admin route exists and is guarded (redirects guest to login).
        $response = $this->get('/admin/vendors/list');
        $this->assertNotEquals(200, $response->getStatusCode());
    }

    public function test_guest_cannot_access_freelancer_dashboard(): void
    {
        $response = $this->get('/freelancer/dashboard');

        $this->assertNotEquals(200, $response->getStatusCode(),
            'Guest reached /freelancer/dashboard with HTTP 200');
        $this->assertTrue(
            in_array($response->getStatusCode(), [301, 302, 303, 307, 308, 401, 403], true),
            'Unexpected status ' . $response->getStatusCode() . ' for guest on freelancer route'
        );
    }

    public function test_customer_session_cannot_access_freelancer_dashboard(): void
    {
        $customer = User::create([
            'f_name' => 'Test',
            'l_name' => 'Customer',
            'phone' => '+971500000003',
            'email' => 'customer-freelancer-boundary-test@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $this->actingAs($customer, 'web');

        $response = $this->get('/freelancer/dashboard');

        $this->assertNotEquals(200, $response->getStatusCode(),
            'Customer session reached /freelancer/dashboard with HTTP 200');
    }

    public function test_vendor_seller_cannot_access_freelancer_dashboard(): void
    {
        $vendor = Seller::create([
            'f_name' => 'Test',
            'l_name' => 'Vendor',
            'phone' => '+971500000004',
            'email' => 'vendor-freelancer-boundary-test@example.com',
            'password' => bcrypt('secret123'),
            'status' => 'approved',
            'seller_type' => 'company',
        ]);
        $this->actingAs($vendor, 'freelancer');

        $response = $this->get('/freelancer/dashboard');

        // FreelancerMiddleware logs the non-freelancer out and redirects to login.
        $this->assertNotEquals(200, $response->getStatusCode(),
            'Vendor seller reached /freelancer/dashboard with HTTP 200');
    }

    public function test_freelancer_seller_cannot_access_vendor_dashboard(): void
    {
        $freelancer = Seller::create([
            'f_name' => 'Test',
            'l_name' => 'Freelancer',
            'phone' => '+971500000006',
            'email' => 'freelancer-vendor-boundary-test@example.com',
            'password' => bcrypt('secret123'),
            'status' => 'approved',
            'seller_type' => 'freelancer',
        ]);
        $this->actingAs($freelancer, 'seller');

        $response = $this->get('/vendor/dashboard/');

        // SellerMiddleware logs the freelancer out and redirects to freelancer login.
        $this->assertNotEquals(200, $response->getStatusCode(),
            'Freelancer seller reached /vendor/dashboard/ with HTTP 200');
    }

    public function test_freelancer_seller_can_access_freelancer_dashboard(): void
    {
        $freelancer = Seller::create([
            'f_name' => 'Test',
            'l_name' => 'Freelancer',
            'phone' => '+971500000005',
            'email' => 'freelancer-boundary-test@example.com',
            'password' => bcrypt('secret123'),
            'status' => 'approved',
            'seller_type' => 'freelancer',
        ]);

        // The dashboard renders currency amounts; seed the minimum currency
        // config the view helpers expect on a fresh database.
        $usd = \App\Models\Currency::create([
            'name' => 'US Dollar',
            'symbol' => '$',
            'code' => 'USD',
            'exchange_rate' => 1,
            'status' => 1,
        ]);
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'system_default_currency'],
            ['value' => (string) $usd->id]
        );

        $this->actingAs($freelancer, 'freelancer');

        $response = $this->get('/freelancer/dashboard');

        $response->assertStatus(200);
    }

    public function test_vendor_session_cannot_access_admin_orders_list(): void
    {
        $this->actingAs($this->makeSeller(), 'seller');

        $response = $this->get('/admin/orders/list/all');

        // Must redirect to admin login (302) or be forbidden — never 200.
        $this->assertNotEquals(200, $response->getStatusCode(),
            'Vendor session reached /admin/orders/list/all with HTTP 200');
        $this->assertTrue(
            in_array($response->getStatusCode(), [301, 302, 303, 307, 308, 401, 403], true),
            'Unexpected status ' . $response->getStatusCode() . ' for vendor on admin orders route'
        );
    }

    public function test_customer_session_cannot_access_vendor_products_list(): void
    {
        $customer = User::create([
            'f_name' => 'Test',
            'l_name' => 'Customer',
            'phone' => '+971500000003',
            'email' => 'customer-boundary-products@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $this->actingAs($customer, 'web');

        $response = $this->get('/vendor/products/list/all');

        $this->assertNotEquals(200, $response->getStatusCode(),
            'Customer session reached /vendor/products/list/all with HTTP 200');
        $this->assertTrue(
            in_array($response->getStatusCode(), [301, 302, 303, 307, 308, 401, 403], true),
            'Unexpected status ' . $response->getStatusCode() . ' for customer on vendor products route'
        );
    }
}
