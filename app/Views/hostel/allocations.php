<?php $pageTitle = 'Hostel Allocations'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-bed me-2"></i>Hostel Allocations</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('hostels') ?>">Hostels</a></li>
                <li class="breadcrumb-item active">Allocations</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('hostel.allocate')): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#allocateModal"><i class="fas fa-plus me-1"></i> New Allocation</button>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Hostel</th>
                        <th>Room No.</th>
                        <th>Status</th>
                        <?php if (hasPermission('hostel.manage')): ?><th class="text-end">Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allocations)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No allocations found for this academic year.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allocations as $a): ?>
                        <tr>
                            <td><?= formatDate($a['start_date']) ?></td>
                            <td>
                                <div class="fw-semibold">
                                    <a href="<?= url('students/'.$a['student_id']) ?>"><?= e($a['first_name'] . ' ' . $a['last_name']) ?></a>
                                </div>
                                <code class="small text-muted"><?= e($a['student_id_number']) ?></code>
                            </td>
                            <td><?= e($a['hostel_name']) ?></td>
                            <td class="fw-bold"><?= e($a['room_number']) ?></td>
                            <td>
                                <span class="badge bg-<?= $a['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($a['status']) ?>
                                </span>
                            </td>
                            <?php if (hasPermission('hostel.manage')): ?>
                            <td class="text-end">
                                <!-- Future functionality: Edit/Vacate -->
                                <button class="btn btn-sm btn-outline-secondary" disabled title="Edit Allocation"><i class="fas fa-edit"></i></button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Allocate Modal -->
<div class="modal fade" id="allocateModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('hostels/allocations') ?>" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title">Allocate Hostel Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small">
                    <i class="fas fa-info-circle me-1"></i> Only rooms with available beds will be shown.
                </div>
                
                <div class="mb-3">
                    <label class="form-label required">Student ID Lookup</label>
                    <input type="number" class="form-control" name="student_id" placeholder="Enter strictly the Student DB ID for now" required>
                    <small class="text-muted">In a full UI, this would be an AJAX searchable dropdown finding active students.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label required">Hostel & Room</label>
                    <select class="form-select" name="hostel_room_id" required>
                        <option value="">-- Select Room --</option>
                        <?php foreach ($hostels as $h): ?>
                            <optgroup label="<?= e($h['name']) ?> (<?= ucfirst($h['type']) ?>)">
                                <?php 
                                $rooms = db()->query("SELECT id, room_number, available_beds FROM hostel_rooms WHERE hostel_id = ? AND status = 'active' AND available_beds > 0 ORDER BY room_number", [$h['id']])->fetchAll();
                                foreach($rooms as $r):
                                ?>
                                    <option value="<?= $r['id'] ?>">Room <?= e($r['room_number']) ?> (<?= e($r['available_beds']) ?> beds left)</option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label required">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i>Allocate</button>
            </div>
        </form>
    </div>
</div>
