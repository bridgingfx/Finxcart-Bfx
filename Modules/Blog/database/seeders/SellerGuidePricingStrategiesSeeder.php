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
 * Daily Content Machine - 2026-09-25 (article 3 of 3).
 *
 * Seeds one original editorial article into the Blog module the same way the
 * admin panel stores it (blogs + blog_translations + blog_seos rows), so it
 * renders through the existing /blog/{slug} frontend route and theme views.
 * Idempotent: safe to re-run, it updates the article by slug.
 */
class SellerGuidePricingStrategiesSeeder extends Seeder
{
    protected const SLUG = 'pricing-strategies-for-small-marketplace-vendors';

    protected const COVER_IMAGE = 'pricing-strategies-cover.jpg';

    protected const TITLE = 'Pricing for Profit: Smart Pricing Strategies for Small Marketplace Vendors';

    protected const META_DESCRIPTION = 'Too many small sellers price by guessing. Learn how to calculate your true costs, choose a pricing strategy that fits your product, and stop racing competitors to the bottom.';

    public function run(): void
    {
        $category = BlogCategory::firstOrCreate(
            ['name' => 'Seller Guides'],
            ['slug' => 'seller-guides', 'status' => 1]
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
<p>Ask a new marketplace seller how they set their prices and you'll usually hear one of three answers: they copied a competitor, they added a round number to their cost, or they guessed. All three are how stores quietly lose money. Pricing is the one lever that touches everything — your profit, your positioning, your ability to run discounts, and whether your business survives its first year. Here's how to price deliberately instead of hopefully.</p>
<h2>Step one: know your true cost per unit</h2>
<p>You can't price for profit if you don't know what each sale actually costs you. Write down every dirham, not just the product cost:</p>
<p><strong>Product cost</strong> — what you pay per unit, including shipping the stock to you.<br>
<strong>Marketplace commission</strong> — the percentage the platform takes on each sale. Know the exact rate for your category before you price, because it comes straight off the top.<br>
<strong>Payment fees</strong> — card processing and any transaction fees.<br>
<strong>Shipping and packaging</strong> — the courier charge, the box, the tape, the filler. Packaging is a real cost; don't absorb it silently.<br>
<strong>Returns allowance</strong> — a small percentage of orders will come back. If you don't budget for returns, each one eats the profit of several good sales.<br>
<strong>Your time</strong> — listing, packing, customer messages, admin. It may not be a cash cost, but a business that pays its owner nothing is a hobby.</p>
<p>Add them up. That number is your floor. Price below it and you are paying customers to buy from you.</p>
<h2>The margin math, done simply</h2>
<p>Decide the margin you need, then work backwards. If your true cost per unit is AED 60 and you need a 30% margin, your price isn't 60 plus 30% of 60 (AED 78) — that gives you a markup of 30%, but a margin of only about 23%. The margin formula is: <strong>price = cost ÷ (1 − margin)</strong>. For a 30% margin on a 60-cost item: 60 ÷ 0.70 = about AED 86. Keep that distinction straight; confusing markup with margin is one of the most common ways small sellers underprice.</p>
<p>That margin has to fund everything growth requires: ads, discounts, better packaging, new stock, and the occasional bad month. Thin margins leave you no room to maneuver.</p>
<h2>Pick a strategy that fits your product</h2>
<p><strong>Cost-plus pricing.</strong> Cost plus your required margin, as above. Simple, honest, and the right default for most new sellers. It guarantees you don't lose money — but it ignores what customers will pay, so use it as your floor, not your ceiling.</p>
<p><strong>Competitor-aware pricing.</strong> Research what similar products sell for on the marketplace, then position deliberately: match the market, price slightly above with better photos and service, or undercut only if your costs genuinely allow it. Never price against competitors blindly — you don't know their costs, and copying a seller who's losing money just recruits you into losing money with them.</p>
<p><strong>Value-based pricing.</strong> Price on what the product is worth to the buyer, not what it cost you. A handmade item, a hard-to-find part, or a product that solves an urgent problem can carry a higher price if your listing communicates the value. This is where good photos and honest, detailed descriptions pay for themselves.</p>
<p><strong>Charm pricing.</strong> Ending prices in 9 — AED 99 instead of AED 100 — is a retail convention shoppers are used to. It won't rescue a bad product, but it's a legitimate, standard finishing touch on an already sensible price.</p>
<h2>When not to compete on price</h2>
<p>The race to the bottom is the most dangerous game in marketplaces. There is always someone willing to sell cheaper — often someone who doesn't understand their own costs yet and will burn out in months. If your product is genuinely better, your photos are better, your delivery is faster, or your service is more reliable, price accordingly and say why. Shoppers pay premiums for certainty: clear delivery dates, easy returns, and a seller who answers messages.</p>
<p>The exception is launch pricing. A new listing with zero reviews is a harder sell, so a temporary introductory price — clearly temporary — can earn your first reviews faster. Plan the return to normal price from the start, and don't let "introductory" become permanent.</p>
<h2>Treat shipping as part of the price</h2>
<p>Shoppers compare the total at checkout, not the item price. A AED 80 product with AED 20 shipping loses to a AED 95 product with free shipping more often than sellers expect. Fold shipping into your price where you can and advertise free or flat-rate delivery — then make sure your margin math still works with shipping included. And be honest about delivery times; a cheap price that arrives late costs you the repeat customer.</p>
<h2>Review your prices on a schedule</h2>
<p>Prices aren't set-and-forget. Supplier costs rise, courier rates change, commissions get adjusted, and competitors come and go. Put a recurring reminder — monthly for a new store, quarterly once stable — to re-check your true costs against your prices. Small, regular adjustments beat one panicked price hike a year.</p>
<p>Track which products actually make money, not just which ones sell. A bestseller with a 5% margin after all costs is a worse business than a slow seller with a 40% margin. The sales report tells you what moved; the margin report tells you what worked. Run both.</p>
<h2>The mistakes to avoid</h2>
<p>Forgetting fees and commissions when pricing. Copying a competitor's price without knowing their costs. Discounting so often that the "sale" price becomes the real price. Pricing in round numbers because it felt right. And the biggest one: treating pricing as a one-time guess instead of an ongoing discipline. Your prices are a strategy, not a sticker — revisit them, test them, and let the numbers decide.</p>
HTML;
    }
}
