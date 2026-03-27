<?php $pageTitle = 'Timetable Management'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-dark font-weight-bold mb-0">Academic Timetable Module</h4>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h6 class="text-primary fw-bold mb-3">Select a Class Section to Schedule</h6>
        
        <div class="row">
            <?php if(!empty($sections)): foreach($sections as $s): ?>
            <div class="col-md-4 mb-3">
                <div class="card bg-light border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="fw-bold mb-1 text-dark"><?= e($s['program_name']) ?></h6>
                        <p class="text-muted small mb-2 text-uppercase"><?= e($s['batch_term']) ?></p>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-secondary px-3 py-2">SECTION: <?= e($s['section_name']) ?></span>
                            <a href="<?= url('academic/timetable/generator?section_id='.$s['id']) ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-calendar-plus me-1"></i> Generate
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div class="col-12 py-5 text-center text-muted">
                <i class="fas fa-folder-open fa-3x mb-3 text-light"></i>
                <p>No active Class Sections found. Please create Cohorts and Sections first.</p>
                <a href="<?= url('academic/sections/create') ?>" class="btn btn-primary mt-2">Create Section</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
