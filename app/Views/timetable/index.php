<?php $pageTitle = 'Timetable'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-calendar-alt me-2"></i>Timetable</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Timetable</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('timetable.manage')): ?>
        <a href="<?= url('timetable/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Period</a>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <div class="card-body bg-light">
        <form method="GET" action="<?= url('timetable') ?>" id="filterForm">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label required">Course</label>
                    <select class="form-select" name="course_id" required onchange="document.getElementById('filterForm').submit()">
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label required">Batch</label>
                    <select class="form-select" name="batch_id" required onchange="document.getElementById('filterForm').submit()">
                        <option value="">-- Select Batch --</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $batchId == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> View</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($batchId): ?>
<div class="card">
    <div class="card-header"><i class="fas fa-table me-2"></i>Weekly Schedule</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 text-center" style="min-width: 800px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 15%">Day</th>
                        <th style="width: 85%">Periods</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
                    foreach ($days as $key => $label): 
                        $periods = $timetable[$key] ?? [];
                    ?>
                    <tr>
                        <th class="align-middle bg-light"><?= $label ?></th>
                        <td class="text-start p-2">
                            <?php if (empty($periods)): ?>
                                <span class="text-muted small">No class scheduled</span>
                            <?php else: ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($periods as $p): ?>
                                    <div class="card border-primary" style="width: 200px;">
                                        <div class="card-body p-2 position-relative">
                                            <?php if (hasPermission('timetable.manage')): ?>
                                                <form action="<?= url('timetable/'.$p['id'].'/delete') ?>" method="POST" class="position-absolute top-0 end-0 p-1" onsubmit="return confirm('Delete this period?');">
                                                    <?= csrfField() ?>
                                                    <button type="submit" class="btn btn-link text-danger p-0 border-0"><i class="fas fa-times"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            <div class="fw-bold text-primary small mb-1"><?= date('H:i', strtotime($p['start_time'])) ?> - <?= date('H:i', strtotime($p['end_time'])) ?></div>
                                            <div class="fw-semibold text-truncate" title="<?= e($p['subject_name']) ?>"><?= e($p['subject_code']) ?></div>
                                            <div class="small text-muted text-truncate"><i class="fas fa-user-tie me-1"></i><?= e($p['faculty_name']) ?></div>
                                            <?php if ($p['room_number']): ?>
                                                <div class="small text-muted mt-1"><i class="fas fa-map-marker-alt me-1"></i>Room <?= e($p['room_number']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
