<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= url('elms/forum'.($courseId?"?course={$courseId}":'')) ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-pen me-2 text-primary"></i>Start a Discussion</h4>
</div>

<?php $errs = flash('errors', null) ?? []; ?>
<?php if (!empty($errs)): ?>
<div class="alert alert-danger py-2 small"><?= implode('<br>', array_map('e', is_array($errs)?$errs:[$errs])) ?></div>
<?php endif; ?>

<div style="max-width:760px">
<form method="POST" action="<?= url('elms/forum/store') ?>">
    <?= csrfField() ?>

    <div class="bg-white rounded-3 border p-4 mb-3" style="border-color:#e8e3ff!important">

        <div class="mb-3">
            <label class="form-label fw-semibold small">Thread Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" required maxlength="255"
                   placeholder="Ask a clear question or describe your topic…"
                   value="<?= e(flash('old_title', '')) ?>">
            <div class="text-muted" style="font-size:.7rem;margin-top:.25rem">Be specific — good titles get better answers faster</div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold small">Course <span class="text-muted small fw-normal">(optional)</span></label>
                <select name="course_id" class="form-select" id="courseSelect" onchange="loadCategories()">
                    <option value="">General / All Courses</option>
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $courseId==$c['id']?'selected':'' ?>><?= e($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small">Category <span class="text-muted small fw-normal">(optional)</span></label>
                <select name="category_id" class="form-select" id="catSelect">
                    <option value="">— Select Category —</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-1">
            <label class="form-label fw-semibold small">Your Message <span class="text-danger">*</span></label>
            <!-- Simple toolbar -->
            <div class="d-flex gap-1 mb-1 flex-wrap">
                <?php foreach ([['fas fa-bold','**','**','Bold'],['fas fa-italic','_','_','Italic'],['fas fa-code','`','`','Inline code'],['fas fa-quote-left','> ','','Quote']] as [$icon,$pre,$suf,$tip]): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius:6px;font-size:.72rem;padding:.2rem .5rem" title="<?= $tip ?>"
                        onclick="wrapText('<?= $pre ?>','<?= $suf ?>')"><i class="<?= $icon ?>"></i></button>
                <?php endforeach; ?>
            </div>
            <textarea name="body" id="bodyTA" class="form-control" rows="9" required
                      placeholder="Describe your question in detail. Include any relevant context, code, or error messages…"></textarea>
            <div class="text-muted" style="font-size:.7rem;margin-top:.25rem">Markdown supported: **bold**, _italic_, `code`, > quote</div>
        </div>
    </div>

    <div class="d-flex gap-2 align-items-center">
        <button type="submit" class="btn btn-primary" style="border-radius:9px"><i class="fas fa-paper-plane me-2"></i>Post Thread</button>
        <a href="<?= url('elms/forum') ?>" class="btn btn-outline-secondary" style="border-radius:9px">Cancel</a>
    </div>
</form>
</div>

<script>
function wrapText(pre, suf) {
    const ta    = document.getElementById('bodyTA');
    const start = ta.selectionStart;
    const end   = ta.selectionEnd;
    const sel   = ta.value.substring(start, end);
    ta.value    = ta.value.substring(0, start) + pre + sel + suf + ta.value.substring(end);
    ta.focus();
    ta.setSelectionRange(start + pre.length, end + pre.length);
}

function loadCategories() {
    const courseId = document.getElementById('courseSelect').value;
    const sel      = document.getElementById('catSelect');
    if (!courseId) { sel.innerHTML = '<option value="">— Select Category —</option>'; return; }
    fetch(`<?= url('elms/forum/categories') ?>?course_id=${courseId}`, {
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => r.json())
    .then(data => {
        sel.innerHTML = '<option value="">— Select Category —</option>';
        (data.categories || []).forEach(cat => {
            sel.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
        });
    })
    .catch(() => {});
}
</script>
