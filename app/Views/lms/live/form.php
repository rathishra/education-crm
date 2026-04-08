<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= url('elms/live') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <h4 class="fw-bold mb-0" style="color:#0f172a">
        <i class="fas fa-<?= isset($class)?'edit':'broadcast-tower' ?> me-2 text-primary"></i><?= e($pageTitle) ?>
    </h4>
</div>

<?php $isEdit = isset($class); ?>
<?php $action = $isEdit ? url('elms/live/'.$class['id'].'/update') : url('elms/live/store'); ?>

<div style="max-width:720px">
<form method="POST" action="<?= $action ?>">
    <?= csrfField() ?>

    <?php $errs = flash('errors', null) ?? []; ?>
    <?php if (!empty($errs)): ?>
    <div class="alert alert-danger py-2 small"><?= implode('<br>', array_map('e', is_array($errs)?$errs:[$errs])) ?></div>
    <?php endif; ?>

    <!-- Basic info -->
    <div class="bg-white rounded-3 border p-4 mb-3" style="border-color:#e8e3ff!important">
        <h6 class="fw-bold mb-3" style="color:#0f172a"><i class="fas fa-info-circle me-2 text-primary"></i>Session Details</h6>

        <div class="mb-3">
            <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" value="<?= e($class['title'] ?? '') ?>" placeholder="e.g. Week 3 Live Q&A" required>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-7">
                <label class="form-label fw-semibold small">Course <span class="text-danger">*</span></label>
                <select name="course_id" class="form-select" required <?= $isEdit?'disabled':'' ?>>
                    <option value="">— Select Course —</option>
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (($class['course_id'] ?? $courseId)==$c['id'])?'selected':'' ?>><?= e($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isEdit): ?><input type="hidden" name="course_id" value="<?= $class['course_id'] ?>"><?php endif; ?>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold small">Platform</label>
                <select name="platform" class="form-select" id="platformSel" onchange="updatePlatformHint()">
                    <?php foreach (['zoom'=>'Zoom','google_meet'=>'Google Meet','teams'=>'Microsoft Teams','webex'=>'Webex','custom'=>'Custom / Other'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= ($class['platform']??'zoom')===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold small">Description</label>
            <textarea name="description" class="form-control" rows="2" placeholder="What will be covered in this session?"><?= e($class['description'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- Meeting link -->
    <div class="bg-white rounded-3 border p-4 mb-3" style="border-color:#e8e3ff!important">
        <h6 class="fw-bold mb-3" style="color:#0f172a"><i class="fas fa-link me-2 text-primary"></i>Meeting Details</h6>

        <div class="mb-3">
            <label class="form-label fw-semibold small">Meeting URL <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text" id="platformIconWrap" style="background:#f8f7ff;border-color:#e8e3ff"><i class="fas fa-video text-primary" id="platformIcon"></i></span>
                <input type="url" name="meeting_url" class="form-control" value="<?= e($class['meeting_url'] ?? '') ?>"
                       placeholder="https://zoom.us/j/..." id="meetingUrl" required>
                <a href="#" class="btn btn-outline-secondary" id="testLinkBtn" target="_blank" title="Test link"><i class="fas fa-external-link-alt"></i></a>
            </div>
            <div class="text-muted small mt-1" id="platformHint">Paste the join URL directly from your meeting platform</div>
        </div>

        <div class="row g-2 mb-1">
            <div class="col-md-6">
                <label class="form-label fw-semibold small">Meeting ID <span class="text-muted fw-normal">(optional)</span></label>
                <input type="text" name="meeting_id" class="form-control" value="<?= e($class['meeting_id'] ?? '') ?>" placeholder="123 456 7890">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small">Passcode <span class="text-muted fw-normal">(optional)</span></label>
                <input type="text" name="meeting_password" class="form-control" value="<?= e($class['meeting_password'] ?? '') ?>" placeholder="abc123">
            </div>
        </div>
    </div>

    <!-- Schedule -->
    <div class="bg-white rounded-3 border p-4 mb-3" style="border-color:#e8e3ff!important">
        <h6 class="fw-bold mb-3" style="color:#0f172a"><i class="fas fa-calendar-alt me-2 text-primary"></i>Schedule</h6>

        <div class="row g-2 mb-3">
            <div class="col-md-5">
                <label class="form-label fw-semibold small">Date &amp; Time <span class="text-danger">*</span></label>
                <input type="datetime-local" name="scheduled_at" class="form-control"
                       value="<?= e(isset($class['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($class['scheduled_at'])) : '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Duration (minutes)</label>
                <input type="number" name="duration_mins" class="form-control" min="5" max="480"
                       value="<?= (int)($class['duration_mins'] ?? 60) ?>" placeholder="60">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Max Participants</label>
                <input type="number" name="max_participants" class="form-control" min="1"
                       value="<?= e($class['max_participants'] ?? '') ?>" placeholder="Unlimited">
            </div>
        </div>

        <div class="row g-3">
            <div class="col-auto">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_recorded" id="isRecorded" <?= !empty($class['is_recorded'])?'checked':'' ?>>
                    <label class="form-check-label small fw-semibold" for="isRecorded">Session will be recorded</label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_published" id="isPublished" <?= (!$isEdit || !empty($class['is_published']))?'checked':'' ?>>
                    <label class="form-check-label small fw-semibold" for="isPublished">Published (visible to students)</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Host notes (instructor-only) -->
    <div class="bg-white rounded-3 border p-4 mb-3" style="border-color:#e8e3ff!important">
        <h6 class="fw-bold mb-2" style="color:#0f172a"><i class="fas fa-sticky-note me-2 text-primary"></i>Host Notes <span class="text-muted small fw-normal">(private — not shown to students)</span></h6>
        <textarea name="host_notes" class="form-control" rows="2" placeholder="Agenda, tech checklist, reminder notes…"><?= e($class['host_notes'] ?? '') ?></textarea>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary" style="border-radius:9px"><i class="fas fa-save me-2"></i><?= $isEdit?'Save Changes':'Schedule Class' ?></button>
        <a href="<?= url('elms/live') ?>" class="btn btn-outline-secondary" style="border-radius:9px">Cancel</a>
    </div>
</form>
</div>

<script>
const platformHints = {
    zoom:        'Paste the Zoom join link (zoom.us/j/...)',
    google_meet: 'Paste the Google Meet link (meet.google.com/...)',
    teams:       'Paste the Microsoft Teams meeting link',
    webex:       'Paste the Webex meeting link (webex.com/meet/...)',
    custom:      'Paste any meeting or streaming URL',
};
const platformIcons = {
    zoom:'fas fa-video', google_meet:'fab fa-google', teams:'fab fa-microsoft',
    webex:'fas fa-video', custom:'fas fa-globe',
};

function updatePlatformHint() {
    const v = document.getElementById('platformSel').value;
    document.getElementById('platformHint').textContent = platformHints[v] || '';
    const ic = document.getElementById('platformIcon');
    ic.className = platformIcons[v] + ' text-primary';
}
updatePlatformHint();

document.getElementById('meetingUrl').addEventListener('input', function() {
    document.getElementById('testLinkBtn').href = this.value || '#';
});
document.getElementById('testLinkBtn').href = document.getElementById('meetingUrl').value || '#';
</script>
