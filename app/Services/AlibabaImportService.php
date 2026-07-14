<?php

namespace App\Services;

use App\Models\AlibabaProduct;
use App\Models\AlibabaSupplier;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\ProductStock;
use App\Models\Upload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlibabaImportService
{
    protected $pricingService;

    public function __construct(AlibabaPricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    public function importSingleProduct($productData, $supplierId, $markupPercentage)
    {
        DB::beginTransaction();

        try {
            $retailPrice = $this->pricingService->calculateRetailPrice(
                $productData['original_price'],
                $markupPercentage
            );

            $alibabaProduct = AlibabaProduct::create([
                'alibaba_product_id' => $productData['alibaba_product_id'],
                'supplier_id' => $supplierId,
                'title' => $productData['title'],
                'description' => $productData['description'],
                'original_price' => $productData['original_price'],
                'retail_price' => $retailPrice,
                'min_order_quantity' => $productData['min_order_quantity'],
                'stock_quantity' => $productData['stock_quantity'],
                'images' => $productData['images'],
                'specifications' => $productData['specifications'],
                'shipping_info' => $productData['shipping_info'],
                'status' => 'imported'
            ]);

            $uploadedImages = $this->processImages($productData['images'], $alibabaProduct->id);

            if (get_setting('alibaba_auto_approve', false)) {
                $localProduct = $this->convertToLocalProduct($alibabaProduct);
                $alibabaProduct->update(['local_product_id' => $localProduct->id]);
            }

            DB::commit();
            return $alibabaProduct;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function importBulkProducts($productsData, $supplierId, $settings)
    {
        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($productsData as $productData) {
            try {
                $this->importSingleProduct($productData, $supplierId, $settings['markup_percentage']);
                $results['successful']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'product_id' => $productData['alibaba_product_id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    public function convertToLocalProduct(AlibabaProduct $alibabaProduct)
    {
        DB::beginTransaction();

        try {
            $product = Product::create([
                'name' => $alibabaProduct->title,
                'description' => $alibabaProduct->description,
                'unit_price' => $alibabaProduct->retail_price,
                'unit' => 'piece',
                'min_qty' => $alibabaProduct->min_order_quantity,
                'tags' => 'alibaba,imported',
                'photos' => json_encode($alibabaProduct->images),
                'thumbnail_img' => $alibabaProduct->images[0] ?? null,
                'meta_title' => $alibabaProduct->title,
                'meta_description' => $alibabaProduct->description,
                'meta_img' => $alibabaProduct->images[0] ?? null,
                'slug' => \Str::slug($alibabaProduct->title),
                'published' => 1,
                'featured' => 0,
                'seller_featured' => 0,
                'cash_on_delivery' => 0,
                'approved' => 1,
                'trenching_point' => 0,
                'current_stock' => $alibabaProduct->stock_quantity,
                'added_by' => 'admin',
                'user_id' => 1,
                'category_id' => 1,
                'brand_id' => null,
                'video_provider' => null,
                'video_link' => null
            ]);

            DB::commit();
            return $product;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function processImages($images, $productId)
    {
        $uploadedImages = [];
        
        foreach ($images as $imageUrl) {
            try {
                $imageContent = file_get_contents($imageUrl);
                $filename = 'alibaba_' . $productId . '_' . time() . '.jpg';
                $path = 'uploads/alibaba/' . $filename;
                
                Storage::put('public/' . $path, $imageContent);
                
                $uploadedImages[] = $path;
            } catch (\Exception $e) {
                \Log::error('Failed to download image: ' . $e->getMessage());
            }
        }
        
        return $uploadedImages;
    }
}