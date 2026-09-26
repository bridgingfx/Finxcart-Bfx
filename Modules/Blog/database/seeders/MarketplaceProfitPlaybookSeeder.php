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
 * Daily Content Machine - 2026-09-26.
 *
 * Seeds one original editorial article into the Blog module the same way the
 * admin panel stores it (blogs + blog_translations + blog_seos rows), so it
 * renders through the existing /blog/{slug} frontend route and theme views.
 * Idempotent: safe to re-run, it updates the article by slug.
 */
class MarketplaceProfitPlaybookSeeder extends Seeder
{
    protected const SLUG = 'margin-beats-volume-2026-marketplace-seller-playbook';

    protected const COVER_IMAGE = 'marketplace-profit-playbook-cover.jpg';

    protected const TITLE = 'Margin Beats Volume: The 2026 Playbook for Marketplace Sellers Who Want to Stay Profitable';

    protected const META_DESCRIPTION = "ChannelEngine's 2026 survey of 550 marketplace sellers says profit margin has beaten net sales as the top success metric. Here is the practical playbook: price for margin, automate the manual work, and expand channels on purpose.";

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
<p>For years, the scoreboard in marketplace selling was simple: how much did you sell? Revenue was the bragging number — the chart that went up and to the right, the figure sellers quoted at meetups. This month, that scoreboard got rewritten. The new number one metric, according to the people actually running marketplace businesses, is profit margin. And everything else about how sellers operate is shifting to match.</p>
<p>The evidence comes from ChannelEngine's 2026 Marketplace Seller Trends Report, released this month. It is the company's second annual survey, covering 550 ecommerce decision-makers across France, Germany, the Netherlands, the UK, and the US. The headline finding: profit margin per marketplace is now the top success metric, cited by 33% of respondents, up from 31% last year. Net sales — last year's champion — has fallen to third place.</p>
<h2>What else the numbers say</h2>
<p>A few more findings are worth sitting with. Implementing AI to improve marketplace operations is now sellers' single biggest priority, cited by 26% of respondents — a new entry at the top of the rankings, ahead of boosting profitability at 25%. Meanwhile, "increasing marketplace footprint" has dropped from the fourth priority in 2025 to eighth. Sellers are not retreating — 49% added two or three new marketplaces over the past year, and 39% now sell on seven or more, up from 34%. But the emphasis has moved from planting flags to making the flags already planted pay.</p>
<p>The reason shows up in the operations data. Three in five sellers still describe their marketplace operations as mostly manual or only partly automated, and on average 32% of a team's weekly marketplace workload is manual work. Among sellers relying on manual processes, 37% report higher operational costs, 34% say manual work slows their expansion into new marketplaces, and 33% trace listing, pricing, or inventory errors directly to it. In the US, 41% say manual operations slow their expansion. As ChannelEngine CEO Jorrit Steinz put it: "With three in five organizations still relying largely on manual processes or partial automation, much of that opportunity is being absorbed by operational costs."</p>
<p>So the 2026 question is no longer "how do I sell more?" It is "how do I keep more of what I sell?" Here is a practical playbook built on what the data is telling us.</p>
<h2>Play 1: Know your margin per marketplace, not just your revenue</h2>
<p>Notice the report's metric is margin <em>per marketplace</em>, not margin overall. That distinction matters. The same product can be nicely profitable on one platform and a quiet loss-maker on another once commissions, payment fees, ad spend, shipping, and return rates differ by channel.</p>
<p>Build a simple per-channel sheet: for each marketplace, list the true cost of a typical sale — product cost, the platform's commission, payment processing, your average shipping and packaging cost, and a realistic returns allowance. Then compare what is left against what you spend to get the sale, including ads and promotions. Many sellers discover their "bestselling" channel is their thinnest one. You do not have to abandon it — sometimes a thin channel is worth keeping for visibility — but you should know it is thin, price accordingly, and stop treating every channel as equally profitable when the numbers say otherwise.</p>
<h2>Play 2: Automate the repetitive third of your week</h2>
<p>If roughly a third of your marketplace workload is manual, that is where your margin is leaking. The fixes are not exotic: sync inventory across channels so you never oversell, use repricing rules with floors you set (never let automation price below your true cost), and standardize your listing templates so every product launches with complete specs and shipping terms.</p>
<p>The error data makes the case on its own — a third of manual-reliant sellers report increased listing, pricing, or inventory errors. One wrong price or one oversold listing costs more than the time you "saved" by doing it by hand. Start with the task you repeat most often and hate the most; that is usually the one costing you the most money.</p>
<h2>Play 3: Expand channels on purpose, not on autopilot</h2>
<p>Half of sellers added new marketplaces this year, and regional expansion is clearly working for some — eMAG reported this week that the number of sellers expanding regionally through its marketplace rose 25% year over year. But every new channel adds listings to maintain, inventory to sync, fees to track, and customer messages to answer. If your operations are already one-third manual, a new channel mostly buys you more manual work.</p>
<p>Before joining the next marketplace, answer three questions honestly: do this channel's shoppers actually buy what I sell, can I fulfill to its delivery standard without breaking my margins, and do I have the operational bandwidth to run it well? Cross-border selling still has real friction — a September industry report found more than 80% of surveyed MSMEs across six ASEAN economies still face challenges in cross-border e-commerce, from differing rules to logistics and platform access. Expansion pays when your operations are ready for it, not before.</p>
<h2>Play 4: Take social commerce seriously</h2>
<p>One number from the report deserves its own line: 55% of sellers surveyed are now active on TikTok Shop, either domestically or internationally. The UK is the most mature market, while France and Germany — where TikTok Shop only launched in March 2025 — are already showing meaningful scale. Social commerce has moved from experiment to established channel in under two years in some markets.</p>
<p>You do not need to become a content creator overnight. But if more than half of your peers are selling where shoppers already spend their attention, ignoring social commerce is a strategic choice you should make deliberately, not by default.</p>
<h2>Play 5: Adopt AI, but mind the compliance fine print</h2>
<p>With 82% of sellers adopting or considering AI in their marketplace operations, the question is no longer whether to use it but how. The report is candid that adoption remains "supportive rather than transformational" — the biggest barriers are risk, control, and reliability, with compliance, legal, or regulatory risks topping the concern list at 31% (and 41% in Germany, while the UK takes the most progressive approach).</p>
<p>The sensible middle path: let AI draft your product descriptions, categorize your catalog, and flag pricing anomalies — but keep a human on final pricing decisions and anything customer-facing until you trust the output. AI that drafts is an assistant; AI that publishes unchecked is a liability.</p>
<h2>The takeaway</h2>
<p>The 2026 seller is not the one with the most listings on the most marketplaces. It is the one who knows exactly what each sale earns on each channel, has automated the repetitive work that used to eat the margin, and treats every new channel as a margin decision rather than a growth reflex. Volume got sellers here. Margin is what keeps them here — and this year, for the first time, the industry's own survey says so.</p>
HTML;
    }
}
