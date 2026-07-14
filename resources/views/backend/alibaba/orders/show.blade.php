@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Order Details')}} #{{ $order->order_id }}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('alibaba.orders.index') }}" class="btn btn-primary">
                <i class="las la-arrow-left"></i>
                <span>{{translate('Back to Orders')}}</span>
            </a>
            @if($order->status == 'pending')
                <button class="btn btn-success" onclick="placeOrder({{ $order->id }})">
                    <i class="las la-check"></i>
                    <span>{{translate('Place Order')}}</span>
                </button>
            @endif
            @if($order->status != 'cancelled' && $order->status != 'delivered')
                <button class="btn btn-danger" onclick="cancelOrder({{ $order->id }})">
                    <i class="las la-times"></i>
                    <span>{{translate('Cancel Order')}}</span>
                </button>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Order Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>{{translate('Order ID:')}}</strong></td>
                                <td>#{{ $order->order_id }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Customer:')}}</strong></td>
                                <td>{{ $order->customer_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Email:')}}</strong></td>
                                <td>{{ $order->customer_email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Phone:')}}</strong></td>
                                <td>{{ $order->customer_phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Status:')}}</strong></td>
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
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>{{translate('Supplier:')}}</strong></td>
                                <td>{{ $order->supplier->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Product:')}}</strong></td>
                                <td>
                                    @if($order->product)
                                        <a href="{{route('alibaba.products.show', $order->product->id)}}">
                                            {{ $order->product->title }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Quantity:')}}</strong></td>
                                <td>{{ $order->quantity }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Unit Price:')}}</strong></td>
                                <td>{{ single_price($order->unit_price) }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Total Amount:')}}</strong></td>
                                <td><strong>{{ single_price($order->total_amount) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($order->notes)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>{{translate('Order Notes')}}</h6>
                        <div class="border p-3 rounded">
                            {{ $order->notes }}
                        </div>
                    </div>
                </div>
                @endif
                
                @if($order->cancellation_reason)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>{{translate('Cancellation Details')}}</h6>
                        <div class="border p-3 rounded">
                            <p><strong>{{translate('Reason:')}}</strong> {{ $order->cancellation_reason }}</p>
                            @if($order->cancellation_notes)
                                <p><strong>{{translate('Notes:')}}</strong> {{ $order->cancellation_notes }}</p>
                            @endif
                            <p><strong>{{translate('Cancelled At:')}}</strong> {{ $order->cancelled_at ? $order->cancelled_at->format('M d, Y H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Shipping Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>{{translate('Shipping Address')}}</h6>
                        <address>
                            {{ $order->shipping_address ?? 'N/A' }}
                        </address>
                    </div>
                    <div class="col-md-6">
                        <h6>{{translate('Shipping Details')}}</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>{{translate('Method:')}}</strong></td>
                                <td>{{ $order->shipping_method ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Tracking Number:')}}</strong></td>
                                <td>{{ $order->tracking_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Expected Delivery:')}}</strong></td>
                                <td>{{ $order->expected_delivery_date ? $order->expected_delivery_date->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Order Timeline')}}</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">{{translate('Order Created')}}</h6>
                            <p class="timeline-text">{{ $order->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    
                    @if($order->confirmed_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">{{translate('Order Confirmed')}}</h6>
                            <p class="timeline-text">{{ $order->confirmed_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($order->shipped_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">{{translate('Order Shipped')}}</h6>
                            <p class="timeline-text">{{ $order->shipped_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($order->delivered_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">{{translate('Order Delivered')}}</h6>
                            <p class="timeline-text">{{ $order->delivered_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($order->cancelled_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-danger"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">{{translate('Order Cancelled')}}</h6>
                            <p class="timeline-text">{{ $order->cancelled_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Quick Actions')}}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($order->status == 'pending')
                        <button class="btn btn-success" onclick="placeOrder({{ $order->id }})">
                            <i class="las la-check"></i>
                            {{translate('Place Order')}}
                        </button>
                    @endif
                    
                    @if($order->status == 'confirmed' || $order->status == 'shipped')
                        <button class="btn btn-info" onclick="updateStatus({{ $order->id }})">
                            <i class="las la-edit"></i>
                            {{translate('Update Status')}}
                        </button>
                    @endif
                    
                    @if($order->status != 'cancelled' && $order->status != 'delivered')
                        <button class="btn btn-danger" onclick="cancelOrder({{ $order->id }})">
                            <i class="las la-times"></i>
                            {{translate('Cancel Order')}}
                        </button>
                    @endif
                    
                    <button class="btn btn-warning" onclick="syncOrder({{ $order->id }})">
                        <i class="las la-sync"></i>
                        {{translate('Sync with Supplier')}}
                    </button>
                </div>
            </div>
        </div>
        
        @if($order->product)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Product Details')}}</h5>
            </div>
            <div class="card-body">
                <div class="text-center">
                    @if($order->product->images)
                        @php
                            $images = json_decode($order->product->images, true);
                            $firstImage = is_array($images) && !empty($images) ? $images[0] : 'assets/img/placeholder.jpg';
                        @endphp
                        @if(filter_var($firstImage, FILTER_VALIDATE_URL))
                            <img src="{{ $firstImage }}" class="img-fluid rounded mb-2" alt="{{ $order->product->title }}">
                        @else
                            <img src="{{ asset($firstImage) }}" class="img-fluid rounded mb-2" alt="{{ $order->product->title }}">
                        @endif
                    @else
                        <img src="{{ asset('assets/img/placeholder.jpg') }}" class="img-fluid rounded mb-2" alt="Placeholder">
                    @endif
                    <h6>{{ $order->product->title }}</h6>
                    <p class="text-muted small">{{ Str::limit($order->product->description, 100) }}</p>
                    <a href="{{route('alibaba.products.show', $order->product->id)}}" class="btn btn-sm btn-primary">
                        {{translate('View Product')}}
                    </a>
                </div>
            </div>
        </div>
        @endif
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
    
    <!-- Update Status Modal -->
    <div class="modal fade" id="update-status-modal" tabindex="-1" role="dialog" aria-labelledby="update-status-modal-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="update-status-modal-label">{{translate('Update Order Status')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="update-status-form">
                        @csrf
                        <input type="hidden" name="order_id" id="update-status-order-id">
                        
                        <div class="form-group">
                            <label>{{translate('New Status')}} <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="">{{translate('Select status')}}</option>
                                <option value="confirmed">{{translate('Confirmed')}}</option>
                                <option value="shipped">{{translate('Shipped')}}</option>
                                <option value="delivered">{{translate('Delivered')}}</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>{{translate('Tracking Number')}}</label>
                            <input type="text" class="form-control" name="tracking_number" placeholder="{{translate('Enter tracking number')}}">
                        </div>
                        
                        <div class="form-group">
                            <label>{{translate('Notes')}}</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="{{translate('Enter status update notes')}}"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="button" class="btn btn-primary" onclick="confirmUpdateStatus()">{{translate('Update Status')}}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
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

        function updateStatus(orderId) {
            $('#update-status-order-id').val(orderId);
            $('#update-status-modal').modal('show');
        }

        function confirmUpdateStatus() {
            var formData = new FormData($('#update-status-form')[0]);
            
            $.ajax({
                url: '{{ route("alibaba.orders.update-status", ":id") }}'.replace(':id', $('#update-status-order-id').val()),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.success) {
                        AIZ.plugins.notify('success', response.message);
                        $('#update-status-modal').modal('hide');
                        location.reload();
                    } else {
                        AIZ.plugins.notify('error', response.message);
                    }
                },
                error: function() {
                    AIZ.plugins.notify('error', '{{ translate("Failed to update status") }}');
                }
            });
        }

        function syncOrder(orderId) {
            if(confirm('{{ translate("Sync this order with the supplier?") }}')) {
                $.ajax({
                    url: '{{ route("alibaba.orders.sync", ":id") }}'.replace(':id', orderId),
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
    </script>
    
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-marker {
            position: absolute;
            left: -35px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        
        .timeline-content {
            padding-left: 15px;
        }
        
        .timeline-title {
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .timeline-text {
            margin-bottom: 0;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
@endsection
