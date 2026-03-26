<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Student ERP')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; }
        .sidebar { width: 260px; }
        .content-shell { margin-left: 260px; padding: 2rem; }
        .list-group-item.active { background-color: #0d6efd; border-color: #0d6efd; }
        .metric-card { border-radius: 0.75rem; }
        .timeline-entry { border-left: 3px solid #0d6efd; padding-left: 1rem; margin-bottom: 1rem; }
        .sidebar .nav-link { color: #fff; }
        .sidebar .nav-link:hover { text-decoration: none; }
    </style>
    @stack('styles')
</head>
<body class="bg-light">
    <div class="d-flex">
        <nav class="sidebar bg-dark text-white position-fixed h-100">
            <div class="p-4">
                <a href="{{ route('students.dashboard') }}" class="d-flex align-items-center mb-3 text-white text-decoration-none">
                    <i class="fas fa-school me-2"></i><span class="fs-5 fw-bold">Edu ERP</span>
                </a>
                <hr class="border-secondary">
                <div class="list-group list-group-flush">
                    <a href="{{ route('students.dashboard') }}" class="list-group-item list-group-item-action bg-transparent text-white">
                        <i class="fas fa-tachometer-alt me-2"></i>Student Dashboard
                    </a>
                    <div class="mt-2 text-uppercase small text-muted ps-2">Students</div>
                    <a href="{{ route('students.create') }}" class="list-group-item bg-transparent">
                        <i class="fas fa-user-plus me-2"></i>Add Student
                    </a>
                    <a href="{{ route('students.index') }}" class="list-group-item bg-transparent">
                        <i class="fas fa-users me-2"></i>Student List
                    </a>
                    <a href="{{ route('students.documents.index') ?? '#' }}" class="list-group-item bg-transparent">
                        <i class="fas fa-file-alt me-2"></i>Student Documents
                    </a>
                    <a href="{{ route('students.behaviour.index') ?? '#' }}" class="list-group-item bg-transparent">
                        <i class="fas fa-exclamation-triangle me-2"></i>Student Behaviour
                    </a>
                    <a href="{{ route('students.promotion.index') ?? '#' }}" class="list-group-item bg-transparent">
                        <i class="fas fa-arrow-up me-2"></i>Student Promotion
                    </a>
                    <a href="{{ route('students.alumni.index') ?? '#' }}" class="list-group-item bg-transparent">
                        <i class="fas fa-user-graduate me-2"></i>Alumni
                    </a>
                </div>
            </div>
        </nav>
        <main class="content-shell flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">@yield('page-title')</h1>
                <div>
                    @yield('page-actions')
                </div>
            </div>
            <div>
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
