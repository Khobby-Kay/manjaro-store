@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Edit Supplier')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('alibaba.suppliers.index') }}" class="btn btn-primary">
                <i class="las la-arrow-left"></i>
                <span>{{translate('Back to Suppliers')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Edit Supplier Information')}}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('alibaba.suppliers.update', $supplier->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('Name')}} <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{translate('Supplier Name')}}" id="name" name="name" class="form-control" required value="{{ old('name', $supplier->name) }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="alibaba_id">{{translate('Alibaba ID')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{translate('Alibaba Supplier ID')}}" id="alibaba_id" name="alibaba_id" class="form-control" value="{{ old('alibaba_id', $supplier->alibaba_id) }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="contact_person">{{translate('Contact Person')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{translate('Contact Person Name')}}" id="contact_person" name="contact_person" class="form-control" value="{{ old('contact_person', $supplier->contact_person) }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="email">{{translate('Email')}}</label>
                        <div class="col-sm-9">
                            <input type="email" placeholder="{{translate('Email Address')}}" id="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="phone">{{translate('Phone')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{translate('Phone Number')}}" id="phone" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="website">{{translate('Website')}}</label>
                        <div class="col-sm-9">
                            <input type="url" placeholder="{{translate('Website URL')}}" id="website" name="website" class="form-control" value="{{ old('website', $supplier->website) }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="address">{{translate('Address')}}</label>
                        <div class="col-sm-9">
                            <textarea name="address" rows="3" class="form-control" placeholder="{{translate('Address')}}">{{ old('address', $supplier->address) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="status">{{translate('Status')}}</label>
                        <div class="col-sm-9">
                            <select class="form-control aiz-selectpicker" name="status" id="status">
                                <option value="1" {{ old('status', $supplier->status) == 1 ? 'selected' : '' }}>{{translate('Active')}}</option>
                                <option value="0" {{ old('status', $supplier->status) == 0 ? 'selected' : '' }}>{{translate('Inactive')}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row">
                        <label class="col-sm-2 col-from-label" for="description">{{translate('Description')}}</label>
                        <div class="col-sm-10">
                            <textarea name="description" rows="4" class="form-control" placeholder="{{translate('Supplier Description')}}">{{ old('description', $supplier->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group mb-0 text-right">
                <button type="submit" class="btn btn-primary">{{translate('Update Supplier')}}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.aiz-selectpicker').selectpicker();
        });
    </script>
@endsection