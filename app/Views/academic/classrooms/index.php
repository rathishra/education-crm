<?php $pageTitle = 'Classrooms & Labs'; ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-door-open me-2 text-primary"></i>Classrooms & Labs</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Classrooms</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#roomModal" id="btnAddRoom">
            <i class="fas fa-plus me-1"></i>Add Room
        </button>
    </div>
</div>

<!-- ── STAT CARDS ─────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="fas fa-door-open text-primary"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold"><?= $stats['total'] ?></div>
                    <div class="text-muted small">Total Rooms</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold"><?= $stats['active'] ?></div>
                    <div class="text-muted small">Active</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="fas fa-users text-info"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold"><?= number_format($stats['total_capacity']) ?></div>
                    <div class="text-muted small">Total Capacity</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="fas fa-flask text-warning"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold"><?= $typeStats['lab'] ?? 0 ?></div>
                    <div class="text-muted small">Labs</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── FILTERS ────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search room number, name, location…" value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="room_type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <?php foreach (['classroom','lab','seminar_hall','auditorium','library','staff_room','office','other'] as $t): ?>
                    <option value="<?= $t ?>" <?= $typeFilter === $t ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$t)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="<?= url('academic/classrooms') ?>" class="btn btn-light btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- ── TABLE ─────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="roomsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Room</th>
                        <th>Type</th>
                        <th class="text-center">Capacity</th>
                        <th>Floor / Location</th>
                        <th>Amenities</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($classrooms)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-door-open fa-2x d-block mb-2 opacity-25"></i>
                            No rooms found. <a href="#" data-bs-toggle="modal" data-bs-target="#roomModal">Add one now.</a>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php
                    $typeColors = [
                        'classroom'    => 'primary',
                        'lab'          => 'warning',
                        'seminar_hall' => 'info',
                        'auditorium'   => 'success',
                        'library'      => 'secondary',
                        'staff_room'   => 'danger',
                        'office'       => 'dark',
                        'other'        => 'secondary',
                    ];
                    foreach ($classrooms as $r):
                        $tc = $typeColors[$r['room_type'] ?? 'classroom'] ?? 'secondary';
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold text-dark"><?= e($r['room_number']) ?></div>
                            <?php if ($r['room_name'] ?? ''): ?><div class="text-muted small"><?= e($r['room_name']) ?></div><?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $tc ?>-subtle text-<?= $tc ?> border border-<?= $tc ?>-subtle">
                                <?= ucwords(str_replace('_',' ', $r['room_type'] ?? 'classroom')) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="fw-semibold"><?= $r['capacity'] ?></span>
                            <div class="text-muted" style="font-size:.7rem">seats</div>
                        </td>
                        <td>
                            <div class="small"><?= $r['floor'] ? 'Floor '  .e($r['floor']) : '—' ?></div>
                            <div class="text-muted small"><?= e($r['location'] ?? '') ?: '' ?></div>
                        </td>
                        <td>
                            <div class="small text-muted" style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($r['amenities'] ?? '') ?>">
                                <?= e($r['amenities'] ?? '') ?: '—' ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm px-2 py-0 btn-toggle-status <?= $r['is_active'] ? 'btn-success' : 'btn-secondary' ?>"
                                    data-id="<?= $r['id'] ?>" title="Click to toggle">
                                <?= $r['is_active'] ? 'Active' : 'Inactive' ?>
                            </button>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-primary btn-edit-room me-1"
                                    data-id="<?= $r['id'] ?>" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete-room"
                                    data-id="<?= $r['id'] ?>" data-room="<?= e($r['room_number']) ?>" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── ADD / EDIT MODAL ───────────────────────────────────── -->
<div class="modal fade" id="roomModal" tabindex="-1" aria-labelledby="roomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="frmRoom" novalidate>
                <input type="hidden" id="roomId" name="room_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="roomModalLabel"><i class="fas fa-door-open me-2 text-primary"></i>Add Classroom</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="roomAlerts"></div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Room Number <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" id="roomNumber" class="form-control" placeholder="e.g. R-101" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Room Name</label>
                            <input type="text" name="room_name" id="roomName" class="form-control" placeholder="e.g. Physics Lab">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                            <select name="room_type" id="roomType" class="form-select">
                                <option value="classroom">Classroom</option>
                                <option value="lab">Lab</option>
                                <option value="seminar_hall">Seminar Hall</option>
                                <option value="auditorium">Auditorium</option>
                                <option value="library">Library</option>
                                <option value="staff_room">Staff Room</option>
                                <option value="office">Office</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Capacity <span class="text-danger">*</span></label>
                            <input type="number" name="capacity" id="roomCapacity" class="form-control" value="60" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Floor</label>
                            <input type="text" name="floor" id="roomFloor" class="form-control" placeholder="e.g. 2nd">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location / Block</label>
                            <input type="text" name="location" id="roomLocation" class="form-control" placeholder="e.g. Block B, Science Wing">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Amenities</label>
                            <input type="text" name="amenities" id="roomAmenities" class="form-control" placeholder="e.g. AC, Projector, Whiteboard, WiFi">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" id="roomDescription" class="form-control" rows="2" placeholder="Optional notes about this room"></textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="roomActive" value="1" checked>
                                <label class="form-check-label" for="roomActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveRoom">
                        <i class="fas fa-save me-1"></i>Save Room
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal    = new bootstrap.Modal(document.getElementById('roomModal'));
    const form     = document.getElementById('frmRoom');
    const roomId   = document.getElementById('roomId');
    const alertBox = document.getElementById('roomAlerts');
    const modalTitle = document.getElementById('roomModalLabel');

    function showAlert(type, msg) {
        alertBox.innerHTML = `<div class="alert alert-${type} py-2 mb-3">${msg}</div>`;
    }
    function clearAlert() { alertBox.innerHTML = ''; }

    // Reset form for Add
    document.getElementById('btnAddRoom').addEventListener('click', function () {
        form.reset();
        roomId.value = '';
        document.getElementById('roomActive').checked = true;
        modalTitle.innerHTML = '<i class="fas fa-plus me-2 text-primary"></i>Add Classroom';
        clearAlert();
    });

    // Edit button
    document.querySelectorAll('.btn-edit-room').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            clearAlert();
            fetch(`<?= url('academic/classrooms') ?>/${id}/json`)
                .then(r => r.json())
                .then(res => {
                    if (res.status !== 'success') { alert(res.message); return; }
                    const d = res.data;
                    roomId.value = d.id;
                    document.getElementById('roomNumber').value    = d.room_number;
                    document.getElementById('roomName').value      = d.room_name || '';
                    document.getElementById('roomType').value      = d.room_type || 'classroom';
                    document.getElementById('roomCapacity').value  = d.capacity;
                    document.getElementById('roomFloor').value     = d.floor || '';
                    document.getElementById('roomLocation').value  = d.location || '';
                    document.getElementById('roomAmenities').value = d.amenities || '';
                    document.getElementById('roomDescription').value = d.description || '';
                    document.getElementById('roomActive').checked  = d.is_active == 1;
                    modalTitle.innerHTML = '<i class="fas fa-edit me-2 text-warning"></i>Edit Classroom';
                    modal.show();
                })
                .catch(() => alert('Failed to load room data.'));
        });
    });

    // Form submit (add or edit)
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearAlert();
        const fd  = new FormData(form);
        const id  = roomId.value;
        const url = id
            ? `<?= url('academic/classrooms') ?>/${id}/update`
            : `<?= url('academic/classrooms/store') ?>`;

        document.getElementById('btnSaveRoom').disabled = true;

        fetch(url, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                document.getElementById('btnSaveRoom').disabled = false;
                if (res.status === 'success') {
                    modal.hide();
                    location.reload();
                } else {
                    const errs = res.errors ? Object.values(res.errors).join('<br>') : res.message;
                    showAlert('danger', errs);
                }
            })
            .catch(() => {
                document.getElementById('btnSaveRoom').disabled = false;
                showAlert('danger', 'Network error. Please try again.');
            });
    });

    // Toggle status
    document.querySelectorAll('.btn-toggle-status').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const self = this;
            fetch(`<?= url('academic/classrooms') ?>/${id}/toggle`, { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        if (res.is_active) {
                            self.textContent = 'Active';
                            self.className = 'btn btn-sm px-2 py-0 btn-success btn-toggle-status';
                        } else {
                            self.textContent = 'Inactive';
                            self.className = 'btn btn-sm px-2 py-0 btn-secondary btn-toggle-status';
                        }
                    }
                });
        });
    });

    // Delete
    document.querySelectorAll('.btn-delete-room').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const room = this.dataset.room;
            if (!confirm(`Delete room "${room}"? This cannot be undone.`)) return;
            fetch(`<?= url('academic/classrooms') ?>/${id}/delete`, { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        this.closest('tr').remove();
                    } else {
                        alert(res.message);
                    }
                });
        });
    });

    // DataTable
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#roomsTable').DataTable({ paging: true, searching: false, info: false, order: [] });
    }
});
</script>
