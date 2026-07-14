@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Alibaba Orders')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <button class="btn btn-info" onclick="syncOrderStatuses()">
                <i class="las la-sync"></i>
                <span>{{translate('Sync Order Statuses')}}</span>
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header row gutters-5">
        <div class="col">
            <h5 class="mb-md-0 h6">{{translate('All Orders')}}</h5>
        </div>
        <div class="col-md-3">
            <form class="" id="sort_orders" action="" method="GET">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="search" name="search" @isset($search) value="{{ $search }}" @endisset placeholder="{{ translate('Type order ID & Enter') }}">
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <form class="" id="sort_orders" action="" method="GET">
            <div class="row gutters-5 mb-3">
                <div class="col-md-2">
                    <select class="form-control aiz-selectpicker" data-live-search="true" name="supplier_id" id="supplier_id">
                        <option value="">{{translate('All Suppliers')}}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @isset($supplier_id) @if($supplier_id == $supplier->id) selected @endif @endisset>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control aiz-selectpicker" name="status" id="status">
                        <option value="">{{translate('All Status')}}</option>
                        <option value="pending" @isset($status) @if($status == 'pending') selected @endif @endisset>{{translate('Pending')}}</option>
                        <option value="confirmed" @isset($status) @if($status == 'confirmed') selected @endif @endisset>{{translate('Confirmed')}}</option>
                        <option value="shipped" @isset($status) @if($status == 'shipped') selected @endif @endisset>{{translate('Shipped')}}</option>
                        <option value="delivered" @isset($status) @if($status == 'delivered') selected @endif @endisset>{{translate('Delivered')}}</option>
                        <option value="cancelled" @isset($status) @if($status == 'cancelled') selected @endif @endisset>{{translate('Cancelled')}}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary" type="submit">{{translate('Filter')}}</button>
                </div>
            </div>
        </form>
        
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Order ID')}}</th>
                    <th>{{translate('Customer')}}</th>
                    <th>{{translate('Supplier')}}</th>
                    <th>{{translate('Product')}}</th>
                    <th>{{translate('Quantity')}}</th>
                    <th>{{translate('Total Amount')}}</th>
                    <th>{{translate('Status')}}</th>
                    <th>{{translate('Order Date')}}</th>
                    <th>{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @if($orders->count() > 0)
                    @foreach($orders as $key => $order)
                    <tr>
                        <td>{{ ($key+1) + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
                        <td>
                            <a href="{{route('alibaba.orders.show', $order->id)}}">
                                #{{ $order->order_id }}
                            </a>
                        </td>
                        <td>{{ $order->customer_name ?? 'N/A' }}</td>
                        <td>{{ $order->supplier->name ?? 'N/A' }}</td>
                        <td>
                            @if($order->product)
                                <a href="{{route('alibaba.products.show', $order->product->id)}}">
                                    {{ Str::limit($order->product->title, 30) }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $order->quantity }}</td>
                        <td>{{ single_price($order->total_amount) }}</td>
                        <td>
                            @if($order->status == 'pending')
                                <span class="badge badge-inline badge-warning">{{translate('Pending')}}</span>
                            @elseif($order->status == 'confirmed')
                                <span class="badge badge-inline badge-info">{{translate('Confirmed')}}</span>
                            @elseif($order->status == 'shipped')
                                <span class="badge badge-inline badge-primary">{{translate('Shipped')}}</span>
                            @elseif($order->status == 'delivered')
                                <span class="badge badge-inline badge-success">{{translate('Delivered')}}</span>
                            @elseif($order->status == 'cancelled')
                                <span class="badge badge-inline badge-danger">{{translate('Cancelled')}}</span>
                            @else
                                <span class="badge badge-inline badge-secondary">{{translate('Unknown')}}</span>
                            @endif
                        </td>
                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('alibaba.orders.show', $order->id)}}" title="{{ translate('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                            @if($order->status == 'pending')
                                <button class="btn btn-soft-success btn-icon btn-circle btn-sm" onclick="placeOrder({{ $order->id }})" title="{{ translate('Place Order') }}">
                                    <i class="las la-check"></i>
                                </button>
                            @endif
                            @if($order->status != 'cancelled' && $order->status != 'delivered')
                                <button class="btn btn-soft-danger btn-icon btn-circle btn-sm" onclick="cancelOrder({{ $order->id }})" title="{{ translate('Cancel') }}">
                                    <i class="las la-times"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="10" class="text-center">
                            <p class="text-muted">{{ translate('No orders found.') }}</p>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $orders->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection

@section('modal')
    @include('modals.delete_modal')
    
    <!-- Place Order Modal -->
    <div class="modal fade" id="place-order-modal" tabindex="-1" role="dialog" aria-labelledby="place-order-modal-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="place-order-modal-label">{{translate('Place Order with Supplier')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="place-order-form">
                        @csrf
                        <input type="hidden" name="order_id" id="place-order-id">
                        
                        <div class="form-group">
                            <label>{{translate('Order Notes')}}</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="{{translate('Enter any special instructions for the supplier')}}"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>{{translate('Expected Delivery Date')}}</label>
                            <input type="date" class="form-control" name="expected_delivery_date">
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="send_notification" id="send_notification" checked>
                                <label class="custom-control-label" for="send_notification">
                                    {{translate('Send notification to customer')}}
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="button" class="btn btn-primary" onclick="confirmPlaceOrder()">{{translate('Place Order')}}</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancel-order-modal" tabindex="-1" role="dialog" aria-labelledby="cancel-order-modal-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancel-order-modal-label">{{translate('Cancel Order')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="cancel-order-form">
                        @csrf
                        <input type="hidden" name="order_id" id="cancel-order-id">
                        
                        <div class="form-group">
                            <label>{{translate('Cancellation Reason')}} <span class="text-danger">*</span></label>
                            <select class="form-control" name="reason" required>
                                <option value="">{{translate('Select reason')}}</option>
                                <option value="customer_request">{{translate('Customer Request')}}</option>
                                <option value="out_of_stock">{{translate('Out of Stock')}}</option>
                                <option value="supplier_issue">{{translate('Supplier Issue')}}</option>
                                <option value="payment_failed">{{translate('Payment Failed')}}</option>
                                <option value="other">{{translate('Other')}}</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>{{translate('Additional Notes')}}</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="{{translate('Enter additional details about the cancellation')}}"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="refund_customer" id="refund_customer" checked>
                                <label class="custom-control-label" for="refund_customer">
                                    {{translate('Refund customer automatically')}}
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="button" class="btn btn-danger" onclick="confirmCancelOrder()">{{translate('Cancel Order')}}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            // Simple script for search functionality
            $('#search').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $("table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });

        function syncOrderStatuses() {
            if(confirm('{{ translate("Sync all order statuses with suppliers? This may take a few minutes.") }}')) {
                $.ajax({
                    url: '{{ route("alibaba.orders.sync-statuses") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if(response.success) {
                            AIZ.plugins.notify('success', response.message);
                            location.reload();
                        } else {
                            AIZ.plugins.notify('error', response.message);
                        }
                    },
                    error: function() {
                        AIZ.plugins.notify('error', '{{ translate("Sync failed") }}');
                    }
                });
            }
        }

        function placeOrder(orderId) {
            $('#place-order-id').val(orderId);
            $('#place-order-modal').modal('show');
        }

        function confirmPlaceOrder() {
            var formData = new FormData($('#place-order-form')[0]);
            
            $.ajax({
                url: '{{ route("alibaba.orders.place-with-supplier", ":id") }}'.replace(':id', $('#place-order-id').val()),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.success) {
                        AIZ.plugins.notify('success', response.message);
                        $('#place-order-modal').modal('hide');
                        location.reload();
                    } else {
                        AIZ.plugins.notify('error', response.message);
                    }
                },
                error: function() {
                    AIZ.plugins.notify('error', '{{ translate("Failed to place order") }}');
                }
            });
        }

        function cancelOrder(orderId) {
            $('#cancel-order-id').val(orderId);
            $('#cancel-order-modal').modal('show');
        }

        function confirmCancelOrder() {
            var formData = new FormData($('#cancel-order-form')[0]);
            
            $.ajax({
                url: '{{ route("alibaba.orders.cancel", ":id") }}'.replace(':id', $('#cancel-order-id').val()),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.success) {
                        AIZ.plugins.notify('success', response.message);
                        $('#cancel-order-modal').modal('hide');
                        location.reload();
                    } else {
                        AIZ.plugins.notify('error', response.message);
                    }
                },
                error: function() {
                    AIZ.plugins.notify('error', '{{ translate("Failed to cancel order") }}');
                }
            });
        }
    </script>
@endsection