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
 * Daily Content Machine - 2026-09-27.
 *
 * Seeds one original editorial article into the Blog module the same way the
 * admin panel stores it (blogs + blog_translations + blog_seos rows), so it
 * renders through the existing /blog/{slug} frontend route and theme views.
 * Idempotent: safe to re-run, it updates the article by slug.
 */
class SellerCentralMultichannelSeeder extends Seeder
{
    protected const SLUG = 'amazon-seller-central-multi-channel-dashboard-2026';

    protected const COVER_IMAGE = 'seller-central-multichannel-cover.jpg';

    protected const TITLE = "Your Amazon Dashboard Just Became Your Whole Business: What Seller Central's Multi-Channel Move Means for Marketplace Sellers";

    protected const META_DESCRIPTION = "On September 24, 2026, Amazon announced that US sellers can manage orders and shipment tracking from eBay, Shopify, TikTok, and Walmart inside Seller Central — for free. What the move means, the one honest worry about your data, and what to do about it this week.";

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
<p>On Thursday morning, September 24, Amazon made an announcement that would have been unthinkable ten years ago: it is inviting Walmart inside Seller Central. Independent sellers in Amazon's US store will soon be able to connect their accounts on eBay, Shopify, TikTok, and Walmart — and manage orders and shipment tracking for all of them from the same dashboard they already use for their Amazon business. The rollout will be gradual. It will be free to sellers. And Amazon says more channels and more features are coming over the months ahead.</p>
<p>Read that again, because it is worth sitting with. The company that built its fortune on the Amazon flywheel is offering to be the operating system for sales that happen on rival platforms. This is not a feature. It is a strategy. And if you sell on more than one channel — which Amazon says more than 95% of independent sellers in its store do — it deserves your full attention.</p>
<h2>What was actually announced</h2>
<p>According to PYMNTS' report on September 24, sellers will be able to connect accounts on those four platforms and bring listings and orders into one workspace. No more jumping between five dashboards to find a new order, fix a product page, or chase a tracking number — those jobs land in Seller Central.</p>
<p>Walmart's inclusion is the part that raised eyebrows across the industry. Amazon is letting merchants run their Walmart sales from inside Amazon's own software — a remarkable public admission of how sellers actually operate. David Forsythe, Amazon's vice president of North America seller business, put it plainly to PYMNTS: "For many of those sellers, Walmart is one of those channels." And then the philosophy behind it: "We really believe that when sellers succeed across their entire business, they're going to succeed on Amazon as well. This is just a reality of having multiple channels, and we want to support sellers across all those channels."</p>
<p>The business logic is hard to argue with. Amazon says multi-channel independent sellers drive more than 60% of sales in its store. If Seller Central is where the seller lives all day, making it the home for the whole business is just good retention math.</p>
<h2>The uncomfortable question: your data</h2>
<p>GeekWire's coverage the same day raised the question every experienced seller is already asking in their head: what could Amazon do with order and tracking data from rival platforms flowing through its own dashboard? The company has endured years of scrutiny over how it uses seller data, so the question is not paranoia — it is pattern recognition. Amazon executive Mary Beth Westmoreland addressed it directly in an interview, saying the company will "never, ever" use the data for anything other than showing it to the seller.</p>
<p>That is an unusually strong promise, and it is also one you should weigh against incentives. Nothing in the announcement gives Amazon rights to your Walmart sales data — the pledge is explicit. But a dashboard that sees your entire business is worth more to its owner than it is to you. Treat this as a convenience, not a trust exercise. Watch the terms the way you watch your fees.</p>
<h2>What to do about it this week</h2>
<p>The rollout is gradual, so you may not see the feature in your account yet. When it lands, do not rip out your multichannel tooling on day one. Version one of this is orders, shipment tracking, and listings in one workspace — real, useful, but not your whole stack. Before you cancel anything, wait for feature parity on the jobs your current tools handle: inventory sync, repricing rules, channel-level profit reporting. Let the new dashboard prove itself on your least complicated channel first.</p>
<p>The real opportunity is visibility. The sellers who struggle most with multichannel are the ones who cannot answer a simple question: which channel actually made me money this month? One workspace showing every order and every shipment is a starting point for answering that — but only a starting point. Pair it with the discipline from the margin era: track your true cost per channel, commissions, payment fees, ads, shipping, returns, and stop treating every channel as equally profitable when the numbers say otherwise.</p>
<p>One thing to keep straight: Seller Central will not fulfill your non-Amazon orders. Your 3PL arrangements and Shopify fulfillment workflows stay exactly as disciplined — or as chaotic — as they are today. A unified dashboard reduces tab-switching; it does not reduce operational reality.</p>
<p>And keep one eye on the price of "free." Today's free is a go-to-market strategy, not a gift. The dashboard that consolidates your whole business inside Amazon's software is the dashboard that makes leaving Amazon operationally expensive, even where Amazon's fees stop being competitive. That does not make it a bad deal — it makes it a deal with terms. Read them the way you read everything else that touches your margin.</p>
<h2>The bigger play</h2>
<p>Step back and the pattern is clear. The last decade's platform war was about who owned the buyer. This decade's is about who owns the seller's workflow. ChannelEngine's 2026 survey found sellers running on more channels than ever — 39% now sell on seven or more — while 60% still run their marketplace operations mostly manually. The dashboard that absorbs all of that work, even work done on competitors' platforms, becomes the center of gravity for the business.</p>
<p>Amazon is betting that the center of gravity should be Seller Central. For sellers, the honest read is two-sided: a genuine reduction in daily friction, and a deeper operating relationship with the biggest platform in your stack. Take the friction reduction. Just click "connect" with your eyes open — and keep the spreadsheet that tells you what each channel is really worth, wherever the orders happen to live.</p>
<p class="text-muted"><small>Sources: PYMNTS, "Amazon Brings Walmart Orders Into Seller Central," September 24, 2026; GeekWire, "Amazon expands its seller dashboard to include Walmart, eBay, Shopify and TikTok," September 24, 2026; ChannelEngine 2026 Marketplace Seller Trends Report.</small></p>
HTML;
    }
}
