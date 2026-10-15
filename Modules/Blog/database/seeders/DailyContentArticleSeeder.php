<?php

namespace Modules\Blog\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\Blog\app\Models\Blog;
use Modules\Blog\app\Models\BlogCategory;
use Modules\Blog\app\Models\BlogSeo;
use Modules\Blog\app\Models\BlogTranslation;

/**
 * Daily Content Machine - first run (2026-09-25).
 *
 * Seeds one editorial article into the Blog module the same way the admin
 * panel stores it (blogs + blog_translations + blog_seos rows), so it renders
 * through the existing /blog/{slug} frontend route and theme views.
 * Idempotent: safe to re-run, it updates the article by slug.
 */
class DailyContentArticleSeeder extends Seeder
{
    protected const SLUG = 'ai-shopping-agents-are-your-new-customers';

    protected const COVER_IMAGE = 'ai-shopping-agent-cover.jpg';

    protected const TITLE = "Your Next Customer Isn't a Person — It's an AI: How Marketplace Sellers Win the Agentic Commerce Era";

    protected const META_DESCRIPTION = "AI shopping agents like ChatGPT, Gemini, and Amazon's Alexa for Shopping are reshaping how people buy online in 2026. Here is what marketplace sellers must do to stay visible, trusted, and recommended.";

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
<p>A shopper in Dubai opens her phone, but she doesn't open a shopping app. She types into a chat window: "Find me a decent 1.5-ton split AC for a studio in JVC. Budget AED 2,000, installation included, from a seller who actually delivers this week." Thirty seconds later she gets a shortlist — real products, real prices, real delivery dates — and she buys without ever seeing a product listing page.</p>
<p>No clicks. No filters. No browsing. That's the change 2026 has brought to e-commerce, and it's happening faster than most sellers realize. The customer didn't visit your storefront. Her AI agent did.</p>
<h2>Shopping without the shopper</h2>
<p>This is agentic commerce: AI assistants that research, compare, and increasingly complete purchases on a shopper's behalf. It's not a research project anymore. OpenAI launched shopping research in ChatGPT back in November 2025, and built the Agentic Commerce Protocol so merchants can feed their catalogs straight into the assistant — with Target, Sephora, Nordstrom, Best Buy, and Wayfair among the retailers already integrated. Amazon has gone further: its Rufus shopping assistant helped more than 300 million customers research, compare, and buy during 2025, and this past May it folded Rufus into the wider Alexa experience as "Alexa for Shopping," complete with price history, dynamic comparisons, and automated purchasing.</p>
<p>The infrastructure is arriving from every direction at once. Stripe and OpenAI shipped the Agentic Commerce Protocol in late 2025, Google followed with its Universal Commerce Protocol in January 2026, and Visa and Mastercard have rolled out agent-specific transaction standards this year. In September 2026, Amazon even launched "workflows" — an agentic AI service that runs continuously for third-party sellers, watching prices, ratings, and inventory around the clock. Its Seller Assistant already serves more than 230,000 monthly users.</p>
<h2>The numbers that should make every seller sit up</h2>
<p>This isn't a trend slide. It's a channel with numbers attached. Adobe Analytics reported that AI-referred retail traffic in the US grew 393% year over year in Q1 2026 — and that it converts about 42% better than traditional organic traffic. Shopify says AI-driven traffic to its merchants' stores grew eightfold in the same quarter, with orders from that traffic up nearly thirteenfold. Salesforce counted $67 billion in sales influenced by AI agents during Cyber Week 2025 — roughly one in five purchases across the retailers it tracks.</p>
<p>And the catch, per Adobe, is this: the average US product page is only 66% machine-readable. A third of the content shoppers use to decide simply can't be parsed by an agent. In other words, there is real demand arriving through a new channel, and most sellers are accidentally invisible to it.</p>
<h2>What changes when the customer is software</h2>
<p>For decades, sellers optimized for human eyeballs: beautiful hero shots, clever copy, a layout that guides the eye to the buy button. An AI agent doesn't have eyeballs. It reads structured data — prices, specs, stock levels, shipping times, return policies, review summaries — and makes a decision on behalf of someone who trusts it.</p>
<p>That changes the rules. A listing with a gorgeous photo but missing weight, dimensions, or delivery estimate can be skipped by an agent that filters on shipping time. A product with thin, duplicated descriptions looks identical to ten competitors, so the agent picks on price and reviews. Reviews, by the way, become more important, not less: they're the structured trust signal an agent can actually weigh.</p>
<p>It also changes where competition happens. Gartner projects that 90% of B2B purchases — over $15 trillion — will flow through AI agent exchanges by 2028, a figure cited in an August 2026 Harvard Business Review feature. Consumer commerce is moving in the same direction. The storefront is becoming an API, and the pitch is a data feed.</p>
<h2>Your 2026 seller checklist</h2>
<p>None of this requires a rebuild. It's mostly discipline:</p>
<p><strong>1. Make every listing machine-readable.</strong> Complete, accurate structured data — real specs, real stock counts, real prices, clear return and shipping terms. If your catalog can't be parsed, you can't be recommended.</p>
<p><strong>2. Keep feeds fresh.</strong> Agents comparison-shop on delivery dates and stock. A listing that claims next-day delivery you can't fulfill doesn't just lose one sale — it teaches the agent not to trust your store.</p>
<p><strong>3. Earn and display real reviews.</strong> Honest ratings are the closest thing to a universal trust metric agents have. Incentivize reviews after delivery; never fake them — fabricated feedback poisons the one signal that matters most.</p>
<p><strong>4. Write for questions, not keywords.</strong> Agents answer queries like "which air fryer under AED 500 has the best warranty." FAQ-style detail on your listings — warranty terms, what's in the box, compatibility — is the content agents lift.</p>
<p><strong>5. Fulfill reliably.</strong> Agents track outcomes. Late shipments and stockouts get logged by the same software that chose you, and you may never be picked again.</p>
<h2>Why marketplaces have a head start</h2>
<p>Here's the good news for sellers on multi-vendor marketplaces: the platform does the heavy lifting. A marketplace aggregates thousands of catalogs into one structured, searchable, comparison-friendly feed — exactly the format agents consume. Rich product data, standardized specs, review aggregation, payment trust, and logistics tracking are platform features, not seller homework. The sellers who win will be the ones who feed the platform the cleanest data: complete listings, honest inventory, fast fulfillment.</p>
<p>The timing is hard to ignore in this region. A Dubai Chamber of Commerce study based on Euromonitor data projects UAE e-commerce will reach $9.2 billion in 2026, up from $4.8 billion in 2021, with online sales taking a 12.6% share of total retail. Mobile already drives 44% of transactions, and shoppers here favor local platforms — domestic websites accounted for 73% of online sales. That local preference is a gift for marketplace sellers: the agent serving a Dubai shopper will prefer a seller who delivers to Dubai this week over a cheaper one that ships from overseas.</p>
<h2>The storefront isn't dead. It just has a new regular.</h2>
<p>Agentic commerce won't kill the browse-and-buy experience — people still enjoy shopping. But a growing share of purchases will start with a question typed into a chat window, answered by software that never sees your banner ads. The sellers who thrive will be the ones their agents can read, trust, and recommend. Make your catalog machine-readable, keep your promises, and let the robots do the browsing. Your next customer may not be a person at all.</p>
HTML;
    }
}
