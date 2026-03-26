<?php $pageTitle = 'Bulk Communication'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-bullhorn me-2"></i>Bulk Communication</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Bulk Communication</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('communication/log') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-history me-1"></i>View Log
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-paper-plane me-2"></i>Compose Message</div>
            <div class="card-body">
                <form method="POST" action="<?= url('communication/send-bulk') ?>" id="bulkForm">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Communication Type</label>
                            <select class="form-select" name="type" id="commType" required>
                                <option value="sms" <?= old('type') === 'sms' ? 'selected' : '' ?>>
                                    <i class="fas fa-sms"></i> SMS
                                </option>
                                <option value="email" <?= old('type') === 'email' ? 'selected' : '' ?>>Email</option>
                                <option value="whatsapp" <?= old('type') === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Target Group</label>
                            <select class="form-select" name="target_group" id="targetGroup" required>
                                <option value="all_students" <?= old('target_group') === 'all_students' ? 'selected' : '' ?>>All Students</option>
                                <option value="course" <?= old('target_group') === 'course' ? 'selected' : '' ?>>By Course</option>
                                <option value="batch" <?= old('target_group') === 'batch' ? 'selected' : '' ?>>By Batch</option>
                                <option value="due_students" <?= old('target_group') === 'due_students' ? 'selected' : '' ?>>Students with Dues</option>
                            </select>
                        </div>

                        <!-- Conditional Course/Batch select -->
                        <div class="col-md-6 d-none" id="courseSelectWrapper">
                            <label class="form-label required">Select Course</label>
                            <select class="form-select select2" name="course_id" id="bulkCourseSelect">
                                <option value="">— Select Course —</option>
                                <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>" <?= old('course_id') == $course['id'] ? 'selected' : '' ?>>
                                    <?= e($course['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 d-none" id="batchSelectWrapper">
                            <label class="form-label">Select Batch</label>
                            <select class="form-select" name="batch_id" id="bulkBatchSelect">
                                <option value="">— All Batches —</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Use Template</label>
                            <select class="form-select" name="template_id" id="templateSelect">
                                <option value="">— No template —</option>
                                <?php foreach ($templates as $tpl): ?>
                                <option value="<?= $tpl['id'] ?>"
                                        data-content="<?= e($tpl['content'] ?? '') ?>"
                                        data-type="<?= e($tpl['type']) ?>"
                                        <?= old('template_id') == $tpl['id'] ? 'selected' : '' ?>>
                                    <?= e($tpl['name']) ?> (<?= strtoupper($tpl['type']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Template Preview -->
                        <div class="col-12 d-none" id="templatePreviewBox">
                            <div class="alert alert-info mb-0">
                                <strong>Template Preview:</strong>
                                <div id="templatePreviewContent" class="mt-1 text-muted small"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label required">Message</label>
                            <textarea class="form-control" name="message" id="bulkMessage" rows="5" required
                                      placeholder="Type your message here..."><?= e(old('message')) ?></textarea>
                            <div class="form-text">
                                Variables: <code>{student_name}</code> <code>{course_name}</code> <code>{batch_name}</code> <code>{phone}</code>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                        <button type="reset" class="btn btn-outline-secondary ms-2">Clear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Tips</div>
            <div class="card-body">
                <ul class="list-unstyled small">
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Use templates to save time.</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Variables are replaced with actual student data.</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>SMS: keep under 160 characters for single SMS.</li>
                    <li class="mb-2"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Large sends may take a few minutes.</li>
                </ul>
                <hr>
                <a href="<?= url('communication/templates') ?>" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="fas fa-file-alt me-1"></i>Manage Templates
                </a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const targetGroup = document.getElementById('targetGroup');
    const courseWrapper = document.getElementById('courseSelectWrapper');
    const batchWrapper = document.getElementById('batchSelectWrapper');
    const templateSelect = document.getElementById('templateSelect');
    const previewBox = document.getElementById('templatePreviewBox');
    const previewContent = document.getElementById('templatePreviewContent');
    const bulkMessage = document.getElementById('bulkMessage');
    const bulkCourseSelect = document.getElementById('bulkCourseSelect');
    const bulkBatchSelect = document.getElementById('bulkBatchSelect');

    targetGroup.addEventListener('change', function () {
        const val = this.value;
        courseWrapper.classList.toggle('d-none', val !== 'course' && val !== 'batch');
        batchWrapper.classList.toggle('d-none', val !== 'batch');
    });

    templateSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const content = opt.dataset.content || '';
        if (content) {
            bulkMessage.value = content;
            previewContent.textContent = content;
            previewBox.classList.remove('d-none');
        } else {
            previewBox.classList.add('d-none');
        }
    });

    bulkCourseSelect.addEventListener('change', function () {
        const courseId = this.value;
        bulkBatchSelect.innerHTML = '<option value="">— All Batches —</option>';
        if (!courseId) return;
        fetch(<?= json_encode(url('api/batches-by-course')) ?> + '?course_id=' + courseId)
            .then(r => r.json())
            .then(data => {
                data.forEach(function (b) {
                    const opt = document.createElement('option');
                    opt.value = b.id;
                    opt.textContent = b.name;
                    bulkBatchSelect.appendChild(opt);
                });
            }).catch(() => {});
    });
})();
</script>
