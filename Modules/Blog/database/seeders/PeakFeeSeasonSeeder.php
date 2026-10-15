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
 * Daily Content Machine - 2026-10-11.
 *
 * Seeds one original editorial article into the Blog module the same way the
 * admin panel stores it (blogs + blog_translations + blog_seos rows), so it
 * renders through the existing /blog/{slug} frontend route and theme views.
 * Idempotent: safe to re-run, it updates the article by slug.
 */
class PeakFeeSeasonSeeder extends Seeder
{
    protected const SLUG = 'peak-fee-season-q4-margins-marketplace-sellers-2026';

    protected const COVER_IMAGE = 'peak-fee-season-q4-margins-cover.jpg';

    protected const TITLE = "Peak Fees Return October 15: A Seller's Guide to Q4 Margins";

    protected const META_DESCRIPTION = "Amazon's peak fulfilment fees start 15 October and run to 14 January. A practical playbook for sellers to protect Q4 margins before the window opens.";

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
<p>There is a date circled on every Amazon seller's calendar, and it is this Thursday: 15 October. That is when Amazon's holiday peak fulfilment fees kick in for 2026, and they stay in force until 14 January 2027. Every unit Amazon ships for you in that window costs more to move — an average of $0.32 extra per unit on top of the regular fee, before the fuel and logistics surcharge is applied. If you sell on Amazon, this is the week to redo your numbers. If you sell elsewhere, watch what Amazon does, because the rest of the industry rarely lags far behind.</p>
<p>This is not a new shock; Amazon does it every year. What makes 2026 different is how the charges stack. Back in January, Amazon raised standard FBA fulfilment fees by an average of $0.08 per unit. In April it added a 3.5% fuel and logistics surcharge on US and Canadian FBA fees. Now the peak window arrives on top of both. Sellers who modelled their Q4 margins from last year's costs — or worse, from spring's costs — are about to find out what compounding feels like.</p>
<h2>What the fee window actually is</h2>
<p>The details come straight from Amazon's holiday 2026 notice on Seller Central. Peak fees apply from 15 October 2026 to 14 January 2027 across four fulfilment routes: standard FBA, Remote Fulfillment with FBA, Multi-Channel Fulfillment (MCF), and Buy with Prime. So if you use MCF to ship your own Shopify store's orders through Amazon, you pay the peak rate there too.</p>
<p>Two mechanics catch sellers out every year. First, the rate is decided by the day the shipment leaves the fulfilment centre, not the day the customer ordered. A parcel you pack on 14 October but which ships on the 15th pays peak rates. Second, the 3.5% fuel and logistics surcharge applies on top of the peak fee — it multiplies the whole number, not just the non-peak base. The correct all-in figure for any SKU is the published peak fee times 1.035. Nothing exotic; just a step many spreadsheets skip.</p>
<h2>The $0.32 average hides the real story</h2>
<p>Amazon's published average increase of $0.32 per unit is accurate across the whole catalogue and almost useless for your catalogue. Peak fees are set by size tier, and the spread is wide. Seller Central's own rate cards show the step clearly: a small standard item moves about $0.19 (from $2.49 to $2.68), a large standard item about $0.29 ($3.78 to $4.07), a small bulky item $1.04 ($6.78 to $7.82), and an extra-large item $2.73 ($25.56 to $28.29). Supply Chain Dive's reporting on Amazon's examples gave the same picture in product terms: a phone case up $0.19, a T-shirt up $0.39, a baby cot up $1.04.</p>
<p>Read that twice if you sell anything bulky. At 2,000 units a month, the "average" increase costs you $640 — annoying but absorbable. If your catalogue skews bulky, the real number can be triple that. The surcharge on top adds another few percent to the step itself. This is why the sellers who get hurt are not the ones with the highest fees; they are the ones with the lowest-margin units they forgot to re-check.</p>
<h2>Move 1: re-run margins SKU by SKU, before Thursday</h2>
<p>This week, before the 15th, pull your top sellers and recalculate landed margin with the peak fee and the 3.5% surcharge baked in. Amazon has the peak rates loaded in its Revenue Calculator, the Profit Analytics dashboard, and the Fee and Economics Preview Report, so you can compare fulfilment routes without guessing. Rank every SKU by peak-season contribution margin, not revenue. Any SKU that goes underwater — or close to it — gets a decision this week: price it up, coupon it less, or pause it until mid-January. Selling at a loss to chase rank through the most expensive shipping window of the year is not strategy; it is a donation.</p>
<h2>Move 2: do the deals maths properly</h2>
<p>Prime Big Deal Days just closed on 7 October, and Black Friday Week plus Cyber Monday are next, with deal submissions reportedly closing around 20 October. Amazon's event fee structure is the same as Prime Day: $100 upfront per promotion plus 1.5% of promotional sales, with the variable portion capped at $5,000. That fee sits on top of peak fulfilment. Before submitting, model the full stack — deal fee, promotional discount, peak fulfilment, surcharge — on one line. A Lightning Deal that clears its margin test in February can fail it in November, because the shipping line moved. The sellers who win event season are the ones whose best-performing SKUs were already profitable at peak rates before any discount was applied.</p>
<h2>Move 3: hit the inbound deadlines or sell from thin stock</h2>
<p>Peak rates punish late inventory in two ways: you miss the sales, and you pay for replenishment at the worst time. Amazon's published cutoffs for Black Friday and Cyber Monday eligibility are 14 October for Amazon Warehousing and Distribution shipments, and 21 October for FBA shipments using minimal shipment splits. Most sellers use Amazon-optimised splits, which run slightly later — confirm your exact date in Seller Central and treat the earlier date as your own deadline. Amazon has also warned that fulfilment centre capacity shifts away from receiving toward order processing in November and December, so late replenishments may simply not get checked in. If a SKU is going to run dry in December, it is better to know that in October and route the demand somewhere else than to discover it on 2 December.</p>
<h2>Move 4: protect the bulky and the heavy first</h2>
<p>Given the tier spread, your margin defence should start with the largest items in your catalogue. Three levers are worth checking before the window opens. First, dimensional packaging: Amazon's fees respond to the tier your parcel actually ships in, so sellers who re-boxed or right-sized packaging before peak have recovered part of the step outright. Second, merchant-fulfilled alternatives for your worst-margin heavies — orders you ship yourself pay no peak fulfilment fee at all, which can flip a red SKU green even after you account for your own shipping cost. Third, prune: if a bulky SKU only ever sold at razor-thin margins, the honest move is to let it sit out the peak window and bring it back in mid-January when rates normalise.</p>
<h2>This is not only Amazon's story</h2>
<p>The pattern repeats across markets. In India, Amazon and Flipkart revised seller fee and penalty structures ahead of the festive season: from 17 August, Amazon India charges cancellation fees as a percentage of order value — 10% on orders below Rs 10,000, 8% between Rs 10,001 and Rs 50,000, 5% up to Rs 1,00,000, and 2% above that, plus 18% GST — whenever a seller cancels or fails to ship on time. Closing fees across its fulfilment channels rose from 7 September. Flipkart made similar moves. The details differ by country, but the shape of the season is the same everywhere: platforms pass peak costs to sellers in the fourth quarter, and sellers who repriced in October end up healthier than those who noticed in November.</p>
<h2>Use the season instead of fearing it</h2>
<p>Here is the part sellers forget while staring at the fee schedule. Peak season is also the highest-intent buying season of the year. Shoppers in December are not browsing; they are buying, and conversion rates rise with them. A $0.32 higher fulfilment fee on a SKU that converts 30% better is a trade most sellers would take. The danger was never the fee itself — it is entering the window with February's prices and October's stock plan.</p>
<p>So the work this week is unglamorous and decisive. Re-run every SKU's margin at peak rates. Decide on your deals with the full cost stack on the table. Get inbound stock moving toward the 14 and 21 October cutoffs. And pull your heaviest, thinnest SKUs aside for a hard look. None of this requires a new tool or a consultant. It requires an afternoon with the rate card and the willingness to stop selling the three products that were never going to survive the winter. January's fees return to normal. The question is whether your margins make it to January intact.</p>
HTML;
    }
}
