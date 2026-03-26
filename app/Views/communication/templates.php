<?php $pageTitle = 'Communication Templates'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-file-alt me-2"></i>Communication Templates</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Templates</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('communication.create')): ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#templateModal">
        <i class="fas fa-plus me-1"></i>New Template
    </button>
    <?php endif; ?>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Template Name</th>
                        <th>Type</th>
                        <th>Subject</th>
                        <th class="text-center">Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($templates)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No templates found.</td></tr>
                <?php else: ?>
                    <?php foreach ($templates as $tpl): ?>
                    <?php
                    $typeBadges = ['sms' => 'bg-info', 'email' => 'bg-primary', 'whatsapp' => 'bg-success'];
                    $typeIcons  = ['sms' => 'fa-sms', 'email' => 'fa-envelope', 'whatsapp' => 'fa-whatsapp'];
                    $badgeClass = $typeBadges[$tpl['type']] ?? 'bg-secondary';
                    $iconClass  = $typeIcons[$tpl['type']] ?? 'fa-message';
                    ?>
                    <tr>
                        <td><strong><?= e($tpl['name']) ?></strong></td>
                        <td>
                            <span class="badge <?= $badgeClass ?>">
                                <i class="fab <?= $iconClass ?> me-1"></i><?= strtoupper(e($tpl['type'])) ?>
                            </span>
                        </td>
                        <td class="text-muted"><?= e($tpl['subject'] ?? '—') ?></td>
                        <td class="text-center">
                            <?php if (($tpl['status'] ?? 'active') === 'active'): ?>
                            <span class="badge bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= !empty($tpl['created_at']) ? timeAgo($tpl['created_at']) : '—' ?></td>
                        <td class="text-end">
                            <?php if (hasPermission('communication.edit')): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary edit-template-btn"
                                    data-id="<?= $tpl['id'] ?>"
                                    data-name="<?= e($tpl['name']) ?>"
                                    data-type="<?= e($tpl['type']) ?>"
                                    data-subject="<?= e($tpl['subject'] ?? '') ?>"
                                    data-content="<?= e($tpl['content'] ?? '') ?>"
                                    data-status="<?= e($tpl['status'] ?? 'active') ?>"
                                    data-bs-toggle="modal" data-bs-target="#templateModal"
                                    title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (hasPermission('communication.delete')): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="confirmDelete('<?= url('communication/templates/' . $tpl['id']) ?>')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
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

<!-- Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1" aria-labelledby="templateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="templateForm" action="<?= url('communication/templates') ?>">
                <?= csrfField() ?>
                <input type="hidden" name="_method" id="templateMethod" value="POST">
                <input type="hidden" name="template_id" id="templateId">
                <div class="modal-header">
                    <h5 class="modal-title" id="templateModalLabel"><i class="fas fa-file-alt me-2"></i>New Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label required">Template Name</label>
                            <input type="text" class="form-control" name="name" id="tplName" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Type</label>
                            <select class="form-select" name="type" id="tplType" required>
                                <option value="sms">SMS</option>
                                <option value="email">Email</option>
                                <option value="whatsapp">WhatsApp</option>
                            </select>
                        </div>
                        <div class="col-12" id="subjectField">
                            <label class="form-label">Subject <small class="text-muted">(Email only)</small></label>
                            <input type="text" class="form-control" name="subject" id="tplSubject">
                        </div>
                        <div class="col-12">
                            <label class="form-label required">Content</label>
                            <textarea class="form-control" name="content" id="tplContent" rows="5" required placeholder="Message content..."></textarea>
                            <div class="form-text">
                                <strong>Available variables:</strong>
                                <code>{student_name}</code> <code>{course_name}</code> <code>{batch_name}</code>
                                <code>{due_amount}</code> <code>{admission_date}</code> <code>{phone}</code>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="tplStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('tplType').addEventListener('change', function () {
    document.getElementById('subjectField').style.display = this.value === 'email' ? '' : 'none';
});

document.querySelectorAll('.edit-template-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const form = document.getElementById('templateForm');
        form.action = <?= json_encode(url('communication/templates/')) ?> + this.dataset.id;
        document.getElementById('templateMethod').value = 'PUT';
        document.getElementById('templateModalLabel').textContent = 'Edit Template';
        document.getElementById('templateId').value = this.dataset.id;
        document.getElementById('tplName').value = this.dataset.name;
        document.getElementById('tplType').value = this.dataset.type;
        document.getElementById('tplSubject').value = this.dataset.subject;
        document.getElementById('tplContent').value = this.dataset.content;
        document.getElementById('tplStatus').value = this.dataset.status;
        document.getElementById('subjectField').style.display = this.dataset.type === 'email' ? '' : 'none';
    });
});

document.getElementById('templateModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('templateForm');
    form.reset();
    form.action = <?= json_encode(url('communication/templates')) ?>;
    document.getElementById('templateMethod').value = 'POST';
    document.getElementById('templateModalLabel').textContent = 'New Template';
    document.getElementById('subjectField').style.display = '';
});
</script>
