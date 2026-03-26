<?php $pageTitle = 'Batch Subjects - ' . e($batch['name']); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-book me-2"></i>Batch Subjects (Regulation)</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('batches') ?>">Batches</a></li>
            <li class="breadcrumb-item active"><?= e($batch['name']) ?> — Subjects</li>
        </ol></nav>
    </div>
    <a href="<?= url('batches') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Batches</a>
</div>

<!-- Batch Info -->
<div class="alert alert-info d-flex align-items-center gap-3 mb-4">
    <i class="fas fa-layer-group fa-2x"></i>
    <div>
        <strong><?= e($batch['name']) ?></strong>
        <span class="mx-2 text-muted">|</span>
        <?= e($batch['course_name'] ?? 'N/A') ?>
        <?php if ($batch['semester']): ?>
        <span class="badge bg-secondary ms-2">Semester <?= $batch['semester'] ?></span>
        <?php endif; ?>
        <div class="small text-muted mt-1"><?= count($assigned) ?> subject(s) assigned</div>
    </div>
</div>

<div class="row g-4">
    <!-- Assigned Subjects -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list me-2"></i>Assigned Subjects
            </div>
            <div class="card-body p-0">
                <?php if (empty($assigned)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-book fa-3x mb-3 d-block"></i>
                    No subjects assigned yet. Use the form on the right to add subjects.
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Subject Name</th>
                                <th>Type</th>
                                <th class="text-center">Credits</th>
                                <th class="text-center">Semester</th>
                                <th class="text-center">Hrs/Week</th>
                                <th class="text-center">Elective</th>
                                <?php if (hasPermission('batches.edit')): ?>
                                <th></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assigned as $sub): ?>
                            <tr>
                                <td><code><?= e($sub['subject_code']) ?></code></td>
                                <td class="fw-semibold"><?= e($sub['subject_name']) ?></td>
                                <td>
                                    <?php
                                    $typeColors = ['theory' => 'primary', 'practical' => 'warning', 'project' => 'info', 'seminar' => 'secondary'];
                                    $tc = $typeColors[$sub['subject_type']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $tc ?>"><?= ucfirst($sub['subject_type']) ?></span>
                                </td>
                                <td class="text-center"><?= $sub['credits'] ?? '-' ?></td>
                                <td class="text-center">
                                    <?= $sub['semester'] ? 'Sem ' . $sub['semester'] : '<span class="text-muted">-</span>' ?>
                                </td>
                                <td class="text-center"><?= $sub['teaching_hours_per_week'] ?? '-' ?></td>
                                <td class="text-center">
                                    <?php if ($sub['is_elective']): ?>
                                    <span class="badge bg-info">Elective</span>
                                    <?php else: ?>
                                    <span class="text-muted small">Core</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (hasPermission('batches.edit')): ?>
                                <td>
                                    <form method="POST" action="<?= url('batches/' . $batch['id'] . '/subjects/' . $sub['subject_id'] . '/remove') ?>"
                                          onsubmit="return confirm('Remove this subject from the batch?')">
                                        <?= csrfField() ?>
                                        <button class="btn btn-sm btn-outline-danger" title="Remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Assign Subject Form -->
    <?php if (hasPermission('batches.edit')): ?>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-plus me-2"></i>Assign Subject</div>
            <div class="card-body">
                <?php if (empty($available)): ?>
                <div class="alert alert-success mb-0">
                    <i class="fas fa-check-circle me-2"></i>All available subjects are already assigned to this batch.
                </div>
                <?php else: ?>
                <form method="POST" action="<?= url('batches/' . $batch['id'] . '/subjects/assign') ?>">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <select class="form-select" name="subject_id" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($available as $s): ?>
                            <option value="<?= $s['id'] ?>">
                                <?= e($s['code']) ?> — <?= e($s['name']) ?> (<?= ucfirst($s['type']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <input type="number" class="form-control" name="semester"
                               placeholder="e.g. 1" min="1" max="12"
                               value="<?= $batch['semester'] ?? '' ?>">
                        <div class="form-text">Leave blank for non-semester batches.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teaching Hours / Week</label>
                        <input type="number" class="form-control" name="teaching_hours"
                               placeholder="e.g. 4" min="1" max="30">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_elective" id="isElective" value="1">
                            <label class="form-check-label" for="isElective">Mark as Elective</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-1"></i>Assign Subject
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-chart-bar me-2"></i>Summary</h6>
                <?php
                $coreCount = count(array_filter($assigned, fn($s) => !$s['is_elective']));
                $electiveCount = count($assigned) - $coreCount;
                $theoryCount = count(array_filter($assigned, fn($s) => $s['subject_type'] === 'theory'));
                $practicalCount = count(array_filter($assigned, fn($s) => $s['subject_type'] === 'practical'));
                $totalCredits = array_sum(array_column($assigned, 'credits'));
                ?>
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <div class="fs-4 fw-bold text-primary"><?= count($assigned) ?></div>
                            <div class="small text-muted">Total Subjects</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <div class="fs-4 fw-bold text-success"><?= $totalCredits ?></div>
                            <div class="small text-muted">Total Credits</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <div class="fs-4 fw-bold text-info"><?= $coreCount ?></div>
                            <div class="small text-muted">Core</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <div class="fs-4 fw-bold text-warning"><?= $electiveCount ?></div>
                            <div class="small text-muted">Elective</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
