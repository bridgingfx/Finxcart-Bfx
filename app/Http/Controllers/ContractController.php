<?php

// app/Http/Controllers/ContractController.php

namespace App\Http\Controllers;

use App\Models\SellerContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\VendorTier;
use App\Models\ProductTier;
use PDF; // Requires 'barryvdh/laravel-dompdf'

class ContractController extends Controller
{
    // Helper method to provide the full contract content, formatted as HTML
    private function getContractContent(string $tier): array
    {
        $contracts = [
            'Basic' => [
                'title' => 'Finxcart Basic Tier Seller Agreement',
                'html' => $this->getBasicTierHtml(),
            ],
            'Standard' => [
                'title' => 'Finxcart Standard Tier Seller Agreement',
                'html' => $this->getStandardTierHtml(),
            ],
            'Premium' => [
                'title' => 'Finxcart Premium Tier Seller Agreement',
                'html' => $this->getPremiumTierHtml(),
            ],
            'Elite' => [
                'title' => 'Finxcart Elite Tier Seller Agreement',
                'html' => $this->getEliteTierHtml(),
            ],
            'Enterprise' => [
                'title' => 'Finxcart Enterprise Tier Seller Agreement',
                'html' => $this->getEnterpriseTierHtml(),
            ],
            'One-Time' => [
                'title' => 'Finxcart One-Time Tier Seller Agreement',
                'html' => $this->getOneTimeTierHtml(),
            ],
        ];

        return $contracts[$tier] ?? abort(404, 'Contract tier not found.');
    }

    // --- Methods for full HTML content of each tier ---
public function showTestContractPage()
    {
        // 1. Set the variables you requested
        $tierID = 1;
        $tier_name = 'Basic';

        // 2. Return the view and pass the variables
        // Your JavaScript in 'contract.blade.php' will detect $tierID and $tier_name
        return view('vendor-views.contracts.contract', compact('tierID', 'tier_name'));
    }
private function getBasicTierHtml(): string
{
    // Includes full content from sources 1-79, with added sections for Definitions,
    // Finxcart Rights, Indemnification, Termination, Dispute Resolution, and Miscellaneous.
    return '
        <h3>Effective Date: October 22, 2025 </h3>

        <h4>Recitals</h4>
        <p>WHEREAS, Finxcart, Inc. ("Finxcart," "we," "us," or "our") operates an online marketplace platform at www.finxcart.com (the "Platform") that facilitates the buying and selling of services and products; WHEREAS, you ("Seller," "you," or "your") desire to list and sell products on the Platform under the Basic Tier; WHEREAS, this Agreement sets forth the terms and conditions governing your use of the Platform for Basic Tier listings; NOW, THEREFORE, in consideration of the mutual promises and covenants contained herein, the parties agree as follows:</p>

        <h4>Definitions</h4>
        <ul>
            <li><strong>"Product"</strong> means any service, digital good, or item listed by Seller on the Platform.</li>
            <li><strong>"Fees"</strong> means all charges, including monthly fees and any applicable commissions or taxes.</li>
            <li><strong>"Buyer"</strong> means any user purchasing a Product from Seller via the Platform.</li>
            <li><strong>"Content"</strong> means all text, images, videos, and other materials provided by Seller for listings.</li>
        </ul>

        <h4>Tier Description and Grant of Rights</h4>
        <p>The Basic Tier is designed as an entry-level option for low-value, one-time sales Products priced under $500 USD. It is ideal for categories such as basic freelance gigs (e.g., small graphic design tasks) or digital downloads (e.g., simple templates) that are sold once without ongoing support or recurring elements. By entering this Agreement, Finxcart grants you a limited, non-exclusive, revocable license to access and use the Platform\'s features for Basic Tier listings, subject to compliance with all terms herein.</p>
        <p><strong>Features provided include:</strong></p>
        <ul>
            <li>Basic listing capabilities, limited to one (1) image and a 500-word description.</li>
            <li>Standard search visibility within relevant categories.</li>
            <li>Email-based communication for buyer inquiries.</li>
            <li>Basic analytics dashboard displaying views only. You acknowledge that these features are provided to facilitate entry-level participation and may be updated by Finxcart at its sole discretion with reasonable notice.</li>
        </ul>

        <h4>Eligibility and Seller Representations</h4>
        <p><strong>To qualify for the Basic Tier:</strong> Products must be one-time sales priced under $500 USD and comply with Finxcart\'s content guidelines, including prohibitions on illegal, offensive, or infringing materials. Seller must be at least 18 years old (or the age of majority in your jurisdiction) and capable of entering into binding contracts.</p>
        <p><strong>You represent and warrant that:</strong> (i) all information provided during registration is accurate and complete; (ii) your Products do not violate any third-party rights, including intellectual property; (iii) you will comply with all applicable laws, regulations, and taxes related to your sales; and (iv) you have the authority to enter this Agreement.</p>

        <h4>Fees and Payment Terms</h4>
        <ul>
            <li><strong>Monthly Fee:</strong> $49 USD per Product listing, billed on a recurring basis.</li>
            <li><strong>Free First Month:</strong> New Sellers are eligible for one (1) free month on a single Basic Tier Product listing, provided activation occurs within thirty (30) days of signup. Subsequent months will be charged at the standard rate.</li>
            <li><strong>Payment Method:</strong> Fees will be automatically deducted from your linked payment account (e.g., credit card or bank account) on the first day of each billing cycle.</li>
            <li><strong>Late Payments:</strong> Any overdue amounts will incur a late fee of 5% per month or the maximum allowed by law, whichever is less.</li>
            <li><strong>Refunds:</strong> Fees are non-refundable except in cases of Finxcart\'s material breach or as required by applicable law. No prorated refunds for partial months.</li>
            <li><strong>Taxes:</strong> You are responsible for all taxes on your sales; Finxcart may collect and remit sales taxes where required.</li>
        </ul>

        <h4>Seller Obligations</h4>
        <p>You must: Maintain accurate and up-to-date Product descriptions, pricing, delivery timelines, and terms; Respond to Buyer inquiries and disputes within forty-eight (48) hours; Deliver Products promptly and in accordance with described specifications; Uphold high standards of customer service; failure to do so may result in listing suspension or account termination; Prohibit any fraudulent, misleading, or unethical practices; Report any Platform issues or security concerns to Finxcart immediately.</p>

        <h4>Finxcart Rights and Obligations</h4>
        <p>Finxcart reserves the right to review, modify, suspend, or remove any listing or Content that violates this Agreement or Platform policies, without liability. We may update fees, features, or terms with thirty (30) days\' written notice via email or Platform notification. Finxcart will process payments from Buyers and disburse net proceeds to you after deducting Fees, typically within seven (7) business days. We provide no guarantees regarding sales volume, Buyer traffic, or Platform uptime, though we aim for 99% availability.</p>

        <h4>Indemnification</h4>
        <p>You agree to indemnify, defend, and hold harmless Finxcart, its affiliates, officers, directors, employees, and agents from any claims, damages, losses, liabilities, costs, and expenses (including reasonable attorneys\' fees) arising from: (i) your Products or Content; (ii) your breach of this Agreement; (iii) violations of law; or (iv) disputes with Buyers.</p>

        <h4>Disclaimers and Limitations of Liability</h4>
        <p>THE PLATFORM IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, OR NON-INFRINGEMENT. FINXCART DOES NOT WARRANT THAT THE PLATFORM WILL BE UNINTERRUPTED, ERROR-FREE, OR SECURE. IN NO EVENT SHALL FINXCART BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING LOST PROFITS, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGES. FINXCART\'S TOTAL LIABILITY UNDER THIS AGREEMENT SHALL NOT EXCEED THE FEES PAID BY YOU IN THE TWELVE (12) MONTHS PRECEDING THE CLAIM.</p>

        <h4>Termination</h4>
        <ul>
            <li>You may terminate this Agreement by removing all listings and providing written notice to Finxcart; any outstanding Fees remain payable.</li>
            <li>Finxcart may terminate or suspend your access immediately for material breach, policy violations, or legal requirements.</li>
            <li>Upon termination, all licenses granted herein cease, and you must cease using the Platform. Surviving provisions include indemnification, limitations of liability, and payment obligations.</li>
        </ul>

        <h4>Dispute Resolution</h4>
        <p>Any disputes arising from this Agreement shall be resolved through binding arbitration in Delaware, USA, under the rules of the American Arbitration Association, except for injunctive relief which may be sought in court.</p>

        <h4>Miscellaneous</h4>
        <ul>
            <li><strong>Entire Agreement:</strong> This document constitutes the entire agreement between the parties, superseding all prior understandings.</li>
            <li><strong>Severability:</strong> If any provision is held invalid, the remainder shall remain in effect.</li>
            <li><strong>Waiver:</strong> No waiver of any term shall be deemed a further or continuing waiver.</li>
            <li><strong>Assignment:</strong> You may not assign this Agreement without Finxcart\'s consent; Finxcart may assign freely.</li>
            <li><strong>Force Majeure:</strong> Neither party is liable for delays due to events beyond reasonable control (e.g., natural disasters).</li>
            <li><strong>Notices:</strong> All notices shall be sent via email to the addresses provided during registration.</li>
        </ul>
    ';
}

/**
 * Generates the complete HTML content for the Standard Tier Seller Agreement,
 * based on the Finxcart Seller E-Contract (Pages 4-6).
 */
private function getStandardTierHtml(): string
{
    // Includes content specific to Standard Tier (sources 83-131)
    return '
        <h3>Effective Date: October 22, 2025</h3>

        <h4>Recitals</h4>
        <p>WHEREAS, Finxcart, Inc. ("Finxcart," "we," "us," or "our") operates an online marketplace platform at www.finxcart.com (the "Platform") that facilitates the buying and selling of services and products; WHEREAS, you ("Seller," "you," or "your") desire to list and sell products on the Platform under the Standard Tier; WHEREAS, this Agreement sets forth the terms and conditions governing your use of the Platform for Standard Tier listings; NOW, THEREFORE, in consideration of the mutual promises and covenants contained herein, the parties agree as follows:</p>

        <h4>Definitions</h4>
        <ul>
            <li><strong>"Product"</strong> means any service, digital good, or item listed by Seller on the Platform.</li>
            <li><strong>"Fees"</strong> means all charges, including monthly fees and any applicable commissions or taxes.</li>
            <li><strong>"Buyer"</strong> means any user purchasing a Product from Seller via the Platform.</li>
            <li><strong>"Content"</strong> means all text, images, videos, and other materials provided by Seller for listings.</li>
        </ul>

        <h4>Tier Description and Grant of Rights</h4>
        <p>The Standard Tier is tailored for mid-range, one-time sales Products priced between $500 and $1,499 USD. It is suitable for categories involving moderate complexity, such as custom website development, consulting services, or marketing packages delivered as one-off projects for small businesses. By entering this Agreement, Finxcart grants you a limited, non-exclusive, revocable license to access and use the Platform\'s features for Standard Tier listings, subject to compliance with all terms herein.</p>
        <p><strong>Features provided include:</strong></p>
        <ul>
            <li>Enhanced listing capabilities, allowing up to five (5) images or videos.</li>
            <li>Priority ranking in category searches (top 10 positions).</li>
            <li>Real-time chat functionality for buyer interactions.</li>
            <li>Basic analytics dashboard displaying views and conversion rates. You acknowledge that these features are intended to support growing providers and may be modified by Finxcart with reasonable notice to enhance Platform performance.</li>
        </ul>

        <h4>Eligibility and Seller Representations</h4>
        <p><strong>To qualify for the Standard Tier:</strong> Products must be one-time sales priced between $500 and $1,499 USD and adhere to Finxcart\'s content and quality standards. Seller must maintain an active account in good standing and comply with all legal requirements.</p>
        <p><strong>You represent and warrant that:</strong> (i) all registration information is truthful; (ii) your Products are original and non-infringing; (iii) you will fulfill all orders in a timely manner; and (iv) you possess all necessary licenses or permissions for your offerings.</p>

        <h4>Fees and Payment Terms</h4>
        <ul>
            <li><strong>Monthly Fee:</strong> $99 USD per Product listing, billed recurringly.</li>
            <li><strong>Free First Month:</strong> New Sellers qualify for one (1) free month on a single Standard Tier Product, provided the listing is activated within thirty (30) days of account creation.</li>
            <li><strong>Payment Method:</strong> Automatic deduction from your designated payment source on the billing date.</li>
            <li><strong>Late Payments:</strong> Overdue Fees will accrue interest at 5% per month or the legal maximum.</li>
            <li><strong>Refunds:</strong> Non-refundable except for Finxcart\'s fault or legal mandates; no partial-month credits.</li>
            <li><strong>Taxes:</strong> Seller bears responsibility for sales taxes; Finxcart may withhold where applicable.</li>
        </ul>

        <h4>Seller Obligations</h4>
        <p>You must: Ensure Product listings are detailed, accurate, and updated as needed; Handle Buyer communications and resolutions professionally within forty-eight (48) hours; Deliver high-quality Products as advertised, including any promised support or warranties; Avoid any practices that could harm Finxcart\'s reputation, such as spam or false advertising; Cooperate with Finxcart in resolving disputes or investigations.</p>

        <h4>Finxcart Rights and Obligations</h4>
        <p>Finxcart may audit listings for compliance and make adjustments as necessary without prior approval. We reserve the right to revise this Agreement with thirty (30) days\' notice. Payment processing is managed by Finxcart, with net funds disbursed to Seller after Fee deduction. While we strive for optimal Platform functionality, no guarantees are made regarding performance or results.</p>

        <h4>Indemnification</h4>
        <p>You agree to indemnify, defend, and hold harmless Finxcart and its related parties from all claims, damages, and expenses arising from your breach, Products, or actions, including reasonable legal fees.</p>

        <h4>Disclaimers and Limitations of Liability</h4>
        <p>THE PLATFORM IS OFFERED "AS IS" WITHOUT ANY WARRANTIES, INCLUDING IMPLIED WARRANTIES OF MERCHANTABILITY OR FITNESS. FINXCART SHALL NOT BE LIABLE FOR ANY SPECIAL, INDIRECT, OR CONSEQUENTIAL DAMAGES. LIABILITY IS LIMITED TO FEES PAID BY YOU IN THE PRIOR TWELVE (12) MONTHS.</p>

        <h4>Termination</h4>
        <ul>
            <li>Termination by Seller: Notify Finxcart in writing and settle all dues.</li>
            <li>Termination by Finxcart: Immediate for violations; notice provided where feasible.</li>
            <li>Post-Termination: Obligations like indemnification survive.</li>
        </ul>

        <h4>Dispute Resolution</h4>
        <p>Disputes will be arbitrated in Delaware, USA, under AAA rules, with courts available for equitable relief.</p>

        <h4>Miscellaneous</h4>
        <p>Entire Agreement, Severability, Waiver, Assignment, Force Majeure, and Notices as detailed in the Basic Tier (incorporated by reference for consistency).</p>
    ';
}

/**
 * Generates the complete HTML content for the Premium Tier Seller Agreement,
 * based on the Finxcart Seller E-Contract (Pages 7-8).
 */
private function getPremiumTierHtml(): string
{
    // Includes content specific to Premium Tier (sources 149-190)
    return '
        <h3>Effective Date: October 22, 2025</h3>

        <h4>Recitals</h4>
        <p>WHEREAS, Finxcart, Inc. ("Finxcart," "we," "us," or "our") operates an online marketplace platform at www.finxcart.com (the "Platform") that facilitates the buying and selling of services and products; WHEREAS, you ("Seller," "you," or "your") desire to list and sell products on the Platform under the Premium Tier; WHEREAS, this Agreement sets forth the terms and conditions governing your use of the Platform for Premium Tier listings; NOW, THEREFORE, in consideration of the mutual promises and covenants contained herein, the parties agree as follows:</p>

        <h4>Definitions</h4>
        <ul>
            <li><strong>"Product"</strong> means any service, digital good, or item listed by Seller on the Platform.</li>
            <li><strong>"Fees"</strong> means all charges, including monthly fees and any applicable commissions or taxes.</li>
            <li><strong>"Buyer"</strong> means any user purchasing a Product from Seller via the Platform.</li>
            <li><strong>"Content"</strong> means all text, images, videos, and other materials provided by Seller for listings.</li>
        </ul>

        <h4>Tier Description and Grant of Rights</h4>
        <p>The Premium Tier is intended for high-value, one-time sales Products priced $1,500 USD or more. It caters to "high-end service" categories, such as comprehensive projects like full e-commerce setups or enterprise branding, delivered as single purchases to larger clients. Finxcart grants you a limited, non-exclusive, revocable license to utilize Premium Tier features.</p>
        <p><strong>Features provided include:</strong></p>
        <ul>
            <li>Unlimited media uploads (images, videos, PDFs).</li>
            <li>Top search ranking and rotational homepage placement.</li>
            <li>Customizable listing templates and priority support with 24-hour response times.</li>
            <li>Detailed analytics, including traffic sources and buyer demographics. These features are provided to enhance visibility and sales for premium offerings, subject to Finxcart\'s ongoing improvements.</li>
        </ul>

        <h4>Eligibility and Seller Representations</h4>
        <p><strong>To qualify for the Premium Tier:</strong> Products must be one-time sales priced $1,500 USD or higher, meeting Finxcart\'s quality and compliance standards. Seller must demonstrate professional capability through account verification if requested.</p>
        <p><strong>You represent and warrant that:</strong> (i) your Products are of high quality and as described; (ii) you hold all necessary rights and licenses; (iii) you will not engage in prohibited activities; and (iv) all Content is original.</p>

        <h4>Fees and Payment Terms</h4>
        <ul>
            <li><strong>Monthly Fee:</strong> $149 USD per Product listing, billed monthly.</li>
            <li><strong>No Free First Month:</strong> This tier does not offer introductory waivers to reflect its premium nature.</li>
            <li><strong>Payment Method:</strong> Automatic billing; failure to pay may result in listing suspension.</li>
            <li><strong>Late Payments:</strong> 5% monthly interest on overdue amounts.</li>
            <li><strong>Refunds and Taxes:</strong> Non-refundable Fees; Seller responsible for taxes.</li>
        </ul>

        <h4>Seller Obligations</h4>
        <p>You must: Provide thorough Product details and adhere to delivery commitments; Resolve Buyer issues promptly and professionally; Maintain confidentiality of Platform data and avoid competitive misuse.</p>

        <h4>Finxcart Rights and Obligations</h4>
        <p>Right to curate listings for Platform integrity. Updates to Agreement with notice. Secure payment processing and fund disbursement.</p>

        <h4>Indemnification</h4>
        <p>Full indemnification for claims related to your activities, as in prior tiers.</p>

        <h4>Disclaimers and Limitations of Liability</h4>
        <p>"As is" provision; no liability for indirect damages; cap at 12-month Fees.</p>

        <h4>Termination</h4>
        <p>Standard termination procedures, with survival of key clauses.</p>

        <h4>Dispute Resolution</h4>
        <p>Arbitration in Delaware, USA.</p>

        <h4>Miscellaneous</h4>
        <p>As detailed in prior contracts.</p>
    ';
}

/**
 * Generates the complete HTML content for the Elite Tier Seller Agreement,
 * based on the Finxcart Seller E-Contract (Page 9).
 */
private function getEliteTierHtml(): string
{
    // Includes content specific to Elite Tier (sources 203-219)
    return '
        <h3>Effective Date: October 22, 2025</h3>

        <h4>Recitals</h4>
        <p>WHEREAS, this Agreement sets forth the terms and conditions governing your use of the Platform for Elite Tier listings.</p>

        <h4>Definitions</h4>
        <p>"Commission" is expanded as a percentage of sales. Other definitions are incorporated by reference from the Basic Tier.</p>

        <h4>Tier Description and Grant of Rights</h4>
        <p>The Elite Tier is for recurring or monthly rental Products with moderate sales volume, typically $500-$1,500 annual value, such as CRM subscriptions or SaaS tools. Features include: auto-billing, advanced analytics (churn, lifetime value), API access, and co-marketing options. License granted for these features.</p>

        <h4>Eligibility and Seller Representations</h4>
        <p>Products must be Recurring Products. Representations on compliance and quality are required.</p>

        <h4>Fees and Payment Terms</h4>
        <ul>
            <li><strong>Fixed Monthly Fee:</strong> $149 USD per Product.</li>
            <li><strong>Commission:</strong> 15% on sales, deducted from disbursements.</li>
            <li>No Free Month; automatic billing applies.</li>
        </ul>

        <h4>Seller Obligations</h4>
        <p>You must: Manage subscriptions; ensure ongoing support.</p>

        <h4>Finxcart Rights and Obligations</h4>
        <p>Finxcart will: Track commissions; provide integration support.</p>

        <h4>Indemnification</h4>
        <p>Broad coverage for subscription-related claims.</p>

        <h4>Disclaimers and Limitations of Liability</h4>
        <p>Standard "as is" provision; liability cap applies.</p>

        <h4>Termination</h4>
        <p>Includes handling of active subscriptions upon termination.</p>

        <h4>Dispute Resolution</h4>
        <p>Arbitration applies.</p>

        <h4>Miscellaneous</h4>
        <p>Standard miscellaneous clauses apply.</p>
    ';
}

/**
 * Generates the complete HTML content for the Enterprise Tier Seller Agreement,
 * based on the Finxcart Seller E-Contract (Page 10).
 */
private function getEnterpriseTierHtml(): string
{
    // Includes content specific to Enterprise Tier (sources 229-244)
    return '
        <h3>Effective Date: October 22, 2025</h3>

        <h4>Recitals</h4>
        <p>WHEREAS, this Agreement is tailored for high-value recurring Products.</p>

        <h4>Definitions</h4>
        <p>Includes "LTV" for lifetime value. Other definitions are incorporated by reference from the Basic Tier.</p>

        <h4>Tier Description and Grant of Rights</h4>
        <p>This tier is for Products with $1,500+ annual value, like advanced SaaS. Features: All Elite features plus enhanced API, dedicated manager, predictive analytics, premium co-marketing. License granted for these features.</p>

        <h4>Eligibility and Seller Representations</h4>
        <p>This tier is optimized for high LTV. Warranties on scalability are required.</p>

        <h4>Fees and Payment Terms</h4>
        <ul>
            <li><strong>Monthly Fee:</strong> $249 USD per Product.</li>
            <li><strong>Commission:</strong> 10% on sales.</li>
            <li>No Free Month.</li>
        </ul>

        <h4>Seller Obligations</h4>
        <p>You must: Provide enterprise-level support; report metrics.</p>

        <h4>Finxcart Rights and Obligations</h4>
        <p>Finxcart will: Offer dedicated assistance; monitor performance.</p>

        <h4>Indemnification</h4>
        <p>Indemnification for complex integrations.</p>

        <h4>Disclaimers and Limitations of Liability</h4>
        <p>Standard disclaimers and liability cap apply.</p>

        <h4>Termination</h4>
        <p>Termination includes wind-down procedures for subscriptions.</p>

        <h4>Dispute Resolution</h4>
        <p>Arbitration applies.</p>

        <h4>Miscellaneous</h4>
        <p>Standard miscellaneous clauses apply.</p>
    ';
}

/**
 * Generates the complete HTML content for the One-Time Tier Seller Agreement,
 * based on the Finxcart Seller E-Contract (Page 11).
 */
private function getOneTimeTierHtml(): string
{
    // Includes content specific to One-Time Tier (sources 256-269)
    return '
        <h3>Effective Date: October 22, 2025</h3>

        <h4>Recitals</h4>
        <p>WHEREAS, this Agreement is for niche one-time Products like templates and logos.</p>

        <h4>Definitions</h4>
        <p>"Digital Assets" added. Other definitions are incorporated by reference from the Basic Tier.</p>

        <h4>Tier Description and Grant of Rights</h4>
        <p>This tier is commission-based for designated categories (e.g., website templates, logos) at any price. Features match Premium: unlimited media, top ranking, etc. No monthly fee. License granted for these features.</p>

        <h4>Eligibility and Seller Representations</h4>
        <p>Products must be in specific categories, with a high-volume focus. Seller must provide representations regarding IP in digital goods.</p>

        <h4>Fees and Payment Terms</h4>
        <ul>
            <li><strong>Commission:</strong> 30% on sales, no monthly fee.</li>
            <li>Deducted per transaction.</li>
        </ul>

        <h4>Seller Obligations</h4>
        <p>You must: Ensure instant delivery for digital assets.</p>

        <h4>Finxcart Rights and Obligations</h4>
        <p>Finxcart will: Process sales; no upfront costs.</p>

        <h4>Indemnification</h4>
        <p>Indemnification for IP in digital goods.</p>

        <h4>Disclaimers and Limitations of Liability</h4>
        <p>Standard disclaimers and liability cap apply.</p>

        <h4>Termination</h4>
        <p>Immediate; no ongoing commitments.</p>

        <h4>Dispute Resolution</h4>
        <p>Arbitration applies.</p>

        <h4>Miscellaneous</h4>
        <p>Standard miscellaneous clauses apply.</p>
    ';
}


    public function show(string $tierid)
    {


        $tier_name = ProductTier::find($tierid)->name;
        $tierID = $tierid;

        return view('vendor-views.contracts.contract', compact('tierID', 'tier_name'));

    }



    // Handles form submission, file upload, PDF generation, and saving data
    public function signAndSubmit(Request $request)
    {
        // 1. VALIDATION
        $request->validate([
            'tier_name' => 'required|string',
            'seller_full_name' => 'required|string|max:255',
            'agreement_date' => 'required|date',
            'signature_method' => 'required|string|in:upload,draw',

            // ⭐ MODIFIED: Added 'nullable'
            'signature_file_upload' => 'required_if:signature_method,upload|image|max:2048|nullable',

            // ⭐ MODIFIED: Added 'nullable'
            'signature_base64_data' => 'required_if:signature_method,draw|string|nullable',

            'seller_entity' => 'nullable|string|max:255',
            'i_agree' => 'accepted',
        ]);

        $tier = $request->tier_name;
        $sellerId = auth('seller')->id();// Use guard('vendor') if Auth::guard('seller') is not defined
        // Fallback to Auth::id() or error if seller ID is critical and not found
        if (!$sellerId) {
            return response()->json(['message' => 'Authentication Error. Please log in again.'], 401);
        }

        $signaturePath = null;

        // 2. CONDITIONAL SIGNATURE PROCESSING & STORAGE
        if ($request->signature_method === 'upload') {
            $signaturePath = $request->file('signature_file_upload')->store(
                'signatures',
                'public'
            );
        } elseif ($request->signature_method === 'draw') {
            $base64Image = str_replace('data:image/png;base64,', '', $request->signature_base64_data);
            $base64Image = str_replace(' ', '+', $base64Image);
            $imageData = base64_decode($base64Image);

            $fileName = "signature_{$sellerId}_" . time() . '.png';
            $signaturePath = "signatures/{$fileName}";

            Storage::disk('public')->put($signaturePath, $imageData);
        }

        if (!$signaturePath) {
            return response()->json(['message' => 'Could not process the electronic signature.'], 500);
        }

        $signatureFullPath = Storage::disk('public')->path($signaturePath);

        // 3. Prepare Data for PDF Generation
        $contractContent = $this->getContractContent($tier);
        $data = [
            'contract_title' => $contractContent['title'],
            'contract_html_content' => $contractContent['html'],
            'seller_full_name' => $request->seller_full_name,
            'agreement_date' => $request->agreement_date,
            'seller_entity' => $request->seller_entity,
            'signature_image_path' => $signatureFullPath,
            'signer_ip_address' => $request->ip(),
        ];

        // 4. Generate PDF and Save File
        // Ensure you have a view file at resources/views/vendor-views/contracts/pdf_template.blade.php
        $pdf = PDF::loadView('vendor-views.contracts.pdf_template', $data);
        $fileName = "contract_{$sellerId}_{$tier}_" . time() . ".pdf";
        $pdfStoragePath = 'contracts/' . $fileName;

        // Save PDF to storage/app/contracts/ (default disk)
        Storage::put($pdfStoragePath, $pdf->output());

        // 5. Save Data to DB
        // Ensure SellerContract model exists with fillable fields
        SellerContract::create([
            'seller_id' => $sellerId,
            'tier_name' => $tier,
            'seller_full_name' => $request->seller_full_name,
            'seller_entity' => $request->seller_entity,
            'agreement_date' => $request->agreement_date,
            'signature_image_path' => $signaturePath,
            'contract_pdf_path' => $pdfStoragePath,
            'agreed_to_terms' => true,
            'ip_address' => $request->ip(), // Storing IP for audit
        ]);

        // ⭐ 6. Return JSON response for AJAX to trigger download and redirect ⭐
        return response()->json([
            'success' => true,
            'message' => 'Contract signed successfully! Download starting...',
            'download_file_name' => $fileName,
            'pdf_storage_path' => $pdfStoragePath,
        ]);
    }

    /**
     * Handles the GET request to initiate the file download.
     * Route: GET vendor/contract/download (vendor.contract.download)
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadContract(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'name' => 'required|string',
        ]);

        $storagePath = $request->input('path');
        $fileName = $request->input('name');

        // Security check: Only allow downloading files stored in the 'contracts/' directory
        if (!str_starts_with($storagePath, 'contracts/')) {
             return back()->with('error', translate('Invalid_download_request.'));
        }

        if (!Storage::exists($storagePath)) {
            return back()->with('error', translate('Requested_contract_file_not_found.'));
        }

        // Initiate the file download using the default disk
        // Initiate the file download using the default disk
        return Storage::download($storagePath, $fileName);
    }
    public function getContractHtml(string $tier)
    {
        $contract = $this->getContractContent($tier);

        // Return JSON response containing the HTML content
        return response()->json([
            'title' => $contract['title'],
            'html' => $contract['html'],
        ]);
    }
}
