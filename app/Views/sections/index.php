<?php $pageTitle = 'Sections'; ?>
<div class="page-header">
    <div>
        <h1><i class="fas fa-object-group me-2"></i>Sections</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Sections</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        <?php if (hasPermission('batches.create')): ?>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#autoGenerateModal">
            <i class="fas fa-magic me-1"></i>Auto Generate
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSectionModal">
            <i class="fas fa-plus me-1"></i>Add Section
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search section name..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-4">
                <select class="form-select form-select-sm" name="batch_id">
                    <option value="">All Batches</option>
                    <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $batchId == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
            <div class="col-auto">
                <a href="<?= url('sections') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Total: <strong><?= number_format($sections['total'] ?? 0) ?></strong> sections</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Section Name</th>
                        <th>Code</th>
                        <th>Batch</th>
                        <th>Course</th>
                        <th class="text-center">Capacity</th>
                        <th class="text-center">Strength</th>
                        <th>Room</th>
                        <th class="text-center">Status</th>
                        <?php if (hasPermission('batches.edit') || hasPermission('batches.delete')): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sections['data'])): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="fas fa-object-group fa-2x mb-2 d-block"></i>
                            No sections found. <a href="#" data-bs-toggle="modal" data-bs-target="#addSectionModal">Add one now</a>.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($sections['data'] as $i => $s): ?>
                    <tr id="section-row-<?= $s['id'] ?>">
                        <td><?= ($sections['from'] ?? 0) + $i ?></td>
                        <td class="fw-semibold"><?= e($s['name']) ?></td>
                        <td><code><?= e($s['code'] ?? '-') ?></code></td>
                        <td><?= e($s['batch_name'] ?? '-') ?></td>
                        <td><?= e($s['course_name'] ?? '-') ?></td>
                        <td class="text-center"><?= (int)($s['capacity'] ?? 0) ?></td>
                        <td class="text-center">
                            <?php
                            $strength = (int)($s['current_strength'] ?? 0);
                            $cap = (int)($s['capacity'] ?? 0);
                            $pct = $cap > 0 ? round(($strength / $cap) * 100) : 0;
                            $barClass = $pct >= 90 ? 'bg-danger' : ($pct >= 70 ? 'bg-warning' : 'bg-success');
                            ?>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-secondary"><?= $strength ?></span>
                                <?php if ($cap > 0): ?>
                                <div class="progress flex-grow-1" style="height:6px;min-width:40px">
                                    <div class="progress-bar <?= $barClass ?>" style="width:<?= $pct ?>%"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?= e($s['room_number'] ?? '-') ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $s['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($s['status']) ?>
                            </span>
                        </td>
                        <?php if (hasPermission('batches.edit') || hasPermission('batches.delete')): ?>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if (hasPermission('batches.edit')): ?>
                                <button class="btn btn-outline-primary btn-edit-section"
                                        data-id="<?= $s['id'] ?>"
                                        data-name="<?= e($s['name']) ?>"
                                        data-code="<?= e($s['code'] ?? '') ?>"
                                        data-capacity="<?= (int)($s['capacity'] ?? 60) ?>"
                                        data-room="<?= e($s['room_number'] ?? '') ?>"
                                        data-status="<?= $s['status'] ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editSectionModal"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php endif; ?>
                                <?php if (hasPermission('batches.delete')): ?>
                                <form method="POST" action="<?= url('sections/' . $s['id'] . '/delete') ?>" class="d-inline"
                                      onsubmit="return confirm('Delete section &quot;<?= e($s['name']) ?>&quot;?')">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($sections['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php
        $pagination = $sections;
        $baseUrl = url('sections') . '?' . http_build_query(array_filter(['search' => $search, 'batch_id' => $batchId]));
        include BASE_PATH . '/app/Views/partials/pagination.php';
        ?>
    </div>
    <?php endif; ?>
</div>

<!-- Add Section Modal -->
<?php if (hasPermission('batches.create')): ?>
<div class="modal fade" id="addSectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('sections') ?>">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Batch <span class="text-danger">*</span></label>
                        <select class="form-select" name="batch_id" required>
                            <option value="">Select Batch</option>
                            <?php foreach ($batches as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $batchId == $b['id'] ? 'selected' : '' ?>>
                                <?= e($b['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Section Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="e.g. Section A" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Code</label>
                            <input type="text" class="form-control text-uppercase" name="code" placeholder="e.g. A">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" name="capacity" value="60" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room Number</label>
                            <input type="text" class="form-control" name="room_number" placeholder="e.g. 101">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div class="modal fade" id="editSectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editSectionForm" action="">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Section Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="editSectionName" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Code</label>
                            <input type="text" class="form-control text-uppercase" name="code" id="editSectionCode">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" name="capacity" id="editSectionCapacity" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room Number</label>
                            <input type="text" class="form-control" name="room_number" id="editSectionRoom">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="editSectionStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Auto Generate Modal -->
<div class="modal fade" id="autoGenerateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-magic me-2"></i>Auto Generate Sections</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Automatically create sections (A, B, C...) for a batch.</p>
                <div class="mb-3">
                    <label class="form-label">Batch <span class="text-danger">*</span></label>
                    <select class="form-select" id="autoGenBatch">
                        <option value="">Select Batch</option>
                        <?php foreach ($batches as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Number of Sections (max 10)</label>
                    <input type="number" class="form-control" id="autoGenCount" value="3" min="1" max="10">
                </div>
                <div id="autoGenResult" class="alert d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="autoGenBtn">
                    <i class="fas fa-magic me-1"></i>Generate
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Populate edit modal
document.querySelectorAll('.btn-edit-section').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        document.getElementById('editSectionForm').action = '<?= url('sections') ?>/' + id;
        document.getElementById('editSectionName').value     = this.dataset.name;
        document.getElementById('editSectionCode').value     = this.dataset.code;
        document.getElementById('editSectionCapacity').value = this.dataset.capacity;
        document.getElementById('editSectionRoom').value     = this.dataset.room;
        document.getElementById('editSectionStatus').value   = this.dataset.status;
    });
});

// Auto generate
document.getElementById('autoGenBtn').addEventListener('click', function() {
    var batchId = document.getElementById('autoGenBatch').value;
    var count   = document.getElementById('autoGenCount').value;
    var result  = document.getElementById('autoGenResult');

    if (!batchId) {
        result.className = 'alert alert-warning';
        result.textContent = 'Please select a batch.';
        result.classList.remove('d-none');
        return;
    }

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generating...';

    fetch('<?= url('sections/auto-generate') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: '_token=<?= csrfToken() ?>&batch_id=' + encodeURIComponent(batchId) + '&count=' + encodeURIComponent(count)
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        result.className = 'alert alert-success';
        result.textContent = d.message;
        result.classList.remove('d-none');
        setTimeout(function() { window.location.reload(); }, 1500);
    })
    .catch(function(err) {
        result.className = 'alert alert-danger';
        result.textContent = 'An error occurred. Please try again.';
        result.classList.remove('d-none');
    })
    .finally(function() {
        document.getElementById('autoGenBtn').disabled = false;
        document.getElementById('autoGenBtn').innerHTML = '<i class="fas fa-magic me-1"></i>Generate';
    });
});
</script>
<?php endif; ?>
