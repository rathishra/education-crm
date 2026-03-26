<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Education ERP - Super Admin')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; width: 250px; background: #2c3e50; color: #fff; }
        .sidebar a { color: #ecf0f1; text-decoration: none; display: block; padding: 10px 15px; }
        .sidebar a:hover, .sidebar a.active { background: #34495e; border-left: 4px solid #3498db; }
        .content { flex-grow: 1; background: #f8f9fa; }
    </style>
</head>
<body class="d-flex">
    
    <!-- Sidebar -->
    <div class="sidebar p-3">
        <h4 class="text-center py-2 mb-4 border-bottom">ERP Admin</h4>
        <a href="{{ route('organizations.index') }}" class="{{ request()->routeIs('organizations.*') ? 'active' : '' }}"><i class="fas fa-sitemap me-2"></i> Organizations</a>
        <a href="{{ route('institutions.index') }}" class="{{ request()->routeIs('institutions.*') ? 'active' : '' }}"><i class="fas fa-university me-2"></i> Institutions</a>
        <a href="{{ route('campuses.index') }}" class="{{ request()->routeIs('campuses.*') ? 'active' : '' }}"><i class="fas fa-building me-2"></i> Campuses</a>
        <a href="{{ route('departments.index') }}" class="{{ request()->routeIs('departments.*') ? 'active' : '' }}"><i class="fas fa-network-wired me-2"></i> Departments</a>
        <a href="{{ route('courses.index') }}" class="{{ request()->routeIs('courses.*') ? 'active' : '' }}"><i class="fas fa-graduation-cap me-2"></i> Courses</a>
        <a href="{{ route('batches.index') }}" class="{{ request()->routeIs('batches.*') ? 'active' : '' }}"><i class="fas fa-users me-2"></i> Batches</a>
        <a href="{{ route('sections.index') }}" class="{{ request()->routeIs('sections.*') ? 'active' : '' }}"><i class="fas fa-chalkboard-teacher me-2"></i> Sections</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">@yield('page_title')</span>
                <div class="d-flex">
                    <span class="navbar-text">Logged in as Super Admin</span>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables if needed -->
    @stack('scripts')
</body>
</html>
