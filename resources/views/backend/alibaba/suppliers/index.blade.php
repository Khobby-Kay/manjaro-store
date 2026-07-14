@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Alibaba Suppliers')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('alibaba.suppliers.create') }}" class="btn btn-primary">
                <i class="las la-plus"></i>
                <span>{{translate('Add New Supplier')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header row gutters-5">
        <div class="col">
            <h5 class="mb-md-0 h6">{{translate('All Suppliers')}}</h5>
        </div>
        <div class="col-md-3">
            <form class="" id="sort_suppliers" action="" method="GET">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="search" name="search" @isset($search) value="{{ $search }}" @endisset placeholder="{{ translate('Type name & Enter') }}">
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Contact Person')}}</th>
                    <th>{{translate('Email')}}</th>
                    <th>{{translate('Phone')}}</th>
                    <th>{{translate('Status')}}</th>
                    <th>{{translate('Products Count')}}</th>
                    <th>{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @if($suppliers->count() > 0)
                    @foreach($suppliers as $key => $supplier)
                    <tr>
                        <td>{{ ($key+1) + ($suppliers->currentPage() - 1) * $suppliers->perPage() }}</td>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->contact_person }}</td>
                        <td>{{ $supplier->email }}</td>
                        <td>{{ $supplier->phone }}</td>
                        <td>
                            @if($supplier->status == 1)
                                <span class="badge badge-inline badge-success">{{translate('Active')}}</span>
                            @else
                                <span class="badge badge-inline badge-danger">{{translate('Inactive')}}</span>
                            @endif
                        </td>
                        <td>{{ $supplier->products_count ?? 0 }}</td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('alibaba.suppliers.edit', $supplier->id)}}" title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{route('alibaba.suppliers.show', $supplier->id)}}" title="{{ translate('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('alibaba.suppliers.destroy', $supplier->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="text-center">
                            <p class="text-muted">{{ translate('No suppliers found.') }}</p>
                            <a href="{{ route('alibaba.suppliers.create') }}" class="btn btn-primary btn-sm">
                                {{ translate('Add Your First Supplier') }}
                            </a>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $suppliers->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        // Simple script for search functionality
        $(document).ready(function() {
            $('#search').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $("table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
@endsection