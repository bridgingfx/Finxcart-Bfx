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
 * Daily Content Machine - 2026-09-25 (seller guide 3 of 3).
 *
 * Seeds one original editorial article into the Blog module the same way the
 * admin panel stores it (blogs + blog_translations + blog_seos rows), so it
 * renders through the existing /blog/{slug} frontend route and theme views.
 * Idempotent: safe to re-run, it updates the article by slug.
 */
class SellerGuideCustomerTrustSeeder extends Seeder
{
    protected const SLUG = 'building-customer-trust-new-online-store-uae';

    protected const COVER_IMAGE = 'customer-trust-cover.jpg';

    protected const TITLE = 'Building Customer Trust as a New Online Store: A Practical Guide for UAE Sellers';

    protected const META_DESCRIPTION = 'New online stores in the UAE lose sales to one thing: doubt. Learn the practical steps — honest listings, clear policies, fast replies — that turn first-time visitors into confident buyers.';

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
<p>Every new online store faces the same invisible competitor: doubt. A shopper lands on your store, likes a product, checks the price — and then hesitates. "Is this store real? Will my order actually arrive? What if something goes wrong?" Established brands answer those questions with years of reputation. You don't have that yet, so you have to answer them deliberately, on every page and in every interaction. Trust isn't a marketing trick — it's the sum of a hundred small promises you make and keep.</p>
<h2>Finish your store profile before your first sale</h2>
<p>An empty store profile is the fastest way to lose a visitor. Before you chase traffic, make your store look like a real business: a clear store name, a recognizable logo, and an "About" section that says who you are and what you sell in plain language. Add working contact details — at minimum an email address and a phone or WhatsApp number a real person answers. Many UAE shoppers will message a seller with a question before ordering; if there's no way to reach you, they buy from someone else.</p>
<p>Treat your store page like a physical shopfront. A shop with no sign, no name, and the shutters half-closed doesn't get walk-ins — and a bare store profile doesn't get orders.</p>
<h2>Write listings that tell the whole truth</h2>
<p>Nothing builds trust like accuracy, and nothing destroys it like a listing that overpromises. Write product titles that say exactly what the item is. List the real specifications — size, material, what's included, what's not. If you're selling a brand, name it correctly; if it's unbranded, say so. Never copy another seller's description word for word, and never paste manufacturer marketing claims you can't verify.</p>
<p>Be especially honest about condition and limitations. If a handmade item varies slightly from piece to piece, say that. If a gadget needs batteries that aren't included, say that too. Buyers who know exactly what they're getting rarely complain; buyers who feel misled rarely return — and they tell their friends.</p>
<h2>Show the full price, early</h2>
<p>Surprise charges at checkout are one of the quickest ways to kill trust. If there are shipping fees, taxes, or payment charges, make them visible before the customer reaches the payment step. Shoppers compare the total, not the headline price, and a store that reveals costs late looks like it was hiding them.</p>
<p>The UAE's consumer protection rules expect clear, honest pricing and fair return terms — check the current requirements and build them into your store rather than discovering them after a complaint. Compliance isn't just legal cover; it's a trust signal you can show on your site.</p>
<h2>Publish a shipping and returns policy you can actually keep</h2>
<p>New sellers often copy a generous returns policy from a big brand and then can't honor it. Don't. Write policies around what you can genuinely do: realistic delivery times for your courier, a returns window you can afford, and a clear process for damaged or wrong items. Then display that policy where buyers can find it without hunting — a link in your store header and a summary on each product page.</p>
<p>The secret most new sellers miss: the policy matters less than keeping it. A modest promise kept beats a generous promise broken, every time. When something does go wrong — and it will — handle it fast and fairly. One well-handled problem creates more loyalty than ten smooth orders.</p>
<h2>Answer messages like your business depends on it</h2>
<p>Because it does. A shopper who messages "is this available in blue?" is one step from buying. Slow, vague, or copy-pasted replies push them to a competitor; a quick, specific, human answer closes the sale. Set a personal rule — reply within a few hours during the day, sooner if you can — and actually answer the question asked instead of sending a generic template.</p>
<p>Your tone matters too. Be polite, be direct, and if you don't know something, say you'll check and get back to them — then do it. People remember how a seller made them feel long after they forget the product details.</p>
<h2>Earn reviews the honest way</h2>
<p>Reviews are the closest thing a new store has to reputation, but there is exactly one legitimate way to get them: deliver a good experience and ask. After an order arrives, follow up with a short message thanking the buyer and inviting honest feedback. Most happy customers simply forget to review unless asked.</p>
<p>What you must never do: post fake reviews, pay for positive ratings, or write your own testimonials. Fake reviews are dishonest, they violate marketplace rules, and experienced shoppers can smell them — a brand-new store with fifty glowing five-star reviews looks worse than one with six genuine ones. A small number of real reviews beats a large number of fake ones in every way that matters.</p>
<h2>Offer payment options shoppers already trust</h2>
<p>In the UAE, shoppers are comfortable with cards, but cash on delivery is still a familiar option many buyers prefer for a first order from an unknown store — it removes their risk entirely. Where the platform supports it, offering familiar payment methods lowers the barrier for that crucial first purchase. Whatever you accept, make the checkout process simple and secure: no unnecessary steps, no confusing redirects, and clear confirmation once the order is placed.</p>
<h2>Follow up after the sale</h2>
<p>The order doesn't end at delivery. A quick message confirming the order shipped, another when it's out for delivery, and a check-in after it arrives costs you minutes and buys you something advertising can't: a customer who feels looked after. If a buyer reports a problem, fix it before defending yourself — apologize for the experience, offer the return or replacement your policy promises, and sort out whose fault it was later.</p>
<p>Repeat customers are the real prize. The first sale from a stranger is expensive in effort; the second sale to the same person is nearly free. Every trust-building habit above compounds: honest listings earn fair reviews, fair reviews bring new buyers, and good service turns new buyers into regulars. That's how a store with no reputation gets one — one kept promise at a time.</p>
HTML;
    }
}
