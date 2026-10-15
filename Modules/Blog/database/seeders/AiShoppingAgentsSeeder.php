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
 * Daily Content Machine - 2026-10-08.
 *
 * Seeds one original editorial article into the Blog module the same way the
 * admin panel stores it (blogs + blog_translations + blog_seos rows), so it
 * renders through the existing /blog/{slug} frontend route and theme views.
 * Idempotent: safe to re-run, it updates the article by slug.
 */
class AiShoppingAgentsSeeder extends Seeder
{
    protected const SLUG = 'ai-shopping-agents-marketplace-sellers-2026';

    protected const COVER_IMAGE = 'ai-shopping-agents-cover.jpg';

    protected const TITLE = 'AI Shopping Agents Are Buying: Optimise Your Listings Now';

    protected const META_DESCRIPTION = "Shopping assistants compare and buy products for customers. How to structure titles, highlights, and attributes so AI agents find and recommend listings.";

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
<p>There is a new kind of buyer on the marketplaces, and it never scrolls. It reads your listing the way a spreadsheet reads a cell: attributes in, decision out. When a shopper asks their AI assistant for "a waterproof Bluetooth speaker under $50 with good battery life", the assistant does not browse. It compares structured data across listings, picks two or three, and increasingly completes the purchase inside the chat. This is agentic commerce — and it changes what a good product listing is.</p>
<p>The shift is no longer theoretical. In September 2026 Amazon rolled out agentic AI "workflows" for third-party sellers, running continuously to watch ratings, pricing, and inventory and act on seller prompts (Reuters, 23 September 2026). Shopify launched Agentic Storefronts in March 2026, putting merchant catalogues directly inside assistants like ChatGPT and Copilot, and reported that orders arriving from AI chats had grown fifteenfold since January 2025. Google introduced its Universal Commerce Protocol at NRF in January 2026 with more than sixty launch partners, standardising how agents talk to merchant catalogues. One industry tracker put AI-attributed orders at 20% of global online orders during the 2025 holiday season, worth $262 billion. Whether that exact number holds or not, the direction is unmistakable: a growing share of buying decisions will be made by software reading your listing.</p>
<h2>The reader of your listing has changed</h2>
<p>For twenty years, listing optimisation meant persuading a human: the hook in the title, the lifestyle image, the bullet that lands the emotional benefit. An agent does not get persuaded. It parses. The title, the bullets, the item specifics, the price, the delivery promise, the return terms, and the review summary are the inputs; the recommendation is the output. A listing that reads beautifully to a person but leaves the material, dimensions, and compatibility unstated is, to an agent, an incomplete record — and incomplete records do not get recommended.</p>
<p>Amazon's July 2026 title change made this explicit. Titles were compressed to 75 characters, and a new 125-character Item Highlights field was added for the handful of facts that matter most. That was not a formatting tweak; it was the marketplace rebuilding its shelf so machines can parse it cleanly, as Teikametrics noted in its analysis. The short title is the machine-readable label. The highlights are the facts the agent must not miss. Keyword stuffing in the title no longer buys visibility; clear, factual fields do.</p>
<h2>What agents actually rank on</h2>
<p>The ranking inputs sound familiar, because they are the fields you already control: complete and accurate product titles, full item specifics (brand, model, size, material, condition), photos that match the written details, pricing, shipping cost and dispatch times, return terms, and seller ratings. Trade-press coverage of the resale market made the same point from the secondhand side: agents rank listings by accuracy, pricing, shipping speed, and seller reliability — and they cannot infer what you never wrote down.</p>
<p>That last point deserves emphasis. A human shopper fills gaps: they look at the photo, skim the reviews, and guess the rest. An agent will not invent your best feature. If the compatibility list is not in a field it reads, your product is not compatible. If the battery life is only implied by a lifestyle image, it does not exist. The listings that win in agentic commerce are the boring ones — complete, consistent, and literal.</p>
<h2>The playbook: make your catalogue agent-readable</h2>
<p><strong>1. Rewrite titles as facts, not pitches.</strong> You have 75 characters on Amazon now. Spend them on the three facts that decide the purchase: what it is, the defining spec, the variant. "Stainless Steel Insulated Bottle 1L — Cold 24h" beats "Amazing Premium Quality Water Bottle Best Gift!!!". Write the title an agent would quote back in an answer, because that is literally what will happen.</p>
<p><strong>2. Fill every attribute field, especially the boring ones.</strong> Material, dimensions, weight, capacity, compatibility, colour, count, age range — the fields sellers skip are the fields agents filter on. A shopper tells the assistant "under 500 grams" or "fits iPhone 15"; the agent filters by the attribute table, not by your description. Teikametrics reported a client catalogue where a single content upgrade — titles, bullets, attributes — moved conversion from 4.3% to 5.7%, lifted ROAS from 5.15 to 6.33, and raised total sales 70.5%. Content is the lever; the agent era just makes it the biggest one.</p>
<p><strong>3. Use Item Highlights for the five facts that close the sale.</strong> The 125-character highlights field is your shortlist pitch to the machine: the headline benefit, the key spec, the compatibility, the warranty, the differentiator. One fact each. No adjectives without numbers.</p>
<p><strong>4. Make photos agree with the text.</strong> Agents cross-check: does the main image show what the title claims? Mismatched photos — wrong colour variant, a bundle shown but a single unit listed — read as unreliable data. Refresh any listing where the photos lag behind the current product, and make sure variant images match their variants exactly.</p>
<p><strong>5. Treat delivery and returns as ranking inputs.</strong> Dispatch time, delivery promise, shipping cost, and return terms are not just conversion factors for humans; they are ranking factors for agents. Consistent, honest fulfilment data — valid tracking, realistic dispatch times — is what earns the recommendation. The multichannel tools are catching up: ShipStation's October 2026 "Ship with Walmart" launch brought Walmart-negotiated rates and order protection into sellers' existing workflows, and Amazon has been folding eBay, Shopify, TikTok Shop, and Walmart orders into Seller Central, as PPC Land reported — so consistent fulfilment data now flows from one place.</p>
<p><strong>6. Harvest reviews for the questions agents get asked.</strong> Review content increasingly feeds the summaries agents show shoppers. Read your reviews for the actual questions — "does it fit?", "how long does the battery last?", "is it really waterproof?" — and make sure the answers live in your bullets and attributes, not just in the review section. If fifty reviewers praise one feature, that feature belongs in Item Highlights.</p>
<h2>Use the agents on your side of the desk</h2>
<p>Agentic commerce is not only about being read by agents; it is about running them. Amazon's new seller workflows are free and optional: set one to alert you when a product's rating drops, another to monitor pricing on your top SKUs, another to flag inventory gaps before a stockout costs you the buy box. The Seller Assistant stores your seller profile for tailored recommendations on pricing, promotions, and inventory, and it plugs into tools sellers already use. The sellers who benefit first will be the ones who treat these agents like junior staff: give them narrow jobs, check their work, and expand what works.</p>
<p>ThredUp's 2026 resale research found 63% of Gen Z are already comfortable with agent-driven buying, and nearly half use visual search and price comparison features. Your next generation of customers will not just tolerate the agent — they will expect it. The sellers whose catalogues are already structured, complete, and consistent will be the ones the agent recommends. The rest will be invisible to a buyer who never saw a search results page.</p>
<h2>Start with your top twenty</h2>
<p>You do not need to rewrite a thousand listings this week. Take your top twenty SKUs by revenue and run each through one question: could an agent recommend this product from the structured fields alone, without ever seeing the photos or reviews? If the answer is no, you now know exactly what is missing — and fixing it is the highest-return content work you can do this quarter. The marketplace shelf is being rebuilt for machines. Make sure your products are on it.</p>
HTML;
    }
}
