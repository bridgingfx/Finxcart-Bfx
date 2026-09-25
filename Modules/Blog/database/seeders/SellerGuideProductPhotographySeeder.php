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
 * Daily Content Machine - 2026-09-25 (article 2 of 3).
 *
 * Seeds one original editorial article into the Blog module the same way the
 * admin panel stores it (blogs + blog_translations + blog_seos rows), so it
 * renders through the existing /blog/{slug} frontend route and theme views.
 * Idempotent: safe to re-run, it updates the article by slug.
 */
class SellerGuideProductPhotographySeeder extends Seeder
{
    protected const SLUG = 'how-to-photograph-products-that-sell';

    protected const COVER_IMAGE = 'product-photography-cover.jpg';

    protected const TITLE = 'How to Photograph Products That Sell: A Practical Guide for New Online Sellers';

    protected const META_DESCRIPTION = 'You do not need a studio or an expensive camera to take product photos that win the click. A phone, a window, and a white background are enough — here is the exact method new marketplace sellers use.';

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
<p>On a marketplace, your product photo is your storefront, your salesperson, and your packaging all at once. Shoppers decide in a couple of seconds whether to tap your listing or scroll past it — and that decision is made on the thumbnail, long before anyone reads your description. The good news: you don't need a studio or an expensive camera. A smartphone, a window, and a white background will beat most of your competitors if you use them properly.</p>
<h2>The setup: a window, a white sweep, and your phone</h2>
<p>Natural window light is the single best lighting tool a new seller owns. Place a table next to a large window (not in direct sun — bright shade or a north-facing window gives soft, even light), and bend a large sheet of white paper or card into a gentle curve behind your product: this is called a sweep, and it removes the horizon line so the product floats on clean white. Put your phone on a small tripod, a stack of books, or anything stable — most blur in product photos comes from hand shake, not bad cameras.</p>
<p>Shoot with the window to one side of the product, not behind it. If the shadow side looks too dark, prop a second white sheet or a piece of foam board on the opposite side to bounce light back. That one reflector costs almost nothing and is the difference between flat and professional.</p>
<h2>Phone camera technique that actually matters</h2>
<p>Four habits separate good product shots from bad ones: <strong>clean the lens</strong> (a smudged lens is the most common cause of hazy photos), <strong>tap to focus and lock exposure</strong> on the product so the camera doesn't keep re-adjusting, <strong>use the grid</strong> to keep the product straight and centered, and <strong>never pinch-zoom</strong> — walk closer instead, or crop later. Shoot at the product's eye level rather than pointing down at it; a straight-on angle looks far more trustworthy than a steep downward shot.</p>
<p>Turn off the flash. Always. Phone flash creates harsh shadows and hot spots that make products look cheap. If the room is too dark, move closer to the window rather than switching the flash on.</p>
<h2>The six shots every listing should have</h2>
<p>One photo is never enough. Shoppers can't pick your product up, so your photos have to do the touching for them. Cover these six:</p>
<p><strong>1. The hero shot.</strong> Product centered on a clean white or light background, straight-on, filling the frame. This is your thumbnail — it must read clearly at tiny sizes.</p>
<p><strong>2. The angle shot.</strong> A three-quarter view showing depth. Flat frontal shots hide a product's real shape; an angle tells the truth about size and proportions.</p>
<p><strong>3. The detail shot.</strong> Close-ups of texture, stitching, material, buttons, or whatever the quality of the product lives in. This is where you justify your price.</p>
<p><strong>4. The scale shot.</strong> The product next to a familiar object — a hand, a coin, a phone. "Compact" and "large" mean nothing without a reference point, and wrong-size surprises are a top cause of returns.</p>
<p><strong>5. The in-use shot.</strong> The product doing its job: the bag on a shoulder, the lamp switched on in a room, the serum on a bathroom shelf. This is the shot that makes someone imagine owning it.</p>
<p><strong>6. What's in the box.</strong> Everything the buyer receives, laid out neatly. It kills "I thought it came with X" complaints before they happen.</p>
<h2>Mistakes that quietly kill sales</h2>
<p><strong>Mixed lighting.</strong> Window light plus a warm ceiling bulb gives photos an ugly yellow-blue cast that makes colors look wrong. Turn room lights off and shoot with one light source.</p>
<p><strong>Cluttered backgrounds.</strong> Your kitchen counter, a patterned bedsheet, your hand holding the product — every extra element distracts and cheapens the listing. Keep the background boring on purpose.</p>
<p><strong>Heavy filters.</strong> If your photo's colors don't match the real product, you're manufacturing returns and bad reviews. Edit for accuracy, not beauty: straighten, crop, and fix white balance — nothing more.</p>
<p><strong>Hiding flaws.</strong> If a handmade item has natural variation, or a product has a quirk, show it. Buyers who discover surprises after delivery don't come back; buyers who saw the truth in the photos leave five-star reviews.</p>
<h2>Keep your catalog consistent</h2>
<p>Shoot every product in the same style: same background, same light direction, same framing. A store where every thumbnail looks like it came from the same place feels like a real brand; a store where every listing looks different feels like a random pile of goods. Consistency also makes your store page look organized, which matters when a shopper opens your vendor profile to decide whether they trust you.</p>
<h2>The 10-minute checklist before you publish</h2>
<p>Run every listing through this: lens clean, one light source, white sweep, phone stable, six shots covered, colors true to life, no filters, consistent style with the rest of the catalog, and at least one photo showing real scale. If a photo fails any of these, reshoot — ten minutes now saves you returns, complaints, and lost trust later.</p>
<p>You will get faster at this. The first product takes an hour; by the tenth, you'll shoot a full set in fifteen minutes. And every one of those photos keeps working for you around the clock, on every screen, for every shopper who will never visit a physical store. In online selling, the camera is the shop floor — treat it that way.</p>
HTML;
    }
}
