<?php $pageTitle = 'Attendance Manager'; ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-dark font-weight-bold mb-0">Record Class Attendance</h4>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="GET" action="<?= url('academic/attendance/mark') ?>">
                    
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Select Cohort / Section *</label>
                        <select class="form-select form-select-lg" name="section_id" required>
                            <option value="">-- Choose Section --</option>
                            <?php if(!empty($sections)): foreach($sections as $s): ?>
                                <option value="<?= $s['section_id'] ?>"><?= e($s['program_name']) ?> (<?= e($s['batch_term']) ?>) - SECTION <?= e($s['section_name']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Academic Subject *</label>
                            <select class="form-select" name="subject_id" required>
                                <option value="">-- Choose Subject --</option>
                                <?php if(!empty($subjects)): foreach($subjects as $sub): ?>
                                    <option value="<?= $sub['id'] ?>"><?= e($sub['subject_code']) ?> - <?= e($sub['subject_name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Session Date *</label>
                            <input type="date" class="form-control" name="date" value="<?= date('Y-m-d') ?>" required max="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-2">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                            <i class="fas fa-arrow-right me-1"></i> Proceed to Mark Register
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
