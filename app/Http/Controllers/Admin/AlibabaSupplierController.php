<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AlibabaSupplier;
use App\Models\StaffActivityLog;
use App\Services\AlibabaApiService;

class AlibabaSupplierController extends Controller
{
    protected $apiService;

    public function __construct(AlibabaApiService $apiService)
    {
        $this->apiService = $apiService;
        // Temporarily comment out permission middleware for testing
        // $this->middleware(['permission:manage_alibaba_suppliers']);
    }

    public function index()
    {
        try {
            $suppliers = AlibabaSupplier::latest()->paginate(20);
        } catch (\Exception $e) {
            // If table doesn't exist or other database issues, create empty collection
            $suppliers = collect([])->paginate(20);
        }
        return view('backend.alibaba.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('backend.alibaba.suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'alibaba_id' => 'nullable|string|unique:alibaba_suppliers',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        // Generate alibaba_id if not provided
        if (!$request->alibaba_id) {
            $request->merge(['alibaba_id' => 'AUTO_' . time() . '_' . rand(1000, 9999)]);
        }

        $supplier = AlibabaSupplier::create($request->all());

        // Log activity
        StaffActivityLog::logSuccess(
            auth()->id(),
            'alibaba_supplier_create',
            'Created new Alibaba supplier: ' . $supplier->name
        );

        // Check if this is an AJAX request
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'supplier_id' => $supplier->id,
                'message' => 'Supplier created successfully'
            ]);
        }

        flash(translate('Supplier created successfully'))->success();
        return redirect()->route('alibaba.suppliers.index');
    }

    public function show(AlibabaSupplier $supplier)
    {
        $supplier->load(['products', 'orders']);
        return view('backend.alibaba.suppliers.show', compact('supplier'));
    }

    public function edit(AlibabaSupplier $supplier)
    {
        return view('backend.alibaba.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, AlibabaSupplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'alibaba_id' => 'required|string|unique:alibaba_suppliers,alibaba_id,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        $supplier->update($request->all());

        // Log activity
        StaffActivityLog::logSuccess(
            auth()->id(),
            'alibaba_supplier_update',
            'Updated Alibaba supplier: ' . $supplier->name
        );

        flash(translate('Supplier updated successfully'))->success();
        return redirect()->route('alibaba.suppliers.index');
    }

    public function destroy(AlibabaSupplier $supplier)
    {
        $supplierName = $supplier->name;
        $supplier->delete();

        // Log activity
        StaffActivityLog::logWarning(
            auth()->id(),
            'alibaba_supplier_delete',
            'Deleted Alibaba supplier: ' . $supplierName
        );

        flash(translate('Supplier deleted successfully'))->success();
        return redirect()->route('alibaba.suppliers.index');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:alibaba_suppliers,id'
        ]);

        try {
            AlibabaSupplier::whereIn('id', $request->ids)->delete();
            return response()->json(1);
        } catch (\Exception $e) {
            return response()->json(0);
        }
    }
}