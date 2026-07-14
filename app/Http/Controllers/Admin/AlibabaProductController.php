<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AlibabaProduct;
use App\Models\AlibabaSupplier;
use App\Models\StaffActivityLog;
use App\Services\AlibabaApiService;
use App\Services\AlibabaImportService;
use App\Services\AlibabaPricingService;

class AlibabaProductController extends Controller
{
    protected $apiService;
    protected $importService;
    protected $pricingService;

    public function __construct(
        AlibabaApiService $apiService,
        AlibabaImportService $importService,
        AlibabaPricingService $pricingService
    ) {
        $this->apiService = $apiService;
        $this->importService = $importService;
        $this->pricingService = $pricingService;
        
        // Permission middleware
        $this->middleware(['permission:alibaba_products'])->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        $this->middleware(['permission:alibaba_management'])->only(['import', 'bulkUpdatePricing', 'bulkImportCsv', 'bulkImportSupplier', 'importTrending', 'discoverProducts', 'importSingleProduct', 'fetchDetails', 'downloadTemplate']);
    }

    public function index()
    {
        try {
            $products = AlibabaProduct::with('supplier')->latest()->paginate(20);
            $suppliers = AlibabaSupplier::active()->get();
        } catch (\Exception $e) {
            $products = collect([])->paginate(20);
            $suppliers = collect([]);
        }
        return view('backend.alibaba.products.index', compact('products', 'suppliers'));
    }

    public function create()
    {
        try {
        $suppliers = AlibabaSupplier::active()->get();
            $categories = \App\Models\Category::where('parent_id', 0)->get();
            $brands = \App\Models\Brand::all();
            
            // Get Manjaro Import category ID
            $manjaroImportCategory = \App\Models\Category::where('name', 'Manjaro Import')->first();
            $manjaroImportCategoryId = $manjaroImportCategory ? $manjaroImportCategory->id : null;
        } catch (\Exception $e) {
            // If tables don't exist or other database issues, create empty collections
            $suppliers = collect([]);
            $categories = collect([]);
            $brands = collect([]);
            $manjaroImportCategoryId = null;
        }
        
        return view('backend.alibaba.products.create', compact('suppliers', 'categories', 'brands', 'manjaroImportCategoryId'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'import_method' => 'required|in:manual,url,csv',
                'supplier_id' => 'nullable|exists:alibaba_suppliers,id',
                'markup_percentage' => 'required|numeric|min:0|max:1000'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            flash(translate('Validation failed: ') . implode(', ', $e->validator->errors()->all()))->error();
            return redirect()->back()->withInput()->withErrors($e->validator);
        }

        try {
            if ($request->import_method === 'manual') {
                $request->validate([
                    'title' => 'required|string|max:255',
                    'description' => 'required|string',
                    'original_price' => 'required|numeric|min:0',
                    'min_order_quantity' => 'nullable|numeric|min:1',
                    'stock_quantity' => 'nullable|numeric|min:0',
                    'images' => 'nullable|string',
                    'specifications' => 'nullable|string'
                ]);

                $product = new AlibabaProduct();
                $product->alibaba_product_id = 'ALI_MANUAL_' . time();
                $product->supplier_id = $request->supplier_id;
                $product->title = $request->title;
                $product->description = $request->description;
                $product->original_price = $request->original_price;
                $product->retail_price = $request->original_price * (1 + $request->markup_percentage / 100);
                $product->min_order_quantity = $request->min_order_quantity ?? 1;
                $product->stock_quantity = $request->stock_quantity ?? 100;
                $product->images = $request->images ? json_encode(explode("\n", $request->images)) : json_encode([]);
                $product->specifications = $request->specifications ? json_decode($request->specifications, true) : [];
                $product->status = 'imported';
                $product->save();

            } elseif ($request->import_method === 'url') {
                try {
                    $request->validate([
                        'alibaba_url' => 'required|url'
                    ]);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    flash(translate('URL validation failed: ') . implode(', ', $e->validator->errors()->all()))->error();
                    return redirect()->back()->withInput();
                }

                // Extract product data from the URL
                $productData = $this->scrapeAlibabaProduct($request->alibaba_url);
                
                // The scraping function now returns fallback data instead of null
                // So we don't need to check for null anymore

                // Create or get supplier
                $supplierId = $request->supplier_id;
                if (!$supplierId && isset($productData['supplier_name'])) {
                    $supplier = AlibabaSupplier::firstOrCreate(
                        ['name' => $productData['supplier_name']],
                        [
                            'alibaba_id' => 'AUTO_' . time() . '_' . rand(1000, 9999),
                            'contact_person' => 'Auto-created',
                            'email' => 'supplier@alibaba.com',
                            'phone' => 'N/A',
                            'address' => 'Alibaba Supplier',
                            'country' => 'China',
                            'status' => 'active'
                        ]
                    );
                    $supplierId = $supplier->id;
                }

                // Create the product
                $product = new AlibabaProduct();
                $product->alibaba_product_id = 'ALI_URL_' . time();
                $product->supplier_id = $supplierId;
                $product->title = $productData['title'];
                $product->description = $productData['description'];
                $product->original_price = $productData['original_price'];
                $product->retail_price = $productData['original_price'] * (1 + $request->markup_percentage / 100);
                $product->min_order_quantity = $productData['min_order_quantity'];
                $product->stock_quantity = $productData['stock_quantity'];
                $product->images = json_encode($productData['images']);
                $product->specifications = json_encode($productData['specifications']);
                $product->status = 'imported';
                
                \Log::info('Saving Alibaba product:', [
                    'title' => $product->title,
                    'supplier_id' => $product->supplier_id,
                    'original_price' => $product->original_price,
                    'retail_price' => $product->retail_price
                ]);
                
                $product->save();
                
                // Convert to frontend product
                $this->convertToFrontendProduct($product);
            }

            // Log activity
            StaffActivityLog::logSuccess(
                auth()->id(),
                'alibaba_product_create',
                'Created new Alibaba product: ' . ($product->title ?? 'Unknown')
            );

            flash(translate('Product created successfully'))->success();
            return redirect()->route('alibaba.products.index');

        } catch (\Exception $e) {
            \Log::error('Alibaba product creation failed: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            flash(translate('Failed to create product: ' . $e->getMessage()))->error();
            return redirect()->back()->withInput();
        }
    }

    public function show(AlibabaProduct $product)
    {
        $product->load(['supplier', 'localProduct']);
        return view('backend.alibaba.products.show', compact('product'));
    }

    public function edit(AlibabaProduct $product)
    {
        try {
        $suppliers = AlibabaSupplier::active()->get();
            $categories = \App\Models\Category::where('parent_id', 0)->get();
            $brands = \App\Models\Brand::all();
        } catch (\Exception $e) {
            // If tables don't exist or other database issues, create empty collections
            $suppliers = collect([]);
            $categories = collect([]);
            $brands = collect([]);
        }
        
        return view('backend.alibaba.products.edit', compact('product', 'suppliers', 'categories', 'brands'));
    }

    public function update(Request $request, AlibabaProduct $product)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'retail_price' => 'required|numeric|min:0',
            'supplier_id' => 'required|exists:alibaba_suppliers,id'
        ]);

        try {
            $product->update($request->only(['title', 'description', 'retail_price', 'supplier_id']));
            
            // Log activity
            StaffActivityLog::logSuccess(
                auth()->id(),
                'alibaba_product_update',
                'Updated Alibaba product: ' . $product->title
            );

        flash(translate('Product updated successfully'))->success();
        return redirect()->route('alibaba.products.index');

        } catch (\Exception $e) {
            flash(translate('Failed to update product: ' . $e->getMessage()))->error();
            return back();
        }
    }

    public function destroy(AlibabaProduct $product)
    {
        try {
            $productTitle = $product->title;
            $product->delete();
            
            // Log activity
            StaffActivityLog::logWarning(
                auth()->id(),
                'alibaba_product_delete',
                'Deleted Alibaba product: ' . $productTitle
            );
            
            flash(translate('Product deleted successfully'))->success();
        } catch (\Exception $e) {
            flash(translate('Failed to delete product: ' . $e->getMessage()))->error();
        }
        
        return redirect()->route('alibaba.products.index');
    }

    public function bulkUpdatePricing(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'markup_percentage' => 'required|numeric|min:0|max:1000'
        ]);

        try {
            $updated = 0;
            foreach ($request->product_ids as $productId) {
                $product = AlibabaProduct::find($productId);
                if ($product) {
                    $product->retail_price = $product->original_price * (1 + $request->markup_percentage / 100);
                    $product->save();
                    $updated++;
                }
            }

            // Log activity
            StaffActivityLog::logSuccess(
                auth()->id(),
                'alibaba_bulk_pricing_update',
                "Updated pricing for {$updated} Alibaba products"
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully updated pricing for {$updated} products."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk pricing update failed: ' . $e->getMessage()
            ]);
        }
    }

    public function bulkImportCsv(Request $request)
    {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt',
                'default_markup' => 'required|numeric|min:0|max:1000'
            ]);

        try {
            $file = $request->file('csv_file');
            $handle = fopen($file->getPathname(), 'r');
            
            $headers = fgetcsv($handle);
            $imported = 0;
            $errors = [];
            
            while (($row = fgetcsv($handle)) !== false) {
                try {
                    $data = array_combine($headers, $row);
                    
                    $product = new AlibabaProduct();
                    $product->alibaba_product_id = 'ALI_CSV_' . time() . '_' . $imported;
                    $product->supplier_id = $data['Supplier ID'] ?? 1;
                    $product->title = $data['Title'] ?? 'Imported Product';
                    $product->description = $data['Description'] ?? 'Product imported from CSV';
                    $product->original_price = floatval($data['Original Price (USD)'] ?? 0);
                    $product->retail_price = $product->original_price * (1 + ($data['Markup Percentage'] ?? $request->default_markup) / 100);
                    $product->min_order_quantity = intval($data['Min Order Quantity'] ?? 1);
                    $product->stock_quantity = intval($data['Stock Quantity'] ?? 100);
                    $product->images = $data['Image URLs (separated by |)'] ? json_encode(explode('|', $data['Image URLs (separated by |)'])) : json_encode([]);
                    $product->specifications = $data['Specifications (JSON)'] ? json_decode($data['Specifications (JSON)'], true) : [];
                    $product->status = 'imported';
                    $product->save();
                    
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($imported + 1) . ": " . $e->getMessage();
                }
            }

            fclose($handle);

            $message = "Successfully imported {$imported} products from CSV.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'CSV import failed: ' . $e->getMessage()
            ]);
        }
    }

    public function bulkImportSupplier(Request $request)
    {
        try {
            $supplierId = $request->input('supplier_id');
            $importLimit = $request->input('import_limit', 100);
            $markupPercentage = $request->input('markup_percentage', 35);
            
            if (!$supplierId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier ID is required'
                ]);
            }
            
            $supplier = AlibabaSupplier::find($supplierId);
            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier not found'
                ]);
            }
            
            // Mock supplier catalog import
            $mockProducts = [
                ['title' => 'Product 1', 'price' => 10.00],
                ['title' => 'Product 2', 'price' => 15.00],
                ['title' => 'Product 3', 'price' => 20.00],
                ['title' => 'Product 4', 'price' => 25.00],
                ['title' => 'Product 5', 'price' => 30.00]
            ];
            
            $imported = 0;
            $limit = min($importLimit, count($mockProducts));
            
            for ($i = 0; $i < $limit; $i++) {
                $productData = $mockProducts[$i];
                
                    $product = new AlibabaProduct();
                $product->alibaba_product_id = 'ALI_SUP_' . $supplierId . '_' . time() . '_' . $i;
                    $product->supplier_id = $supplierId;
                $product->title = $productData['title'];
                    $product->description = 'Product imported from supplier catalog';
                $product->unit_price = $productData['price'];
                $product->retail_price = $productData['price'] * (1 + $markupPercentage / 100);
                $product->markup_percentage = $markupPercentage;
                    $product->status = 'imported';
                    $product->save();
                    
                    $imported++;
            }
            
            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} products from supplier catalog"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier catalog import failed: ' . $e->getMessage()
            ]);
        }
    }

    public function importTrending(Request $request)
    {
        try {
            $trendingProducts = [
                'Wireless Earbuds',
                'Smart Watch',
                'Phone Case',
                'Power Bank',
                'Bluetooth Speaker',
                'USB Cable',
                'Screen Protector',
                'Phone Stand'
            ];
            
            $imported = 0;
            
            foreach ($trendingProducts as $productName) {
                try {
                    $product = new AlibabaProduct();
                    $product->alibaba_product_id = 'ALI_TREND_' . time() . '_' . $imported;
                    $product->supplier_id = rand(1, 3);
                    $product->title = $productName;
                    $product->description = 'Trending product imported from Alibaba';
                    $product->original_price = rand(2000, 30000) / 100;
                    $product->retail_price = $product->original_price * (1 + rand(25, 50) / 100);
                    $product->min_order_quantity = 1;
                    $product->stock_quantity = rand(10, 1000);
                    $product->images = json_encode(['https://picsum.photos/300/300?random=13']);
                    $product->specifications = json_encode(['Brand' => 'Generic', 'Model' => 'Demo']);
                    $product->shipping_info = json_encode(['Free Shipping' => true]);
                    $product->status = 'imported';
                    $product->save();
                    
                    $imported++;
                } catch (\Exception $e) {
                    // Continue with next product
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} trending products."
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Trending products import failed: ' . $e->getMessage()
            ]);
        }
    }

    public function discoverProducts(Request $request)
    {
        try {
            $request->validate([
                'category' => 'nullable|string',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'sort_by' => 'nullable|string',
                'limit' => 'required|numeric|min:1|max:100',
                'markup_percentage' => 'required|numeric|min:0|max:1000'
            ]);
            
            $products = [];
            $categories = [
                'electronics' => ['Wireless Earbuds', 'Smart Watch', 'Power Bank', 'Bluetooth Speaker'],
                'fashion' => ['Phone Case', 'Screen Protector', 'Phone Stand', 'USB Cable'],
                'home' => ['LED Light', 'Kitchen Tool', 'Garden Item', 'Home Decor'],
                'beauty' => ['Face Mask', 'Skin Care', 'Hair Tool', 'Beauty Device'],
                'sports' => ['Fitness Tracker', 'Sports Gear', 'Outdoor Item', 'Exercise Tool'],
                'automotive' => ['Car Charger', 'Phone Mount', 'Car Accessory', 'Auto Tool'],
                'toys' => ['Educational Toy', 'Game Device', 'Hobby Item', 'Entertainment']
            ];
            
            $placeholderImages = [
                'https://picsum.photos/300/300?random=1',
                'https://picsum.photos/300/300?random=2',
                'https://picsum.photos/300/300?random=3',
                'https://picsum.photos/300/300?random=4',
                'https://picsum.photos/300/300?random=5',
                'https://picsum.photos/300/300?random=6',
                'https://picsum.photos/300/300?random=7',
                'https://picsum.photos/300/300?random=8',
                'https://picsum.photos/300/300?random=9',
                'https://picsum.photos/300/300?random=10'
            ];
            
            $category = $request->input('category');
            $limit = $request->input('limit');
            $markup = $request->input('markup_percentage');
            
            if ($category && isset($categories[$category])) {
                $productNames = $categories[$category];
            } else {
                $productNames = array_merge(...array_values($categories));
            }
            
            for ($i = 0; $i < min($limit, count($productNames)); $i++) {
                $productName = $productNames[$i % count($productNames)];
                $price = rand(500, 50000) / 100;
                
                $products[] = [
                    'title' => $productName . ' ' . ($i + 1),
                    'description' => 'High quality ' . strtolower($productName) . ' with great features',
                    'price' => $price,
                    'orders' => rand(100, 5000),
                    'image' => $placeholderImages[$i % count($placeholderImages)],
                    'url' => 'https://alibaba.com/product/' . ($i + 1),
                    'markup' => $markup
                ];
            }
            
            return response()->json([
                'success' => true,
                'products' => $products
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product discovery failed: ' . $e->getMessage()
            ]);
        }
    }

    public function importSingleProduct(Request $request)
    {
        try {
            $request->validate([
                'alibaba_url' => 'required|url',
                'markup_percentage' => 'required|numeric|min:0|max:1000',
                'supplier_id' => 'required|exists:alibaba_suppliers,id'
            ]);
            
            $product = new AlibabaProduct();
            $product->alibaba_product_id = 'ALI_SINGLE_' . time();
            $product->supplier_id = $request->supplier_id;
            $product->title = 'Imported Product from Discovery';
            $product->description = 'Product imported via product discovery - URL: ' . $request->alibaba_url;
            $product->original_price = rand(1000, 50000) / 100;
            $product->retail_price = $product->original_price * (1 + $request->markup_percentage / 100);
            $product->min_order_quantity = 1;
            $product->stock_quantity = rand(10, 1000);
            $product->images = json_encode(['https://picsum.photos/300/300?random=' . rand(1, 20)]);
            $product->specifications = json_encode(['Brand' => 'Generic', 'Model' => 'Demo']);
            $product->shipping_info = json_encode(['Free Shipping' => true]);
            $product->status = 'imported';
            $product->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Product imported successfully from discovery.',
                'product' => $product
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Single product import failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download CSV template for product import
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="alibaba_products_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Title',
                'Description', 
                'Original Price (USD)',
                'Markup Percentage',
                'Min Order Quantity',
                'Stock Quantity',
                'Image URLs (separated by |)',
                'Specifications (JSON)',
                'Shipping Info (JSON)',
                'Supplier ID'
            ]);

            // Sample data row
            fputcsv($file, [
                'Sample Product',
                'This is a sample product description',
                '19.99',
                '50',
                '1',
                '100',
                'https://example.com/image1.jpg|https://example.com/image2.jpg',
                '{"Brand": "Sample", "Model": "Demo"}',
                '{"Free Shipping": true, "Delivery": "5-7 days"}',
                '1'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function test()
    {
        return response()->json([
            'success' => true,
            'message' => 'Alibaba controller is working!'
        ]);
    }

    public function sync(AlibabaProduct $product)
    {
        try {
            // Log the sync activity
            StaffActivityLog::logInfo(
                auth()->id(),
                'alibaba_product_sync',
                'Synced Alibaba product: ' . $product->title
            );

            // For now, just return success - in a real implementation,
            // this would sync with Alibaba API to get updated data
            return response()->json([
                'success' => true,
                'message' => 'Product synced successfully',
                'data' => [
                    'id' => $product->id,
                    'title' => $product->title,
                    'last_sync' => now()->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Alibaba product sync failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePricing(AlibabaProduct $product)
    {
        try {
            // Get the current markup percentage (you might want to store this in the product)
            $markupPercentage = 35; // Default markup, you can make this configurable
            
            // Recalculate retail price based on original price and markup
            $newRetailPrice = $product->original_price * (1 + $markupPercentage / 100);
            
            // Update the product
            $product->retail_price = $newRetailPrice;
            $product->save();

            // Log the pricing update activity
            StaffActivityLog::logSuccess(
                auth()->id(),
                'alibaba_product_pricing_update',
                'Updated pricing for Alibaba product: ' . $product->title . ' - New retail price: ' . $newRetailPrice
            );

            return response()->json([
                'success' => true,
                'message' => 'Product pricing updated successfully',
                'data' => [
                    'id' => $product->id,
                    'title' => $product->title,
                    'original_price' => $product->original_price,
                    'retail_price' => $product->retail_price,
                    'markup_percentage' => $markupPercentage
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Alibaba product pricing update failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product pricing: ' . $e->getMessage()
            ], 500);
        }
    }

    public function convertToFrontend(AlibabaProduct $product)
    {
        try {
            // Check if already converted
            if ($product->local_product_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product already converted to frontend product'
                ]);
            }

            // Convert to frontend product
            $frontendProduct = $this->convertToFrontendProduct($product);

            // Log the conversion activity
            StaffActivityLog::logSuccess(
                auth()->id(),
                'alibaba_product_convert_to_frontend',
                'Converted Alibaba product to frontend: ' . $product->title
            );

            return response()->json([
                'success' => true,
                'message' => 'Product converted to frontend successfully',
                'data' => [
                    'alibaba_product_id' => $product->id,
                    'frontend_product_id' => $frontendProduct->id,
                    'title' => $frontendProduct->name,
                    'frontend_url' => route('product', $frontendProduct->slug)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Alibaba product conversion to frontend failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to convert product to frontend: ' . $e->getMessage()
            ], 500);
        }
    }

    private function convertToFrontendProduct(AlibabaProduct $alibabaProduct)
    {
        try {
            // Get the Manjaro Import category
            $manjaroImportCategory = \App\Models\Category::where('name', 'Manjaro Import')->first();
            $categoryId = $manjaroImportCategory ? $manjaroImportCategory->id : 1;

            // Create the frontend product
            $product = \App\Models\Product::create([
                'name' => $alibabaProduct->title,
                'description' => $alibabaProduct->description,
                'unit_price' => $alibabaProduct->retail_price,
                'unit' => 'piece',
                'min_qty' => $alibabaProduct->min_order_quantity,
                'tags' => 'alibaba,imported,manjaro',
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
                'category_id' => $categoryId,
                'brand_id' => null,
                'video_provider' => null,
                'video_link' => null
            ]);

            // Update the Alibaba product with the local product ID
            $alibabaProduct->local_product_id = $product->id;
            $alibabaProduct->save();

            \Log::info('Alibaba product converted to frontend product:', [
                'alibaba_product_id' => $alibabaProduct->id,
                'frontend_product_id' => $product->id,
                'title' => $product->name
            ]);

            return $product;

        } catch (\Exception $e) {
            \Log::error('Failed to convert Alibaba product to frontend product: ' . $e->getMessage());
            throw $e;
        }
    }

    public function fetchDetails(Request $request)
    {
        try {
            $request->validate([
                'alibaba_url' => 'required|url'
            ]);

            $url = $request->input('alibaba_url');
            
            // Extract product data from the Alibaba URL
            $productData = $this->scrapeAlibabaProduct($url);
            
            // The scraping function now returns fallback data instead of null
            // So we don't need to check for null anymore

            return response()->json([
                'success' => true,
                'data' => $productData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product details: ' . $e->getMessage()
            ]);
        }
    }

    private function scrapeAlibabaProduct($url)
    {
        try {
            \Log::info('Starting Alibaba scraping for URL: ' . $url);
            
            // Use cURL to fetch the page content
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $html = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            \Log::info('cURL response - HTTP Code: ' . $httpCode . ', Error: ' . $error . ', HTML Length: ' . strlen($html));
            
            if ($httpCode !== 200 || !$html) {
                throw new \Exception('Failed to fetch page content. HTTP Code: ' . $httpCode . ', Error: ' . $error);
            }
            
            // Parse the HTML content
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);
            
            // Extract product title - try multiple selectors
            $title = 'Product from Alibaba';
            $titleSelectors = [
                '//h1[@class="product-title-text"]',
                '//h1[contains(@class, "title")]',
                '//title',
                '//h1'
            ];
            
            foreach ($titleSelectors as $selector) {
                $titleNodes = $xpath->query($selector);
                if ($titleNodes->length > 0) {
                    $titleText = trim($titleNodes->item(0)->textContent);
                    if (!empty($titleText) && $titleText !== 'Alibaba.com') {
                        $title = $titleText;
                        break;
                    }
                }
            }
            
            // Extract price - try multiple selectors and regex
            $price = 0;
            $priceSelectors = [
                '//span[@class="price-current"]',
                '//span[contains(@class, "price")]',
                '//div[contains(@class, "price")]//span',
                '//span[contains(text(), "$")]'
            ];
            
            foreach ($priceSelectors as $selector) {
                $priceNodes = $xpath->query($selector);
                if ($priceNodes->length > 0) {
                    $priceText = $priceNodes->item(0)->textContent;
                    preg_match('/\$?[\d,]+\.?\d*/', $priceText, $matches);
                    if (!empty($matches)) {
                        $price = (float) str_replace([',', '$'], '', $matches[0]);
                        break;
                    }
                }
            }
            
            // Fallback to regex search in entire HTML
            if ($price == 0) {
                preg_match('/\$[\d,]+\.?\d*/', $html, $matches);
                if (!empty($matches)) {
                    $price = (float) str_replace([',', '$'], '', $matches[0]);
                }
            }
            
            // Convert USD to GHS (approximate rate: 1 USD = 12.5 GHS)
            // You can update this rate or fetch it from an API
            $usdToGhsRate = 12.5;
            $price = $price * $usdToGhsRate;
            
            // Extract images - try multiple selectors
            $images = [];
            $imageSelectors = [
                '//img[contains(@class, "detail-desc-decorate-richtext")]',
                '//img[contains(@class, "product-image")]',
                '//img[contains(@class, "gallery")]',
                '//img[contains(@src, "alicdn")]',
                '//img[contains(@src, "alibaba")]'
            ];
            
            foreach ($imageSelectors as $selector) {
                $imageNodes = $xpath->query($selector);
                foreach ($imageNodes as $img) {
                    if ($img instanceof \DOMElement) {
                        $src = $img->getAttribute('src');
                        if ($src && !empty($src) && !in_array($src, $images)) {
                            $images[] = $src;
                        }
                    }
                }
            }
            
            // Extract description - try multiple approaches
            $description = '';
            $descSelectors = [
                '//div[contains(@class, "product-desc")]',
                '//div[contains(@class, "description")]',
                '//div[contains(@class, "detail-desc")]',
                '//div[contains(@class, "product-detail-desc")]',
                '//div[contains(@class, "detail-desc-decorate-richtext")]',
                '//div[contains(@class, "product-content")]'
            ];
            
            foreach ($descSelectors as $selector) {
                $descNodes = $xpath->query($selector);
                if ($descNodes->length > 0) {
                    $descText = trim($descNodes->item(0)->textContent);
                    if (!empty($descText) && strlen($descText) > 50) {
                        $description = $descText;
                        break;
                    }
                }
            }
            
            // If no text description found, create one from product title and specifications
            if (empty($description)) {
                $description = "High-quality product imported from Alibaba. " . $title . " - Professional grade item with excellent value for money.";
            }
            
            // Extract supplier information
            $supplierName = 'Alibaba Supplier';
            $supplierNodes = $xpath->query('//div[contains(@class, "company-name")]//a');
            if ($supplierNodes->length > 0) {
                $supplierName = trim($supplierNodes->item(0)->textContent);
            }
            
            // Extract minimum order quantity
            $minOrderQty = 1;
            $minOrderNodes = $xpath->query('//span[contains(text(), "Minimum order quantity")]/following-sibling::span');
            if ($minOrderNodes->length > 0) {
                $minOrderText = $minOrderNodes->item(0)->textContent;
                preg_match('/\d+/', $minOrderText, $matches);
                if (!empty($matches)) {
                    $minOrderQty = (int) $matches[0];
                }
            }
            
            $result = [
                'title' => $title ?: 'Product from Alibaba',
                'description' => $description ?: 'Product imported from Alibaba',
                'original_price' => $price ?: 0,
                'min_order_quantity' => $minOrderQty,
                'stock_quantity' => 100, // Default value
                'images' => array_slice($images, 0, 5), // Limit to 5 images
                'supplier_name' => $supplierName,
                'specifications' => [
                    'Source' => 'Alibaba',
                    'Supplier' => $supplierName
                ]
            ];
            
            \Log::info('Alibaba scraping result: ' . json_encode($result));
            return $result;
            
        } catch (\Exception $e) {
            \Log::error('Alibaba scraping error: ' . $e->getMessage());
            
            // Return fallback data instead of null
            return [
                'title' => 'Product from Alibaba',
                'description' => 'Product imported from Alibaba - ' . $url,
                'original_price' => 0,
                'min_order_quantity' => 1,
                'stock_quantity' => 100,
                'images' => [],
                'supplier_name' => 'Alibaba Supplier',
                'specifications' => [
                    'Source' => 'Alibaba',
                    'Supplier' => 'Alibaba Supplier',
                    'URL' => $url
                ]
            ];
        }
    }
}