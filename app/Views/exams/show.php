<?php $pageTitle = 'Exam Details: ' . e($exam['name']); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-file-signature me-2"></i><?= e($exam['name']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('exams') ?>">Exams</a></li>
                <li class="breadcrumb-item active"><?= e($exam['name']) ?></li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('exams') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row">
    <!-- Exam Info -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light"><i class="fas fa-info-circle me-2"></i>Exam Overview</div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted w-50">Exam Type</td>
                        <td class="fw-semibold"><?= ucfirst(str_replace('_', ' ', $exam['type'])) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Start Date</td>
                        <td class="fw-semibold"><?= formatDate($exam['start_date']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">End Date</td>
                        <td class="fw-semibold"><?= formatDate($exam['end_date']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            <span class="badge bg-<?= $exam['status'] === 'completed' ? 'success' : ($exam['status'] === 'ongoing' ? 'primary' : 'info') ?>">
                                <?= ucfirst($exam['status']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <?php if (hasPermission('exams.manage')): ?>
            <div class="card-footer bg-light">
                <h6 class="mb-3">Add Schedule</h6>
                <form action="<?= url("exams/{$exam['id']}/schedule") ?>" method="POST">
                    <?= csrfField() ?>
                    <div class="mb-2">
                        <select class="form-select form-select-sm" name="subject_id" required>
                            <option value="">-- Select Subject --</option>
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="date" class="form-control form-control-sm" name="date" required placeholder="Date">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><input type="time" class="form-control form-control-sm" name="start_time" required placeholder="Start"></div>
                        <div class="col-6"><input type="time" class="form-control form-control-sm" name="end_time" required placeholder="End"></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><input type="number" class="form-control form-control-sm" name="max_marks" required placeholder="Max Marks" value="100"></div>
                        <div class="col-6"><input type="number" class="form-control form-control-sm" name="min_marks" required placeholder="Min/Pass Marks" value="35"></div>
                    </div>
                    <div class="mb-2">
                        <input type="text" class="form-control form-control-sm" name="room_number" placeholder="Room No (Optional)">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-plus me-1"></i>Add</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Exam Schedules -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light"><i class="fas fa-calendar-alt me-2"></i>Exam Schedule</div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Subject</th>
                            <th>Marks (Min/Max)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schedules)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No schedules added yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($schedules as $sch): ?>
                            <tr>
                                <td class="fw-semibold"><?= formatDate($sch['date'], 'd M Y') ?></td>
                                <td><?= date('H:i', strtotime($sch['start_time'])) ?> - <?= date('H:i', strtotime($sch['end_time'])) ?></td>
                                <td><?= e($sch['subject_name']) ?> <small class="text-muted">(<?= e($sch['subject_code']) ?>)</small></td>
                                <td><?= e($sch['min_marks']) ?> / <?= e($sch['max_marks']) ?></td>
                                <td class="text-end">
                                    <?php if (hasPermission('exams.enter_marks')): ?>
                                        <a href="<?= url("exams/{$exam['id']}/schedule/{$sch['id']}/marks") ?>" class="btn btn-sm btn-outline-success" title="Enter Marks"><i class="fas fa-edit"></i> Marks</a>
                                    <?php endif; ?>
                                    
                                    <?php if (hasPermission('exams.manage')): ?>
                                        <form action="<?= url("exams/{$exam['id']}/schedule/{$sch['id']}/delete") ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this schedule? Related marks may also be lost.');">
                                            <?= csrfField() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
