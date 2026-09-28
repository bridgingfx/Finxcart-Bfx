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
 * Daily Content Machine - 2026-09-28.
 *
 * Seeds one original editorial article into the Blog module the same way the
 * admin panel stores it (blogs + blog_translations + blog_seos rows), so it
 * renders through the existing /blog/{slug} frontend route and theme views.
 * Idempotent: safe to re-run, it updates the article by slug.
 */
class Q4HolidaySellerChecklistSeeder extends Seeder
{
    protected const SLUG = 'q4-holiday-2026-marketplace-seller-checklist';

    protected const COVER_IMAGE = 'q4-holiday-seller-checklist-cover.jpg';

    protected const TITLE = "The Clock Is Already Ticking: Your Marketplace Seller's Q4 Holiday Checklist for 2026";

    protected const META_DESCRIPTION = "It's September 28 and the holiday clock is running. Amazon's peak fees start October 15, inbound deadlines hit mid-October, and 6 in 10 shoppers start buying before Black Friday. A practical Q4 checklist for marketplace sellers — verified deadlines, fees, and the moves to make this week.";

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
<p>Here is the honest truth about the last week of September: if you sell on marketplaces, the holiday season has already started. Not the shopping — the work. The inventory has to move, the fees are about to change, and the deadlines that decide whether your Q4 goes smoothly are measured in days now, not months. Sellers who wait until October to think about the holidays spend December firefighting. Sellers who act this week spend December packing orders.</p>
<p>So treat this as a field manual. Every number below is current and published — no guesswork, no recycled advice from three seasons ago.</p>
<h2>1. Reprice before October 15 — the fee trap is real</h2>
<p>Amazon's holiday peak fulfillment fees run October 15, 2026 through January 14, 2027, and they cover FBA, Remote Fulfillment with FBA, Multi-Channel Fulfillment, and Buy with Prime. The per-unit increase over non-peak rates matches 2025: an average of about $0.32 per unit, with a 3.5% fuel and logistics surcharge stacking on top of it (Chain Store Age, ChainStoreage.com).</p>
<p>The detail that catches sellers off guard: Amazon charges the fulfillment fee when the unit leaves the fulfillment center — not when the customer orders. An order placed October 14 at the old rate becomes a peak-fee order if it ships October 15. If your Q4 pricing was set with today's fees baked in, your margins are about to get tighter than you think. Run your margin math this week with peak fees included, and adjust where you have to. EcomWatch's breakdown puts it plainly: a small standard item with a $3.22 base fee ends up roughly $0.43 above base once the fuel surcharge is added.</p>
<h2>2. Your freight has to leave now</h2>
<p>For Black Friday Week and Cyber Monday, Amazon's inbound deadlines are inventory arrival dates — your products have to be physically received, not just shipped. Per Linnworks' peak-season planning guide: October 14 for Amazon Warehousing and Distribution, October 21 for FBA shipments using minimal shipment splits, and October 28 for FBA shipments using Amazon-optimized shipment splits (the option most sellers choose). Deal submissions close October 20.</p>
<p>That means freight leaves in the first days of October at the latest. Amazon says its fulfillment centers prioritize receiving in September and October, then shift capacity toward processing customer orders in November and December — sellers who arrive late may see lower capacity limits right when they need them most. Stock that misses the window does not just miss a promotion; it misses the highest-traffic days of the year.</p>
<h2>3. Clear dead stock before October 1</h2>
<p>Peak storage fees run October 1 through December 31, and standard-size storage roughly triples during the period (Sellerboard). Every unit sitting in FBA past October 1 is paying premium rent — and if any of it has been there long enough to cross an aged-inventory band in November, you are paying the aged surcharge on top of peak storage on top of a utilization surcharge you may have triggered by sending Q4 depth too early.</p>
<p>The move is simple: look at days of stock left per SKU, and move slow inventory out now. Removals take weeks to process, so this decision belongs to this week, not mid-October. Sellerboard's rule of thumb is worth stealing: 4 to 8 weeks of supply inside FBA for most catalogs, with depth staged in a warehouse or 3PL and replenished on sell-through.</p>
<h2>4. Build the shipping buffer into your offers</h2>
<p>Carrier costs are moving too. The Postal Service filed notice on August 25 for a proposed temporary peak price change covering Priority Mail, USPS Ground Advantage, and two other competitive parcel products — pending regulatory review, rates would take effect October 4 and run to January 17, averaging a 6% increase, higher than last year's peak adjustment (Linnworks). That stacks on the 8% temporary increase to base postage that has been in effect since April.</p>
<p>If you sell on your own channels alongside the marketplaces, check your carrier contracts and rate cards this week. The bigger lesson is psychological: Radial's annual peak-season consumer survey, released September 1, found that 70% of holiday shoppers would trade a discount for guaranteed delivery — and when price and availability are equal, 78% choose faster shipping. A guaranteed-delivery promise converts better than an extra 5% off in a season when shoppers are anxious about timing. Promise only what you can deliver, but do not bury your speed.</p>
<h2>5. The season starts in October, not on Black Friday</h2>
<p>One of the most underpriced facts in e-commerce: most of your holiday customers are already shopping. Infobip's research across nine markets (September 22, 2026) found that nearly six in ten shoppers will have started holiday buying by early November — and only 19% wait until Black Friday week itself to begin.</p>
<p>Translation: if your promotions, inventory depth, and ad budgets are all aimed at the last week of November, you are ignoring the majority of the buying window. Start your early-bird campaigns in October. Keep budget in reserve for December — the "Q5" stretch after Christmas keeps growing as gift cards get redeemed and procrastinators convert. And if your business is on the Gulf, remember the region's own calendar runs through the festive gifting season into Lunar New Year — the extended APAC window is one of the world's most dynamic periods for cross-border commerce, per FedEx's retail analysis.</p>
<h2>6. Your listings are being read by machines now</h2>
<p>The Infobip research had another finding sellers should sit with: 63.2% of consumers have used an AI assistant or chatbot while shopping in the past 12 months, with about 22% doing it frequently. Among shoppers open to AI help, 73% use it to compare products and 68% to find deals matching their preferences. Industry analysis cited by RTB House expects AI chat agents and referral tools to drive up to 20% of e-commerce traffic this season.</p>
<p>AI shopping assistants recommend products by reading structured information — clear titles, complete specifications, honest reviews, consistent categorization. This is the same listing hygiene you have always needed; it is just worth more now. Audit your top 50 SKUs for title clarity, bullet completeness, and accurate attributes. The products with the cleanest data get recommended. The ones with keyword-stuffed titles and missing specs get skipped — by humans and machines alike.</p>
<h2>7. Check your store on a phone. Tonight.</h2>
<p>eMarketer's holiday forecast, reported by Talk Business &amp; Politics, projects mobile will contribute 71.8% of the incremental dollar gains in online sales this season. Black Friday e-commerce sales are pegged at $13.4 billion (up 7.7%), and Cyber Monday is expected to be the biggest online shopping day of 2026 at $16.27 billion, up 7.4% and accounting for 16.5% of all online holiday sales.</p>
<p>That is your season in two sentences: it is mobile, and it is bigger than last year. So load your own storefront on a phone tonight. Check load speed, checkout friction, image quality on small screens, and whether your listings and reviews render cleanly. If you have not tested the full purchase path on mobile in the last month, you are flying blind into the channel carrying most of the growth.</p>
<h2>8. Staff the surge — and the returns</h2>
<p>More orders mean more messages, more "where is my package" tickets, and a return wave in January. Flexible returns were cited by 32% of shoppers as a reason to buy earlier (Radial). If your return policy is unclear or your response times slip during peak, it shows up in reviews right when your traffic is highest.</p>
<p>Line up holiday help now — customer service, packing, and warehouse shifts for the Thanksgiving weekend. Write your FAQ updates and return instructions before the surge, not during it. The sellers who survive Q4 with their ratings intact are the ones whose support operation was built for December in October.</p>
<h2>The checklist, in one place</h2>
<p>This week: run margin math with peak fees, schedule freight to hit inbound deadlines, start removals on dead stock, check carrier rates, and launch your first early-bird promotions. October: monitor receiving, keep ad budgets balanced across the whole season (not just Black Friday week), and audit your mobile experience and top listings. November: staff the surge, watch capacity limits, and hold budget for the December tail. The calendar is not forgiving this year — but it is predictable. Use that.</p>
HTML;
    }
}
