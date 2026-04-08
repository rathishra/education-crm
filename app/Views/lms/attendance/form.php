<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= url('elms/attendance') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <h4 class="fw-bold mb-0" style="color:#0f172a">
        <i class="fas fa-<?= isset($session)?'edit':'plus-circle' ?> me-2 text-primary"></i><?= e($pageTitle) ?>
    </h4>
</div>

<?php $action = isset($session) ? url('elms/attendance/'.$session['id'].'/update') : url('elms/attendance/store'); ?>

<div style="max-width:640px">
<form method="POST" action="<?= $action ?>">
    <?= csrfField() ?>

    <?php $errs = flash('errors', null) ?? []; ?>
    <?php if (!empty($errs)): ?>
    <div class="alert alert-danger py-2 small"><?= implode('<br>', array_map('e', is_array($errs)?$errs:[$errs])) ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-3 border p-4 mb-3" style="border-color:#e8e3ff!important">
        <h6 class="fw-bold mb-3" style="color:#0f172a"><i class="fas fa-info-circle me-2 text-primary"></i>Session Details</h6>

        <div class="mb-3">
            <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" value="<?= e($session['title'] ?? 'Class Session') ?>" required>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold small">Course <span class="text-danger">*</span></label>
                <select name="course_id" class="form-select" required <?= isset($session)?'disabled':'' ?>>
                    <option value="">— Select Course —</option>
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (($session['course_id'] ?? $courseId)==$c['id'])?'selected':'' ?>><?= e($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($session)): ?>
                <input type="hidden" name="course_id" value="<?= $session['course_id'] ?>">
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small">Session Type</label>
                <select name="type" class="form-select">
                    <?php foreach (['offline'=>'Offline / In-Person','online'=>'Online / Remote','live'=>'Live Stream'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= ($session['type']??'offline')===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Date <span class="text-danger">*</span></label>
                <input type="date" name="session_date" class="form-control" value="<?= e($session['session_date'] ?? date('Y-m-d')) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Start Time</label>
                <input type="time" name="start_time" class="form-control" value="<?= e($session['start_time'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small">End Time</label>
                <input type="time" name="end_time" class="form-control" value="<?= e($session['end_time'] ?? '') ?>">
            </div>
        </div>

        <div class="mb-1">
            <label class="form-label fw-semibold small">Notes</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Optional session notes…"><?= e($session['notes'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary" style="border-radius:9px"><i class="fas fa-save me-2"></i><?= isset($session)?'Save Changes':'Create &amp; Mark Attendance' ?></button>
        <a href="<?= url('elms/attendance') ?>" class="btn btn-outline-secondary" style="border-radius:9px">Cancel</a>
    </div>
</form>
</div>
