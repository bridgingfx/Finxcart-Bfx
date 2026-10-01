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
 * Daily Content Machine - 2026-10-01.
 *
 * Seeds one original editorial article into the Blog module the same way the
 * admin panel stores it (blogs + blog_translations + blog_seos rows), so it
 * renders through the existing /blog/{slug} frontend route and theme views.
 * Idempotent: safe to re-run, it updates the article by slug.
 */
class ReturnsMarginPlaybookSeeder extends Seeder
{
    protected const SLUG = 'returns-margin-playbook-2026-marketplace-sellers';

    protected const COVER_IMAGE = 'returns-margin-playbook-cover.jpg';

    protected const TITLE = 'The $10-to-$65 Problem: A Returns Playbook for Marketplace Sellers';

    protected const META_DESCRIPTION = "A returned unit costs $10 to $65, and 2026 made it pricier: prepaid return labels, tighter windows, and fees on every return. A practical playbook for marketplace sellers to price, triage, and cut returns.";

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
<p>Here is a number worth sitting with: a single returned item costs a retailer somewhere between $10 and $65 to process, according to recent 2026 industry data (Ringly). Not the refund — the handling. The label, the inbound freight, the opening, the inspection, the relisting or the write-off. And with online return rates running around one in five orders, that line item has quietly become the biggest silent drain on marketplace margins.</p>
<p>The National Retail Federation and Happy Returns put total US retail merchandise returns at $849.9 billion for 2025, with online returns at a 19.3% rate against 15.8% across all retail — roughly 9% of them fraudulent (SaleHoo, citing NRF). Vntana's 2026 data puts the online average a touch higher at 20.8%, with some categories above 30%. This is not a customer-service department. It is a cost centre the size of a small country — and in 2026 it got more expensive to run.</p>
<h2>2026 changed the rules of returns</h2>
<p>The policy changes that landed this year all push in one direction: the seller absorbs more of the cost. Per seller-industry reporting on Amazon's February 2026 updates, every US third-party seller now has to use Amazon's own prepaid return labels — including the high-value items that used to be exempt — and sellers can no longer message the buyer to offer a partial refund or troubleshoot once a return request opens (reported by MyAmazonGuy). Refund timelines were cut from 14 days to 7, the returnless-refund threshold was raised so items under $75 in eligible categories are refunded without the customer sending anything back, and electronics return windows tightened from 30 days to 15 (reported by Jarvio).</p>
<p>Then there is the FBA returns processing fee, which Amazon charges per returned unit: apparel and shoes pay it on every single return — $1.65 for a small standard parcel up to $3.89 plus per-weight charges for heavier tiers — while other categories pay only when their return rate exceeds a per-product threshold, provided they shipped at least 25 units that month (Novadata, verified against Seller Central in September 2026). On top of that, when a customer is refunded, Amazon keeps a refund administration fee out of the referral fee it returns to you. Every piece of this is predictable and priced. Which means it can be planned for.</p>
<h2>The real anatomy of a return's cost</h2>
<p>Most sellers track the refund. Few track the full stack. Beyond the money-back, a return costs you the outbound shipping you cannot recover, the prepaid label you now buy from the marketplace, the labour of receiving and grading the unit, and the markdown when a like-new product can only be relisted as open-box. There is the inventory distortion: a unit that leaves your available stock for two weeks in the middle of Q4 might as well not exist. And there is the slow cost — return data you never look at, which means the same defect triggers the same returns on the next hundred units.</p>
<p>Do the ugly math for your own catalogue: take last quarter's return rate by SKU, multiply by the fully loaded cost of one return, and subtract it from the margin you thought you were making. Sellers who run this exercise for the first time usually find one or two SKUs were losing money on every order all along — not because of the product, but because of the return curve.</p>
<h2>The playbook</h2>
<p><strong>1. Price returns into unit economics, not into hope.</strong> Add a return-reserve line to your per-SKU P&amp;L: return rate × cost per return. If an SKU's reserve eats the margin, you have three levers — raise the price, fix the driver, or delist. Sellers in high-return categories like apparel, where return rates of 15–20% are normal, cannot skip this. Treat the reserve as a cost of goods, not a surprise.</p>
<p><strong>2. Kill the expectation gap.</strong> Nearly half of every dollar returned traces back to one root cause: shoppers could not tell what they were buying until it arrived (Vntana). That is a listing problem, not a product problem. True-to-life dimensions, weight, colour accuracy in real light, materials listed honestly, and review content that answers the actual questions — these are the cheapest return-reduction tools in existence. One practical habit: read your last fifty one-star and two-star reviews and tag each complaint with the listing element that set the wrong expectation.</p>
<p><strong>3. Grade every return; don't just rebox it.</strong> Build a simple triage lane: resellable as new, resellable as open-box or renewed, parts salvage, liquidation, recycle. The difference between a 40% recovery rate and a 70% recovery rate on returned inventory is often one inspection step and one person trained to make the call. Open-box listings on the marketplaces are a genuine second revenue stream for categories like electronics and home goods — but only if the grading is honest and consistent.</p>
<p><strong>4. Make the return-fee decision deliberately.</strong> About 65% of merchants now charge a return shipping fee, averaging $9.04 per return (Ringly). A fee cuts frivolous returns and recovers real cost, but it also costs you some goodwill — shoppers buy more confidently when returns feel free, and Radial's consumer research has repeatedly found delivery and return confidence drives earlier purchasing. There is no universal right answer; there is only your category, your margin, and your customer. What is not defensible is having no policy — the merchants losing money are the ones who never decided.</p>
<p><strong>5. Turn returns into a data feed.</strong> Every return carries a reason. Aggregate them monthly by SKU and reason code, and the top two reasons for your top ten SKUs will hand you a punch list: a sizing chart to fix, a packaging flaw, a colour that photographs wrong, a compatibility note missing from the bullet points. Returns data is free quality control; most sellers throw it away.</p>
<p><strong>6. Plan for the January wave.</strong> Q4 orders become January returns. Staff your receiving lane, keep packaging materials in stock through the new year, and schedule your return-triage the way you schedule inbound freight. Sellers who treat January as a quiet month get buried; the return wave is as predictable as the order wave that caused it.</p>
<h2>Returns are a margin problem, not a service problem</h2>
<p>The marketplaces are not going to make returns cheaper — every policy move this year has moved cost toward sellers. The sellers who win in this environment are not the ones with the most generous policy or the strictest one; they are the ones who measure the cost, price it in, fix what is fixable in the listing, and recover what is recoverable in the warehouse. Returns are 20% of your volume. Start managing them like it.</p>
HTML;
    }
}
