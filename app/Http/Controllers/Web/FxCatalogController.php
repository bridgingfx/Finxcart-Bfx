<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Utils\FxListing;
use App\Utils\ProductManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Additive JSON endpoints for the fx catalog/search/compare experience.
 * None of these replace existing routes; all are GET and public.
 */
class FxCatalogController extends Controller
{
    /**
     * Grouped autocomplete: listings, categories, providers.
     * GET /fx/search-suggest?q=...
     */
    public function suggest(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['products' => [], 'categories' => [], 'providers' => []]);
        }

        // Listings: reuse the verified translated search pipeline (name/tags/authors/publishing houses),
        // falling back to a direct name match when there are no translation rows.
        $productModels = [];
        try {
            $search = ProductManager::translated_product_search(base64_encode($query), 'all', 6, 1);
            $productModels = $search['products'] ?? [];
        } catch (\Throwable) {
            // fall through to direct match below
        }
        if (empty($productModels)) {
            $productModels = Product::active()->where('name', 'like', "%{$query}%")->take(6)->get();
        }
        $products = [];
        foreach ($productModels as $product) {
            $type = FxListing::type($product);
            [$suggestPrice] = FxListing::priceStrings($product);
            $products[] = [
                'id' => $product->id,
                'name' => $product->name,
                'url' => route('product', $product->slug),
                'thumbnail' => getStorageImages(path: $product->thumbnail_full_url, type: 'product'),
                'price' => $suggestPrice,
                'tag' => $type['tag'],
            ];
        }

        // Categories.
        $categories = Category::query()
            ->withCount('products')
            ->where('name', 'like', "%{$query}%")
            ->take(6)->get()
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'url' => route('products', ['data_from' => 'category', 'category_id' => $c->id]),
                'count' => (int) $c->products_count,
            ]);

        // Providers (shops).
        $providers = Shop::where('name', 'like', "%{$query}%")
            ->take(6)->get()
            ->map(fn (Shop $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'url' => route('shopView', $s->id),
                'image' => getStorageImages(path: $s->image_full_url, type: 'shop'),
            ]);

        return response()->json([
            'products' => $products,
            'categories' => $categories,
            'providers' => $providers,
        ]);
    }

    /**
     * Quick-preview drawer content for one product.
     * GET /fx/product/{id}/preview
     */
    public function quickPreview(int $id): JsonResponse
    {
        $product = Product::active()->find($id);
        if (! $product) {
            return response()->json(['ok' => false], 404);
        }

        $html = view('web-views.partials._fx-quick-preview-content', [
            'product' => $product,
        ])->render();

        return response()->json(['ok' => true, 'html' => $html]);
    }

    /**
     * Compare table for up to 4 products of the SAME listing type.
     * GET /fx/compare-table?ids[]=1&ids[]=2
     */
    public function compareTable(Request $request): JsonResponse
    {
        $ids = array_values(array_filter(array_map('intval', (array) $request->query('ids', []))));
        if (empty($ids)) {
            return response()->json(['ok' => false, 'message' => 'No products selected.'], 422);
        }

        $products = Product::active()->whereIn('id', $ids)->get()->keyBy('id');

        // Enforce: same canonical transaction type (App\Enums\TransactionType —
        // the single source of truth, override respected so it matches what
        // FxListing::type() shows; fulfillment variants such as the two
        // download promises stay comparable), max 4, in requested order.
        $ordered = [];
        $firstType = null;
        foreach ($ids as $id) {
            $product = $products->get($id);
            if (! $product) {
                continue;
            }
            $type = \App\Enums\TransactionType::forProduct($product)->value;
            if ($firstType === null) {
                $firstType = $type;
            }
            if ($type !== $firstType) {
                continue;
            }
            $ordered[] = $product;
            if (count($ordered) >= 4) {
                break;
            }
        }

        if (empty($ordered)) {
            return response()->json(['ok' => false, 'message' => 'No comparable products found.'], 422);
        }

        $html = view('web-views.partials._fx-compare-table', [
            'products' => collect($ordered),
        ])->render();

        return response()->json([
            'ok' => true,
            'html' => $html,
            'ids' => array_map(fn ($p) => $p->id, $ordered),
        ]);
    }
}
