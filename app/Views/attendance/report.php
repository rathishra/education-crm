<?php $pageTitle = 'Attendance Reports'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-chart-line me-2"></i>Attendance Reports</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('attendance') ?>">Attendance</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('attendance') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><i class="fas fa-filter me-2"></i>Filter Reports</div>
            <div class="card-body">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-tools fs-1 mb-3"></i>
                    <h4>Advanced Reporting Engine</h4>
                    <p>The detailed attendance reports, including Monthly Sheets and Defaulter Lists, will be available in the upcoming analytics release.</p>
                </div>
            </div>
        </div>
    </div>
</div>
