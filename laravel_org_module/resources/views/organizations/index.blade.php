@extends('layouts.app')

@section('title', 'Manage Organizations')
@section('page_title', 'Organizations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Organization List</h4>
    <a href="{{ route('organizations.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Organization</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('organizations.index') }}" class="row g-3 align-items-center">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search name or code...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100"><i class="fas fa-search me-1"></i> Filter</button>
            </div>
            @if(request()->anyFilled(['search', 'status']))
            <div class="col-md-2">
                <a href="{{ route('organizations.index') }}" class="btn btn-outline-danger w-100">Clear</a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Organization Name</th>
                        <th>Contact</th>
                        <th>Institutions</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organizations as $org)
                    <tr>
                        <td><code>{{ $org->organization_code }}</code></td>
                        <td class="fw-bold text-primary">{{ $org->organization_name }}</td>
                        <td>
                            <div class="small">{{ $org->email ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $org->phone ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-info text-dark rounded-pill">{{ $org->institutions_count }} / {{ $org->max_institutions }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $org->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($org->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('organizations.edit', $org) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            
                            <form action="{{ route('organizations.destroy', $org) }}" method="POST" class="d-inline" onsubmit="return confirm('Suspend this organization?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No organizations found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($organizations->hasPages())
    <div class="card-footer border-0 bg-white">
        {{ $organizations->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
