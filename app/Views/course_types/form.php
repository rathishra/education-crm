<?php
$isEdit    = !is_null($courseType);
$pageTitle = $isEdit ? 'Update Course Type' : 'Add Course Type';
$action    = $isEdit ? url("course-types/{$courseType['id']}") : url('course-types');

$v = function (string $key, $default = '') use ($courseType) {
    $old = getFlash('old_input.' . $key, null);
    if ($old !== null) return $old;
    return $courseType[$key] ?? $default;
};

// Build existing year overrides for JS
$existingYears = [];
if ($isEdit && !empty($years)) {
    foreach ($years as $yr) {
        $existingYears[(int)$yr['year_code']] = (int)$yr['no_of_semester'];
    }
}
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-graduation-cap me-2"></i><?= $pageTitle ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('course-types') ?>">Course Types</a></li>
                <li class="breadcrumb-item active"><?= $isEdit ? 'Update' : 'Add' ?></li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('course-types') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<!-- Errors -->
<?php $formErrors = (array)(getFlash('errors') ?? []); ?>
<?php if (!empty($formErrors)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <strong><i class="fas fa-exclamation-triangle me-1"></i>Please fix the errors below:</strong>
    <ul class="mb-0 mt-1 ps-3">
        <?php foreach ($formErrors as $fe): ?><li><?= e(is_array($fe) ? implode(', ', $fe) : $fe) ?></li><?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" action="<?= $action ?>" id="ctForm">
    <?= csrfField() ?>

    <div class="card shadow-sm">
        <div class="card-header py-3" style="background:#2c3e8c;">
            <h5 class="mb-0 text-white fw-semibold">
                <i class="fas fa-graduation-cap me-2"></i><?= $pageTitle ?>
            </h5>
        </div>
        <div class="card-body px-4 py-4">

            <!-- Code -->
            <div class="row mb-3 align-items-center">
                <label class="col-md-2 col-form-label fw-semibold text-end">
                    <span class="text-danger me-1">*</span>Code :
                </label>
                <div class="col-md-7">
                    <input type="text" name="code" class="form-control"
                           value="<?= e($v('code')) ?>"
                           placeholder="e.g. B.Tech"
                           maxlength="50" required
                           style="text-transform:uppercase"
                           oninput="this.value=this.value.toUpperCase()">
                </div>
            </div>

            <!-- Description -->
            <div class="row mb-3 align-items-center">
                <label class="col-md-2 col-form-label fw-semibold text-end">
                    <span class="text-danger me-1">*</span>Description :
                </label>
                <div class="col-md-7">
                    <input type="text" name="description" class="form-control"
                           value="<?= e($v('description')) ?>"
                           placeholder="e.g. Bachelor of Technology"
                           maxlength="255" required>
                </div>
            </div>

            <!-- Short Description -->
            <div class="row mb-3 align-items-center">
                <label class="col-md-2 col-form-label fw-semibold text-end">
                    Short Description :
                </label>
                <div class="col-md-7">
                    <input type="text" name="short_description" class="form-control"
                           value="<?= e($v('short_description')) ?>"
                           placeholder="Short Description"
                           maxlength="255">
                </div>
            </div>

            <!-- Course Category -->
            <div class="row mb-3 align-items-start">
                <label class="col-md-2 col-form-label fw-semibold text-end pt-2">
                    <span class="text-danger me-1">*</span>Course Category :
                </label>
                <div class="col-md-10">
                    <?php
                    $categories = [
                        'certificate'      => 'Certificate Course',
                        'ug'               => 'Under Graduate',
                        'pg'               => 'Post Graduate',
                        'school'           => 'School',
                        'research_scholar' => 'Research Scholar',
                        'mphil'            => 'M.Phil',
                        'phd'              => 'Phd',
                    ];
                    $selCat = $v('course_category', 'ug');
                    ?>
                    <div class="d-flex flex-wrap gap-3 pt-2">
                        <?php foreach ($categories as $val => $label): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                   name="course_category"
                                   id="cat_<?= $val ?>"
                                   value="<?= $val ?>"
                                   <?= $selCat === $val ? 'checked' : '' ?>
                                   required>
                            <label class="form-check-label" for="cat_<?= $val ?>"><?= $label ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Degree Type -->
            <div class="row mb-3 align-items-center">
                <label class="col-md-2 col-form-label fw-semibold text-end">
                    <span class="text-danger me-1">*</span>Degree Type :
                </label>
                <div class="col-md-10">
                    <?php $selDeg = $v('degree_type', 'full_time'); ?>
                    <div class="d-flex gap-4 pt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="degree_type" id="deg_full" value="full_time" <?= $selDeg === 'full_time' ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="deg_full">Full Time</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="degree_type" id="deg_part" value="part_time" <?= $selDeg === 'part_time' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="deg_part">Part Time</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Duration -->
            <div class="row mb-3 align-items-center">
                <label class="col-md-2 col-form-label fw-semibold text-end">
                    <span class="text-danger me-1">*</span>Duration :
                </label>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="number" name="duration" id="durationInput" class="form-control"
                               value="<?= e($v('duration', 3)) ?>"
                               min="1" max="10" required>
                        <select name="duration_unit" id="durationUnit" class="form-select" style="max-width:120px">
                            <option value="year"  <?= $v('duration_unit', 'year') === 'year'  ? 'selected' : '' ?>>Year</option>
                            <option value="month" <?= $v('duration_unit', 'year') === 'month' ? 'selected' : '' ?>>Month</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- No. of Semester -->
            <div class="row mb-4 align-items-center">
                <label class="col-md-2 col-form-label fw-semibold text-end">
                    <span class="text-danger me-1">*</span>No. of Semester :
                </label>
                <div class="col-md-2">
                    <input type="number" name="no_of_semester" id="semInput" class="form-control"
                           value="<?= e($v('no_of_semester', 2)) ?>"
                           min="1" max="12" required>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Semesters per year</small>
                </div>
            </div>

            <!-- ── Year / Semester two-panel table ── -->
            <div class="row g-0 border rounded overflow-hidden" id="yearSemSection">
                <!-- LEFT: Year table -->
                <div class="col-md-6 border-end">
                    <div class="px-3 py-2 fw-semibold" style="background:#e9ecef;border-bottom:1px solid #dee2e6;">
                        Year
                    </div>
                    <div id="yearTableWrap">
                        <table class="table table-sm table-bordered mb-0" id="yearTable">
                            <thead style="background:#f8f9fa;">
                                <tr>
                                    <th class="ps-3">Code</th>
                                    <th>No. of Semester</th>
                                </tr>
                            </thead>
                            <tbody id="yearTableBody">
                                <!-- filled by JS -->
                            </tbody>
                        </table>
                        <div id="yearEmpty" class="text-center text-muted small py-3 d-none">
                            Set duration in <strong>Year</strong> unit to see year breakdown.
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Semester table -->
                <div class="col-md-6">
                    <div class="px-3 py-2 fw-semibold" style="background:#e9ecef;border-bottom:1px solid #dee2e6;">
                        Semester
                    </div>
                    <div id="semTableWrap">
                        <table class="table table-sm table-bordered mb-0" id="semTable">
                            <thead style="background:#f8f9fa;">
                                <tr>
                                    <th class="ps-3">Semester No.</th>
                                    <th>Year</th>
                                </tr>
                            </thead>
                            <tbody id="semTableBody">
                                <!-- filled by JS -->
                            </tbody>
                        </table>
                        <div id="semEmpty" class="text-center text-muted small py-3 d-none">
                            No course type semester.
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /card-body -->

        <!-- Footer buttons -->
        <div class="card-footer d-flex gap-2 py-3 px-4">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i><?= $isEdit ? 'Update' : 'Save' ?>
            </button>
            <a href="<?= url('course-types') ?>" class="btn btn-outline-secondary">
                Back
            </a>
        </div>
    </div>
</form>

<script>
const existingYears = <?= json_encode($existingYears) ?>;

function buildTables() {
    const duration = parseInt(document.getElementById('durationInput').value) || 0;
    const unit     = document.getElementById('durationUnit').value;
    const defSem   = parseInt(document.getElementById('semInput').value) || 2;

    const yearBody  = document.getElementById('yearTableBody');
    const semBody   = document.getElementById('semTableBody');
    const yearTable = document.getElementById('yearTable');
    const semTable  = document.getElementById('semTable');
    const yearEmpty = document.getElementById('yearEmpty');
    const semEmpty  = document.getElementById('semEmpty');

    yearBody.innerHTML = '';
    semBody.innerHTML  = '';

    if (unit !== 'year' || duration < 1) {
        yearTable.classList.add('d-none');
        semTable.classList.add('d-none');
        yearEmpty.classList.remove('d-none');
        semEmpty.classList.remove('d-none');
        return;
    }

    yearTable.classList.remove('d-none');
    semTable.classList.remove('d-none');
    yearEmpty.classList.add('d-none');
    semEmpty.classList.add('d-none');

    let semCounter = 1;

    for (let y = 1; y <= duration; y++) {
        // Preserve previously entered override
        const existingInput = document.querySelector(`[name="year_semesters[${y}]"]`);
        let semCount = existingInput
            ? (parseInt(existingInput.value) || defSem)
            : (existingYears[y] !== undefined ? existingYears[y] : defSem);

        // Year row
        const yr = document.createElement('tr');
        yr.innerHTML = `
            <td class="ps-3 text-danger fw-semibold">${y}</td>
            <td>
                <input type="number" name="year_semesters[${y}]"
                       class="form-control form-control-sm year-sem-input"
                       value="${semCount}" min="1" max="12"
                       style="width:80px" data-year="${y}">
            </td>`;
        yearBody.appendChild(yr);

        // Semester rows
        for (let s = 1; s <= semCount; s++) {
            const sr = document.createElement('tr');
            sr.innerHTML = `<td class="ps-3 text-danger fw-semibold">${semCounter}</td><td>Year ${y}</td>`;
            semBody.appendChild(sr);
            semCounter++;
        }
    }

    // Re-attach listeners for year-sem inputs
    document.querySelectorAll('.year-sem-input').forEach(inp => {
        inp.addEventListener('input', rebuildSemTable);
    });
}

function rebuildSemTable() {
    const duration = parseInt(document.getElementById('durationInput').value) || 0;
    const semBody  = document.getElementById('semTableBody');
    semBody.innerHTML = '';
    let semCounter = 1;
    for (let y = 1; y <= duration; y++) {
        const inp  = document.querySelector(`[name="year_semesters[${y}]"]`);
        const cnt  = inp ? (parseInt(inp.value) || 1) : (parseInt(document.getElementById('semInput').value) || 2);
        for (let s = 1; s <= cnt; s++) {
            const sr = document.createElement('tr');
            sr.innerHTML = `<td class="ps-3 text-danger fw-semibold">${semCounter}</td><td>Year ${y}</td>`;
            semBody.appendChild(sr);
            semCounter++;
        }
    }
}

// Trigger rebuild on any change
document.getElementById('durationInput').addEventListener('input',  buildTables);
document.getElementById('durationUnit').addEventListener('change',  buildTables);
document.getElementById('semInput').addEventListener('input',       buildTables);

// Initial render
buildTables();
</script>
