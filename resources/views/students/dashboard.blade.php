@extends('layouts.app')

@section('title', 'Student Dashboard')
@section('page-title', 'Student Dashboard')

@section('page-actions')
    <a href="{{ route('students.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus me-2"></i>Add Student
    </a>
@endsection

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <x-metric-card title="Total Students" value="{{ number_format($stats['total']) }}" icon="fas fa-users" description="All students" />
        </div>
        <div class="col-md-3">
            <x-metric-card title="Active Students" value="{{ number_format($stats['active']) }}" icon="fas fa-user-check" description="Currently enrolled" />
        </div>
        <div class="col-md-3">
            <x-metric-card title="New Admissions" value="{{ number_format($stats['new_admissions']) }}" icon="fas fa-user-plus" description="This month" />
        </div>
        <div class="col-md-3">
            <x-metric-card title="Alumni" value="{{ number_format($stats['alumni']) }}" icon="fas fa-user-graduate" description="Graduated students" />
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Department breakdown</strong>
                </div>
                <div class="card-body">
                    <canvas id="departmentChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Course distribution</strong>
                </div>
                <div class="card-body">
                    <canvas id="courseChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between">
            <strong>Recent Student Activities</strong>
            <a href="{{ route('students.index') }}" class="small text-muted">View all</a>
        </div>
        <div class="card-body">
            @forelse($recentActivities as $activity)
                <div class="timeline-entry">
                    <div class="d-flex justify-content-between">
                        <strong>{{ ucfirst($activity->event_type) }}</strong>
                        <small class="text-muted">{{ $activity->happened_at->format('d M, Y H:i') }}</small>
                    </div>
                    <p class="mb-1">{{ $activity->event_title }}</p>
                    <small class="text-secondary">{{ $activity->event_details }}</small>
                </div>
            @empty
                <p class="text-muted mb-0">No events recorded yet.</p>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const deptCtx = document.getElementById('departmentChart');
        if (deptCtx) {
            new Chart(deptCtx, {
                type: 'bar',
                data: {
                    labels: @json(array_column($departmentBreakdown, 'label')),
                    datasets: [{
                        label: 'Students',
                        data: @json(array_column($departmentBreakdown, 'count')),
                        backgroundColor: '#0d6efd'
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        }

        const courseCtx = document.getElementById('courseChart');
        if (courseCtx) {
            new Chart(courseCtx, {
                type: 'doughnut',
                data: {
                    labels: @json(array_column($courseBreakdown, 'label')),
                    datasets: [{
                        data: @json(array_column($courseBreakdown, 'count')),
                        backgroundColor: ['#0d6efd','#6610f2','#198754','#fd7e14','#dc3545']
                    }]
                },
                options: { responsive: true }
            });
        }
    </script>
@endpush
