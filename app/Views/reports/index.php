<?php $pageTitle = 'Reports'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-chart-bar me-2"></i>Reports</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row g-4">
    <!-- Lead Reports -->
    <div class="col-md-4">
        <a href="<?= url('reports/leads') ?>" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-hover">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;">
                        <i class="fas fa-user-plus fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title">Lead Reports</h5>
                    <p class="card-text text-muted small">Analyze leads by source, status, counselor. Track conversion rates and daily trends.</p>
                </div>
                <div class="card-footer bg-transparent text-primary text-center border-0">
                    View Report <i class="fas fa-arrow-right ms-1"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Admission Reports -->
    <div class="col-md-4">
        <a href="<?= url('reports/admissions') ?>" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-hover">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;">
                        <i class="fas fa-user-graduate fa-2x text-success"></i>
                    </div>
                    <h5 class="card-title">Admission Reports</h5>
                    <p class="card-text text-muted small">View admissions by status, course, and daily trends. Filter by date range.</p>
                </div>
                <div class="card-footer bg-transparent text-success text-center border-0">
                    View Report <i class="fas fa-arrow-right ms-1"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Revenue Reports -->
    <div class="col-md-4">
        <a href="<?= url('reports/revenue') ?>" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-hover">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;">
                        <i class="fas fa-rupee-sign fa-2x text-warning"></i>
                    </div>
                    <h5 class="card-title">Revenue Report</h5>
                    <p class="card-text text-muted small">Track fee collections, payment modes, and revenue by course. Daily revenue charts.</p>
                </div>
                <div class="card-footer bg-transparent text-warning text-center border-0">
                    View Report <i class="fas fa-arrow-right ms-1"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Counselor Performance -->
    <div class="col-md-4">
        <a href="<?= url('reports/counselor') ?>" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-hover">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;">
                        <i class="fas fa-headset fa-2x text-info"></i>
                    </div>
                    <h5 class="card-title">Counselor Performance</h5>
                    <p class="card-text text-muted small">Compare counselors on leads handled, conversions, followups, and conversion rates.</p>
                </div>
                <div class="card-footer bg-transparent text-info text-center border-0">
                    View Report <i class="fas fa-arrow-right ms-1"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Institution-wise Report -->
    <div class="col-md-4">
        <a href="<?= url('reports/institution-wise') ?>" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-hover">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;">
                        <i class="fas fa-building fa-2x text-secondary"></i>
                    </div>
                    <h5 class="card-title">Institution-wise Report</h5>
                    <p class="card-text text-muted small">Multi-institution summary: leads, admissions, active students, and revenue per institution.</p>
                </div>
                <div class="card-footer bg-transparent text-secondary text-center border-0">
                    View Report <i class="fas fa-arrow-right ms-1"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<style>
.card-hover { transition: transform .15s, box-shadow .15s; cursor: pointer; }
.card-hover:hover { transform: translateY(-4px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.1) !important; }
</style>
