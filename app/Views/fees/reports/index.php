<?php $pageTitle = 'Fee Reports'; ?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-chart-bar me-2 text-primary"></i>Fee Reports</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('fees') ?>">Fees</a></li>
            <li class="breadcrumb-item active">Reports</li>
        </ol></nav>
    </div>
</div>

<!-- This Month Stats -->
<div class="row g-3 mb-4">
    <?php foreach([
        ['This Month Collection', '₹'.number_format($monthStats['collection'] ?? 0,2), 'rupee-sign', 'success', url('fees/reports/collection')],
        ['Pending Dues', '₹'.number_format($monthStats['pending'] ?? 0,2), 'exclamation-circle', 'danger', url('fees/reports/pending')],
        ['Overdue Students', $monthStats['overdue_students'] ?? 0, 'user-times', 'warning', url('fees/reports/pending').'?status=overdue'],
        ['Concessions Applied', '₹'.number_format($monthStats['concessions'] ?? 0,2), 'tag', 'info', url('fees/concessions')],
    ] as [$label,$val,$icon,$color,$link]): ?>
    <div class="col-6 col-md-3">
        <a href="<?= $link ?>" class="text-decoration-none">
        <div class="card border-0 shadow-sm card-hover"><div class="card-body d-flex align-items-center gap-3 py-3">
            <div class="rounded-circle bg-<?= $color ?>-subtle d-flex align-items-center justify-content-center" style="width:46px;height:46px">
                <i class="fas fa-<?= $icon ?> text-<?= $color ?>"></i>
            </div>
            <div>
                <div class="fw-bold fs-5"><?= $val ?></div>
                <div class="text-muted small"><?= $label ?></div>
            </div>
        </div></div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Report Cards -->
<div class="row g-4 mb-4">

    <!-- Fee Collection Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:52px;height:52px">
                        <i class="fas fa-receipt text-success fa-lg"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Fee Collection Report</div>
                        <div class="text-muted small">Daily, monthly or custom range</div>
                    </div>
                </div>
                <p class="text-muted small flex-grow-1">Track all fee collections by date range, payment mode, course or fee head. Export to CSV.</p>
                <a href="<?= url('fees/reports/collection') ?>" class="btn btn-success w-100">
                    <i class="fas fa-chart-line me-1"></i>Open Report
                </a>
            </div>
        </div>
    </div>

    <!-- Pending Dues Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center" style="width:52px;height:52px">
                        <i class="fas fa-exclamation-circle text-danger fa-lg"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Pending Dues Report</div>
                        <div class="text-muted small">Outstanding & overdue fees</div>
                    </div>
                </div>
                <p class="text-muted small flex-grow-1">View all students with pending or overdue balances. Filter by course, batch, academic year. Export to CSV.</p>
                <a href="<?= url('fees/reports/pending') ?>" class="btn btn-danger w-100">
                    <i class="fas fa-file-invoice me-1"></i>Open Report
                </a>
            </div>
        </div>
    </div>

    <!-- Student Ledger -->
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:52px;height:52px">
                        <i class="fas fa-book-open text-primary fa-lg"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Student Fee Ledger</div>
                        <div class="text-muted small">Complete transaction history</div>
                    </div>
                </div>
                <p class="text-muted small flex-grow-1">View a student's complete fee ledger — all assigned fees, payments, concessions, fines and refunds in chronological order.</p>
                <div class="input-group">
                    <select id="ledgerStudentSelect" class="form-select" style="width:70%">
                        <option value="">Search student...</option>
                    </select>
                    <a href="<?= url('fees/reports/ledger/0') ?>" id="btnOpenLedger" class="btn btn-primary" style="pointer-events:none;opacity:.5">
                        <i class="fas fa-book-open me-1"></i>View
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Head-wise Summary -->
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center" style="width:52px;height:52px">
                        <i class="fas fa-tags text-warning fa-lg"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Fee Head-wise Summary</div>
                        <div class="text-muted small">Collection by fee category</div>
                    </div>
                </div>
                <p class="text-muted small flex-grow-1">Summarize total collections broken down by fee head — tuition, exam, lab, transport, etc.</p>
                <a href="<?= url('fees/reports/collection') ?>?group_by=head" class="btn btn-warning w-100">
                    <i class="fas fa-chart-pie me-1"></i>View Summary
                </a>
            </div>
        </div>
    </div>

    <!-- Payment Mode Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:52px;height:52px">
                        <i class="fas fa-credit-card text-info fa-lg"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Payment Mode Report</div>
                        <div class="text-muted small">Cash, UPI, Card, Cheque</div>
                    </div>
                </div>
                <p class="text-muted small flex-grow-1">Breakdown of collection by payment mode for reconciliation — cash register, UPI settlement, card batch, cheque clearance.</p>
                <a href="<?= url('fees/reports/collection') ?>?group_by=mode" class="btn btn-info text-white w-100">
                    <i class="fas fa-wallet me-1"></i>View Report
                </a>
            </div>
        </div>
    </div>

    <!-- Course-wise Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center" style="width:52px;height:52px">
                        <i class="fas fa-graduation-cap text-secondary fa-lg"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Course-wise Report</div>
                        <div class="text-muted small">Collections per program</div>
                    </div>
                </div>
                <p class="text-muted small flex-grow-1">Compare total fees collected vs pending across all courses and programs for the academic year.</p>
                <a href="<?= url('fees/reports/collection') ?>?group_by=course" class="btn btn-secondary w-100">
                    <i class="fas fa-university me-1"></i>View Report
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Collection Chart (last 7 days) -->
<?php if(!empty($dailyCollection)): ?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white fw-semibold py-3"><i class="fas fa-chart-line me-2 text-success"></i>Last 7 Days Collection</div>
    <div class="card-body">
        <canvas id="collectionChart" height="80"></canvas>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const BASE = '<?= url('') ?>';

// Student ledger search
$('#ledgerStudentSelect').select2({
    width: '100%',
    ajax: {
        url: BASE + 'fees/assignment/ajax/search',
        dataType: 'json',
        delay: 250,
        data: params => ({ term: params.term }),
        processResults: r => ({ results: r.results || [] }),
        minimumInputLength: 2,
    },
    placeholder: 'Search student...',
}).on('change', function() {
    const sid = $(this).val();
    const btn = $('#btnOpenLedger');
    if (sid) {
        btn.attr('href', BASE + 'fees/reports/ledger/' + sid)
           .css({ 'pointer-events': '', 'opacity': '1' });
    } else {
        btn.attr('href', '#').css({ 'pointer-events': 'none', 'opacity': '.5' });
    }
});

<?php if(!empty($dailyCollection)): ?>
const labels = <?= json_encode(array_column($dailyCollection, 'day')) ?>;
const amounts = <?= json_encode(array_column($dailyCollection, 'total')) ?>;
new Chart(document.getElementById('collectionChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Collection (₹)',
            data: amounts,
            backgroundColor: 'rgba(22,163,74,.7)',
            borderColor: 'rgb(22,163,74)',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => '₹' + v.toLocaleString('en-IN') } } }
    }
});
<?php endif; ?>
</script>
