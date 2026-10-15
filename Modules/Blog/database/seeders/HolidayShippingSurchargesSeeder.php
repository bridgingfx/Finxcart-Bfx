<?php

namespace Modules\Blog\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\Blog\app\Models\Blog;
use Modules\Blog\app\Models\BlogCategory;
use Modules\Blog\app\Models\BlogSeo;
use Modules\Blog\app\Models\BlogTranslation;

/**
 * Daily Content Machine - 2026-10-14.
 *
 * Seeds one original editorial article into the Blog module the same way the
 * admin panel stores it (blogs + blog_translations + blog_seos rows), so it
 * renders through the existing /blog/{slug} frontend route and theme views.
 * Idempotent: safe to re-run, it updates the article by slug.
 */
class HolidayShippingSurchargesSeeder extends Seeder
{
    protected const SLUG = 'holiday-shipping-surcharges-2026-sellers-playbook';

    protected const COVER_IMAGE = 'holiday-shipping-surcharges-2026-cover.jpg';

    protected const TITLE = "Holiday Shipping Surcharges Have Already Started: Your 2026 Carrier Playbook";

    protected const META_DESCRIPTION = "UPS and FedEx holiday surcharges began in late September, and USPS rates rose on 4 October. A practical guide for self-shipping marketplace sellers to budget, pack and price through the 2026 peak window.";

    public function run(): void
    {
        $category = BlogCategory::firstOrCreate(
            ['name' => 'E-commerce Insights'],
            ['slug' => 'e-commerce-insights', 'status' => 1]
        );

        $this->publishCoverImage();

        $blog = Blog::where('slug', self::SLUG)->first();
        if (!$blog) {
            $blog = new Blog();
            $blog->slug = self::SLUG;
            $blog->readable_id = (Blog::max('readable_id') ?? 100000) + 1;
        }
        $blog->category_id = $category->id;
        $blog->writer = 'FinXCart Team';
        $blog->title = self::TITLE;
        $blog->description = $this->articleHtml();
        $blog->image = self::COVER_IMAGE;
        $blog->image_storage_type = 'public';
        $blog->publish_date = now();
        $blog->is_published = 1;
        $blog->status = 1;
        $blog->is_draft = 0;
        $blog->save();

        foreach (['title' => self::TITLE, 'description' => $this->articleHtml()] as $key => $value) {
            BlogTranslation::updateOrInsert(
                [
                    'translation_type' => Blog::class,
                    'translation_id' => $blog->id,
                    'locale' => 'en',
                    'key' => $key,
                    'is_draft' => 0,
                ],
                ['value' => $value, 'updated_at' => now()]
            );
        }

        // NOTE: must be an instance call — BlogSeo defines its own instance
        // updateOrInsert(); the static form never reaches the row.
        (new BlogSeo)->updateOrInsert(
            ['blog_id' => $blog->id],
            [
                'title' => self::TITLE,
                'description' => self::META_DESCRIPTION,
                'index' => '',
                'image' => self::COVER_IMAGE,
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Copy the committed cover art into the public storage disk path the
     * blog frontend expects (storage/app/public/blog/image/...), since
     * storage/ itself is git-ignored and won't exist on fresh deploys.
     */
    protected function publishCoverImage(): void
    {
        $source = public_path('assets/blog/' . self::COVER_IMAGE);
        if (!File::exists($source)) {
            return;
        }
        if (!Storage::disk('public')->exists('blog/image/' . self::COVER_IMAGE)) {
            Storage::disk('public')->put('blog/image/' . self::COVER_IMAGE, File::get($source));
        }
    }

    protected function articleHtml(): string
    {
        return <<<'HTML'
<p>If you self-ship any of your marketplace orders, a line item has been growing on your carrier invoices since late September — and there is nothing seasonal about the timing. Amazon's holiday peak fulfilment fees, which we covered last week, only apply to orders Amazon ships for you. This is the other half of the bill: the holiday surcharges the carriers themselves add to every parcel you send out yourself. They are published, they are dated, and for many sellers they are already being charged.</p>
<p>The strange part is how quietly it started. There was no single announcement day. FedEx published its schedule back in July. UPS followed in late August. USPS filed its temporary rate change in August and got the board sign-off in September. Amazon Shipping announced in early September, DHL eCommerce in late September. Stitched together, here is the 2026 calendar: FedEx from 28 September 2026 to 17 January 2027; UPS from 27 September 2026 to 16 January 2027; USPS from 4 October 2026 to 17 January 2027; Amazon Shipping from 25 October 2026 to 16 January 2027; DHL eCommerce from 25 October 2026 to 16 January 2027. The heaviest weeks — the ones priced like it — run from around 22 November to the day after Christmas across most carriers.</p>
<h2>What each carrier is actually charging</h2>
<p>For ordinary home deliveries, the pattern is remarkably similar. UPS Ground Residential carries a $0.50 demand surcharge per parcel in the opening and closing stretches, rising to $0.75 from 22 November to 26 December. FedEx Ground Residential is $0.50, rising to $0.80 from 23 November to 27 December. Amazon Shipping matches UPS at $0.50 rising to $0.75 from 22 November to 26 December. DHL eCommerce is $0.50 rising to $0.90 over the same peak. USPS works differently: instead of a separate surcharge, its temporary rate increase — roughly 6% on Priority Mail Express, Priority Mail, Ground Advantage and Parcel Select — is baked directly into the price you see when you buy the label, so there is no line item to spot on the invoice. That makes USPS the easiest carrier to under-budget for, because the increase is invisible by design.</p>
<h2>2026 is meaningfully dearer than 2025</h2>
<p>These are not cosmetic adjustments. UPS's residential ground surcharge moved from $0.40 and $0.60 last year to $0.50 and $0.75 — roughly a 25% rise. FedEx moved from $0.40 and $0.65 to $0.50 and $0.80, in the 23–25% range. FedEx's Ground Economy service — the one many sellers use for the cheapest tracked home delivery — carries a demand charge of $2.55 per parcel, rising to $4.05 in the peak weeks. Air and express services moved too: UPS Next Day Air residential is $1.35 rising to $2.50, FedEx Overnight $1.30 to $2.55. If your product mix leans toward express delivery at Christmas, this is where the margin bleeds out fastest.</p>
<h2>The traps cost more than the headline fee</h2>
<p>Three mechanisms hurt more than the fifty pence everyone notices. First, the oversize and additional-handling fees started even earlier — late September at UPS and FedEx — and they are priced to punish sloppy packing: additional handling peaks around $11.85 to $11.90 per parcel, and oversize charges exceed $117 per parcel at the top tier. Second, both big carriers run a separate volume-based schedule for shippers sending more than 20,000 packages in a week; it compares your peak weeks against a June baseline and can reach $8.00 to $9.35 per parcel at the highest peaking tiers. If you are anywhere near that threshold, how you spread volume across weeks matters — pulling orders forward into October can keep you in a lower tier. Third, negotiated contracts can treat individual charges differently, so two sellers shipping identical parcels can pay different surcharges; your invoice is the only rate card that counts.</p>
<h2>Move 1: right-size every parcel before November</h2>
<p>Packaging is now a pricing decision. A parcel that tips into additional-handling territory costs roughly twelve dollars of pure waste per shipment — a charge no amount of negotiating removes, because the surcharge rules apply broadly and cannot be opted out of. Pull your last month of shipments, flag anything with a handling or oversize line, and re-box it. Sellers who standardised mailers and measured dimensions precisely this month will keep more of November than sellers who plan to "look at it in January". The same logic applies to dimensional weight on USPS: its July rate change lowered the dimensional divisor, so a big light box costs more than it did last spring.</p>
<h2>Move 2: ship what you can in October</h2>
<p>The surcharge calendar has a step in it, and the step is exploitable. UPS and FedEx both charge their lower opening rates from late October, then jump to peak rates for the five weeks before Christmas. Any order that can legitimately ship in October — pre-orders, wholesale restocks, holiday bundles offered early — ships at fifty cents instead of seventy-five or eighty. The same goes for your own inventory moves: sending replenishment stock to 3PLs or regional warehouses before the peak weeks means those parcels travel at opening rates too. Early shipping is not just good customer service this year; it is a direct cost cut.</p>
<h2>Move 3: rebuild free-shipping and handling math at peak rates</h2>
<p>A free-shipping threshold set in September assumes September costs. At peak rates, every order below that threshold now costs you fifty to eighty cents more to fulfil — and the carriers' own residential surcharges ($6.45 to $6.50 per ground parcel before peak, $7.25 in the peak weeks on FedEx and UPS) are sitting underneath. If your store offers free shipping over a fixed basket value, run the maths again with the peak surcharges included and decide honestly whether the threshold still pays. Sellers with thin margins often find that raising the threshold by a few dollars for November and December, or trimming the "free shipping" marketing on their lowest-margin SKUs, saves more than any carrier negotiation.</p>
<h2>Move 4: stop using one carrier for everything</h2>
<p>December is the one month where carrier loyalty costs real money. The published schedules make UPS and FedEx home delivery nearly identical in the peak weeks, which means the deciding factor is not the brand — it is the zone, the weight and the service level of each individual parcel. USPS has no separate peak tier, so for small light parcels within its 6% window it can still be the cheapest option even after the increase; Amazon Shipping advertises no residential surcharges or weekend delivery fees, which can help specific routes. The sellers who get through peak cheapest are the ones who rate-shop per parcel, not per quarter.</p>
<h2>Move 5: audit the invoices weekly, not in January</h2>
<p>Carrier billing errors spike in Q4 because the rules get complicated — overlapping surcharge windows, volume tiers computed from baselines you never chose, dimensional weight applied to parcels you packed by hand. The published schedules are your audit script: check that the per-parcel demand charges match the carrier's own calendar, that additional-handling lines correspond to parcels that actually needed handling, and that your negotiated rates are being applied rather than the published ones. One weekly check through December catches more money than one big reconciliation in February, because disputes have time limits.</p>
<h2>The bill is fixed; your exposure is not</h2>
<p>None of this is negotiable away. The surcharges apply broadly, and no amount of loyalty or pleading removes them — what you can control is how much of your volume they touch. Smaller boxes, earlier shipments, honest thresholds, per-parcel routing and a weekly audit do not require new tools or new carriers. They require an hour with the rate schedules and the willingness to stop shipping the way September shipped. The peak window closes in mid-January and rates normalise. Until then, every parcel is a pricing decision — treat it like one.</p>
HTML;
    }
}
