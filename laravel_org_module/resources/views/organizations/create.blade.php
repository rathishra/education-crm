@extends('layouts.app')

@section('title', 'Add Organization')
@section('page_title', 'Create Organization')

@section('content')
<div class="mb-3">
    <a href="{{ route('organizations.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0 mt-1">Organization Details</h5>
    </div>
    <div class="card-body">
        
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('organizations.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">Organization Name *</label>
                    <input type="text" class="form-control" name="organization_name" value="{{ old('organization_name') }}" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label required">Organization Code *</label>
                    <input type="text" class="form-control" name="organization_code" value="{{ old('organization_code') }}" required placeholder="e.g. ORG-001">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" class="form-control" name="phone" value="{{ old('phone') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Max Institutions Limit *</label>
                    <input type="number" class="form-control" name="max_institutions" value="{{ old('max_institutions', 1) }}" min="1" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-12 mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Create Organization</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
