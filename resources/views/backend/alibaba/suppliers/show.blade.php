@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Supplier Details')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('alibaba.suppliers.index') }}" class="btn btn-primary">
                <i class="las la-arrow-left"></i>
                <span>{{translate('Back to Suppliers')}}</span>
            </a>
            <a href="{{ route('alibaba.suppliers.edit', $supplier->id) }}" class="btn btn-info">
                <i class="las la-edit"></i>
                <span>{{translate('Edit Supplier')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Supplier Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-primary">{{translate('Name')}}:</td>
                                <td>{{ $supplier->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-primary">{{translate('Alibaba ID')}}:</td>
                                <td>{{ $supplier->alibaba_id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-primary">{{translate('Contact Person')}}:</td>
                                <td>{{ $supplier->contact_person ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-primary">{{translate('Email')}}:</td>
                                <td>{{ $supplier->email ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-primary">{{translate('Phone')}}:</td>
                                <td>{{ $supplier->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-primary">{{translate('Website')}}:</td>
                                <td>
                                    @if($supplier->website)
                                        <a href="{{ $supplier->website }}" target="_blank">{{ $supplier->website }}</a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-primary">{{translate('Status')}}:</td>
                                <td>
                                    @if($supplier->status == 1)
                                        <span class="badge badge-inline badge-success">{{translate('Active')}}</span>
                                    @else
                                        <span class="badge badge-inline badge-danger">{{translate('Inactive')}}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-primary">{{translate('Created')}}:</td>
                                <td>{{ $supplier->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @if($supplier->address)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-primary">{{translate('Address')}}:</h6>
                        <p>{{ $supplier->address }}</p>
                    </div>
                </div>
                @endif
                @if($supplier->description)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-primary">{{translate('Description')}}:</h6>
                        <p>{{ $supplier->description }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Statistics')}}</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-right">
                            <h3 class="text-primary">{{ $supplier->products_count ?? 0 }}</h3>
                            <p class="text-muted">{{translate('Products')}}</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div>
                            <h3 class="text-success">{{ $supplier->orders_count ?? 0 }}</h3>
                            <p class="text-muted">{{translate('Orders')}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($supplier->products && $supplier->products->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Related Products')}}</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{translate('Product Name')}}</th>
                        <th>{{translate('Alibaba ID')}}</th>
                        <th>{{translate('Price')}}</th>
                        <th>{{translate('Status')}}</th>
                        <th>{{translate('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($supplier->products as $key => $product)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->alibaba_id }}</td>
                        <td>{{ single_price($product->price) }}</td>
                        <td>
                            @if($product->status == 1)
                                <span class="badge badge-inline badge-success">{{translate('Active')}}</span>
                            @else
                                <span class="badge badge-inline badge-danger">{{translate('Inactive')}}</span>
                            @endif
                        </td>
                        <td>
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('alibaba.products.show', $product->id)}}" title="{{ translate('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection