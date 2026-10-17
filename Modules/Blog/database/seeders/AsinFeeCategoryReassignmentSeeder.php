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
 * Daily Content Machine - 2026-10-17.
 *
 * Seeds one original editorial article into the Blog module the same way the
 * admin panel stores it (blogs + blog_translations + blog_seos rows), so it
 * renders through the existing /blog/{slug} frontend route and theme views.
 * Idempotent: safe to re-run, it updates the article by slug.
 */
class AsinFeeCategoryReassignmentSeeder extends Seeder
{
    protected const SLUG = 'amazon-asin-fee-category-reassignment-october-31';

    protected const COVER_IMAGE = 'amazon-fee-category-reassignment-cover.jpg';

    protected const TITLE = "Amazon Is Reassigning ASIN Fee Categories on 31 October: A Seller Action Guide";

    protected const META_DESCRIPTION = "From 31 October, Amazon is moving some ASINs into different fee categories — which can change your referral fee without touching your listing. Here is how to check Fee Preview, model the margin impact, and dispute a wrong classification before it bills.";

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
<p>There is a fee change coming on 31 October, and it is the sneaky kind. Amazon is not raising any rates this time. Instead, it is reassigning some ASINs into different fee categories — and because your referral fee is set by the fee category, a product can start costing you more (or less) per sale with no announcement on your listing, no price movement, and nothing changing on the page your customers see.</p>
<p>This landed on sellers' radar in mid-October, which gives you roughly two weeks to deal with it. It is worth dealing with, because unlike a rate hike you can argue about at the forum level, this one is silent. The listing looks exactly the same. Only the classification behind it moves.</p>
<h2>Fee categories are not the categories you see</h2>
<p>Here is the distinction that makes this easy to miss. The categories shoppers browse — Electronics, Home &amp; Kitchen, Clothing — are not necessarily the same as the fee category Amazon assigns to your ASIN. Every ASIN belongs to exactly one fee category, but the same product can appear in multiple browse locations for discoverability. The fee category is what Amazon uses to calculate your referral fee, and it can also feed into closing fees, FBA fulfilment fees, and returns processing fees. The referral percentages themselves vary substantially between categories, so a product reclassified from one bucket to another can see a real change in what each sale costs.</p>
<p>The practical consequence: you cannot eyeball your product page to check whether you are affected. The page will not change. The money does.</p>
<h2>Start in the Fee Preview report</h2>
<p>Amazon built a report for exactly this. The Fee Preview report shows an ASIN's fee category and, crucially, upcoming category changes — so you can see the reclassification before it starts hitting transactions. That is your first job this week: open Fee Preview, look for any ASIN flagged with a new category, and note the old and new classification for each one.</p>
<p>Do not review by sales volume alone. A high-volume ASIN with a healthy margin may absorb the shift without drama, while a lower-volume product sitting right at its target margin — or at its advertising break-even — can tip into unprofitable territory on a small percentage change. Rank the affected list by dollar impact: units times the estimated per-unit fee delta. That ordering decides where your hour goes.</p>
<h2>Model the margin, not just the fee</h2>
<p>For each affected ASIN, the maths is short but unforgiving. Compare the referral rate of the current category against the new one, then run the difference through your contribution margin: price minus product cost, minus fulfilment, minus the new referral fee. If the answer leaves your target margin intact, file it and move on.</p>
<p>The number that usually breaks is the advertising break-even — the maximum cost per sale you can afford before the unit loses money. A higher referral fee lowers that ceiling. If an ASIN was already being pushed hard on sponsored ads at the edge of profitability, the new category may mean last month's ad budget is now this month's loss. Check which affected products run ads and whether their break-even still holds. Pricing and promotions you set for Q4 were built on the old fee structure; anything affected needs a fresh look before the peak weeks lock in.</p>
<h2>If the classification is wrong, dispute it now</h2>
<p>Amazon says ASINs are continually audited against its fee-category guidelines, and audits make mistakes. If the proposed category does not fit your product, there is a dispute process — but the time to use it is before 31 October, not after weeks of transactions at the wrong rate. Build the case the way Amazon's own guidance asks: document the ASIN, the current and proposed categories, the product attributes that put it in one bucket and not the other, and the specific Amazon fee-category guidance that supports your classification. Then take it to Selling Partner Support with the justification laid out plainly.</p>
<p>Sellers who dispute fees successfully tend to have one thing in common: they keep records. Save the Fee Preview export showing the upcoming change, note the date you contacted support, and keep the thread reference. If the reassignment sticks and you are sure it is wrong, that paper trail is your evidence.</p>
<h2>Watch the timing — it lands inside peak season</h2>
<p>The date matters as much as the change. 31 October is two weeks into the holiday peak fulfilment fee window (15 October to 14 January), which means any cost movement from reclassification arrives exactly when per-unit costs are already at their highest and when every invoice is noisier than usual. That is precisely the environment where a few extra points of referral fee hide unnoticed — buried under peak fulfilment surcharges, storage spikes, and holiday ad spend.</p>
<p>So treat this as an ASIN-level profitability review, not a notification to archive. Fee classification now belongs in your margin monitoring, alongside the fee schedules you already watch. Thirty minutes in Fee Preview this week — flag the changes, model the margin, dispute the wrong ones — is the difference between catching it and reading about it on your January settlement report.</p>
HTML;
    }
}
