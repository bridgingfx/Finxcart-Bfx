<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\CacheManagerTrait;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use CacheManagerTrait;

    public function getBannerList(Request $request): JsonResponse
    {
        $banners = $this->cacheBannerTable();
        $productIds = collect($banners)->where('resource_type', 'product')->pluck('resource_id')->unique()->values()->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $bannerData = [];
        foreach ($banners as $banner) {
            if ($banner['resource_type'] == 'product' && $products->has($banner['resource_id'])) {
                $banner['product'] = Helpers::product_data_formatting($products[$banner['resource_id']]);
            }
            $bannerData[] = $banner;
        }

        return response()->json($bannerData, 200);

    }
}
