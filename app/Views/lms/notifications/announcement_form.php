<style>
.form-section { background:#fff; border-radius:14px; border:1px solid #e8e3ff; padding:1.5rem; margin-bottom:1rem; }
.type-btn { border-radius:10px; padding:.45rem 1rem; font-size:.82rem; font-weight:600; border:2px solid transparent; cursor:pointer; transition:all .15s; }
.type-btn.selected { border-color:currentColor; }
</style>

<?php $isEdit = !empty($ann); ?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= url('elms/announcements') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left me-1"></i>Back</a>
    <h4 class="fw-bold mb-0 ms-1" style="color:#0f172a">
        <i class="fas fa-bullhorn me-2 text-primary"></i><?= $isEdit ? 'Edit Announcement' : 'New Announcement' ?>
    </h4>
</div>

<?php if (!empty(flash('errors'))): ?>
<div class="alert alert-danger rounded-3 small">
    <?php foreach ((array)flash('errors') as $e): ?><div><i class="fas fa-exclamation-circle me-1"></i><?= e($e) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" action="<?= $isEdit ? url('elms/announcements/'.$ann['id'].'/update') : url('elms/announcements/store') ?>">
    <?= csrfField() ?>

    <!-- Core Content -->
    <div class="form-section">
        <div class="mb-3">
            <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" style="border-radius:9px"
                   value="<?= e($ann['title'] ?? '') ?>" placeholder="Announcement title…" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold small">Body <span class="text-danger">*</span></label>
            <textarea name="body" class="form-control" rows="6" style="border-radius:9px;resize:vertical"
                      placeholder="Write your announcement here…" required><?= e($ann['body'] ?? '') ?></textarea>
        </div>

        <!-- Type selector -->
        <div class="mb-0">
            <label class="form-label fw-semibold small d-block">Type</label>
            <input type="hidden" name="type" id="typeInput" value="<?= e($ann['type'] ?? 'info') ?>">
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="type-btn" data-type="info"
                        style="background:#eff6ff;color:#2563eb"
                        onclick="setType('info')">
                    <i class="fas fa-info-circle me-1"></i>Info
                </button>
                <button type="button" class="type-btn" data-type="success"
                        style="background:#f0fdf4;color:#059669"
                        onclick="setType('success')">
                    <i class="fas fa-check-circle me-1"></i>Success
                </button>
                <button type="button" class="type-btn" data-type="warning"
                        style="background:#fffbeb;color:#d97706"
                        onclick="setType('warning')">
                    <i class="fas fa-exclamation-triangle me-1"></i>Warning
                </button>
                <button type="button" class="type-btn" data-type="danger"
                        style="background:#fff1f2;color:#dc2626"
                        onclick="setType('danger')">
                    <i class="fas fa-times-circle me-1"></i>Alert
                </button>
            </div>
        </div>
    </div>

    <!-- Targeting & Scheduling -->
    <div class="form-section">
        <div class="fw-semibold small mb-3" style="color:#6366f1"><i class="fas fa-cog me-1"></i>Targeting & Scheduling</div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold small">Target Audience</label>
                <select name="course_id" class="form-select" style="border-radius:9px">
                    <option value="">Institution-wide (all users)</option>
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($ann['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Leave blank to send to all users in your institution.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold small">Publish Status</label>
                <div class="form-check form-switch mt-1">
                    <input class="form-check-input" type="checkbox" name="is_published" id="isPublished" value="1"
                           <?= (!$isEdit || !empty($ann['is_published'])) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="isPublished">Published (visible to users)</label>
                </div>
                <div class="form-text">Uncheck to save as a draft.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold small"><i class="fas fa-calendar-check me-1 text-success"></i>Publish At</label>
                <input type="datetime-local" name="publish_at" class="form-control" style="border-radius:9px"
                       value="<?= !empty($ann['publish_at']) ? date('Y-m-d\TH:i', strtotime($ann['publish_at'])) : '' ?>">
                <div class="form-text">Schedule a future publish date (leave blank to publish now).</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold small"><i class="fas fa-calendar-times me-1 text-danger"></i>Expires At</label>
                <input type="datetime-local" name="expires_at" class="form-control" style="border-radius:9px"
                       value="<?= !empty($ann['expires_at']) ? date('Y-m-d\TH:i', strtotime($ann['expires_at'])) : '' ?>">
                <div class="form-text">Announcement will auto-hide after this date (leave blank = never).</div>
            </div>
        </div>
    </div>

    <!-- Preview -->
    <div class="form-section" id="previewSection">
        <div class="fw-semibold small mb-2" style="color:#6366f1"><i class="fas fa-eye me-1"></i>Preview</div>
        <div id="previewCard" class="ann-card ann-info" style="border-radius:12px;border:2px solid transparent;padding:1rem 1.15rem">
            <div class="ann-header" style="display:flex;align-items:flex-start;gap:.75rem">
                <div id="previewIcon" class="ann-type-icon" style="width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;background:#dbeafe">
                    <i class="fas fa-info-circle" style="color:#2563eb"></i>
                </div>
                <div>
                    <div id="previewTitle" class="fw-bold" style="color:#0f172a;font-size:.92rem">Your announcement title</div>
                    <div class="text-muted small mt-1"><i class="fas fa-user me-1"></i>You &middot; <span id="previewScope">Institution-wide</span></div>
                    <div id="previewBody" class="mt-2" style="font-size:.85rem;color:#374151;line-height:1.7">Your announcement body will appear here…</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary" style="border-radius:9px;padding:.5rem 1.5rem">
            <i class="fas fa-<?= $isEdit ? 'save' : 'bullhorn' ?> me-1"></i><?= $isEdit ? 'Save Changes' : 'Publish Announcement' ?>
        </button>
        <a href="<?= url('elms/announcements') ?>" class="btn btn-outline-secondary" style="border-radius:9px">Cancel</a>
    </div>
</form>

<script>
const TYPE_CONFIG = {
    info:    {bg:'#dbeafe', ic:'#2563eb', icon:'fas fa-info-circle',         card:'ann-info',    border:'#bfdbfe'},
    success: {bg:'#d1fae5', ic:'#059669', icon:'fas fa-check-circle',        card:'ann-success', border:'#bbf7d0'},
    warning: {bg:'#fef3c7', ic:'#d97706', icon:'fas fa-exclamation-triangle',card:'ann-warning', border:'#fde68a'},
    danger:  {bg:'#fee2e2', ic:'#dc2626', icon:'fas fa-times-circle',        card:'ann-danger',  border:'#fecdd3'},
};

function setType(t) {
    document.getElementById('typeInput').value = t;
    const cfg = TYPE_CONFIG[t];
    // Update buttons
    document.querySelectorAll('.type-btn').forEach(b => b.classList.toggle('selected', b.dataset.type===t));
    // Update preview icon
    const icon = document.getElementById('previewIcon');
    icon.style.background = cfg.bg;
    icon.innerHTML = `<i class="${cfg.icon}" style="color:${cfg.ic}"></i>`;
    // Update card border + background
    const card = document.getElementById('previewCard');
    card.style.background = cfg.bg.replace('fe','ff').replace('d1','f0').replace('fe','ff') || '#eff6ff';
    card.style.borderColor = cfg.border;
}

// Live title/body preview
document.querySelector('[name=title]').addEventListener('input', function() {
    document.getElementById('previewTitle').textContent = this.value || 'Your announcement title';
});
document.querySelector('[name=body]').addEventListener('input', function() {
    document.getElementById('previewBody').textContent = this.value || 'Your announcement body will appear here…';
});
document.querySelector('[name=course_id]').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('previewScope').textContent = opt.value ? opt.text : 'Institution-wide';
});

// Init
setType('<?= e($ann['type'] ?? 'info') ?>');
</script>
