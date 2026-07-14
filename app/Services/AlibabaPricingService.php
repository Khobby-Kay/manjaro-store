<?php

namespace App\Services;

use App\Models\AlibabaProduct;

class AlibabaPricingService
{
    public function calculateRetailPrice($originalPrice, $markupPercentage)
    {
        $markupMultiplier = 1 + ($markupPercentage / 100);
        return round($originalPrice * $markupMultiplier, 2);
    }

    public function calculateProfitMargin(AlibabaProduct $product)
    {
        if ($product->retail_price > 0) {
            return round((($product->retail_price - $product->original_price) / $product->retail_price) * 100, 2);
        }
        return 0;
    }

    public function updatePricing(AlibabaProduct $product, $newMarkupPercentage)
    {
        $newRetailPrice = $this->calculateRetailPrice($product->original_price, $newMarkupPercentage);
        
        $product->update([
            'retail_price' => $newRetailPrice
        ]);

        return $product;
    }

    public function applyBulkPricingUpdate($productIds, $markupPercentage)
    {
        $products = AlibabaProduct::whereIn('id', $productIds)->get();
        
        foreach ($products as $product) {
            $this->updatePricing($product, $markupPercentage);
        }

        return count($products);
    }
}