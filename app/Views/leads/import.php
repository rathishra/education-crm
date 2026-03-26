<?php $pageTitle = 'Import Leads'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-file-import me-2"></i>Import Leads</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('leads') ?>">Leads</a></li>
                <li class="breadcrumb-item active">Import</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('leads') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-upload me-2"></i>Upload CSV File</div>
            <div class="card-body">
                <form method="POST" action="<?= url('leads/import') ?>" enctype="multipart/form-data">
                    <?= csrfField() ?>

                    <div class="mb-4">
                        <label class="form-label required">CSV File</label>
                        <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                        <div class="form-text">Maximum file size: 5MB. Only .csv files accepted.</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Default Lead Source</label>
                            <select class="form-select" name="default_source_id">
                                <option value="">None</option>
                                <?php foreach ($sources as $src): ?>
                                <option value="<?= $src['id'] ?>"><?= e($src['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Applied when source is not in CSV</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default Assign To</label>
                            <select class="form-select select2" name="default_assigned_to">
                                <option value="">Unassigned</option>
                                <?php foreach ($counselors as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Applied when counselor is not in CSV</div>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="skip_duplicates" value="1" id="skipDuplicates" checked>
                        <label class="form-check-label" for="skipDuplicates">
                            Skip duplicate records (matched by phone/email)
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-file-import me-1"></i>Import Leads</button>
                </form>
            </div>
        </div>

        <?php if (!empty($importResult)): ?>
        <div class="card mt-4">
            <div class="card-header"><i class="fas fa-chart-bar me-2"></i>Import Results</div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-primary mb-0"><?= $importResult['total'] ?? 0 ?></h3>
                            <small class="text-muted">Total Rows</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-success mb-0"><?= $importResult['imported'] ?? 0 ?></h3>
                            <small class="text-muted">Imported</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-warning mb-0"><?= $importResult['duplicates'] ?? 0 ?></h3>
                            <small class="text-muted">Duplicates</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-danger mb-0"><?= $importResult['errors'] ?? 0 ?></h3>
                            <small class="text-muted">Errors</small>
                        </div>
                    </div>
                </div>

                <?php if (!empty($importResult['error_details'])): ?>
                <div class="mt-3">
                    <h6>Error Details:</h6>
                    <div class="table-responsive" style="max-height:300px;overflow-y:auto">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th>Row</th><th>Error</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($importResult['error_details'] as $err): ?>
                                <tr>
                                    <td><?= $err['row'] ?></td>
                                    <td><?= e($err['message']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>CSV Format Guide</div>
            <div class="card-body">
                <p class="small">Your CSV file should have headers in the first row. Supported columns:</p>
                <table class="table table-sm small mb-3">
                    <thead class="table-light">
                        <tr><th>Column</th><th>Required</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>first_name</td><td><span class="badge bg-danger">Yes</span></td></tr>
                        <tr><td>last_name</td><td>No</td></tr>
                        <tr><td>phone</td><td><span class="badge bg-danger">Yes</span></td></tr>
                        <tr><td>alternate_phone</td><td>No</td></tr>
                        <tr><td>email</td><td>No</td></tr>
                        <tr><td>date_of_birth</td><td>No</td></tr>
                        <tr><td>gender</td><td>No</td></tr>
                        <tr><td>address_line1</td><td>No</td></tr>
                        <tr><td>address_line2</td><td>No</td></tr>
                        <tr><td>city</td><td>No</td></tr>
                        <tr><td>state</td><td>No</td></tr>
                        <tr><td>pincode</td><td>No</td></tr>
                        <tr><td>country</td><td>No</td></tr>
                        <tr><td>qualification</td><td>No</td></tr>
                        <tr><td>percentage</td><td>No</td></tr>
                        <tr><td>passing_year</td><td>No</td></tr>
                        <tr><td>school_college</td><td>No</td></tr>
                        <tr><td>priority</td><td>No</td></tr>
                        <tr><td>notes</td><td>No</td></tr>
                    </tbody>
                </table>
                <div class="alert alert-info small mb-0">
                    <i class="fas fa-lightbulb me-1"></i>
                    <strong>Tip:</strong> Export existing leads first to get a template with the correct format.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-download me-2"></i>Download Template</div>
            <div class="card-body">
                <p class="small text-muted">Download a blank CSV template with the correct headers.</p>
                <a href="<?= url('leads/export?template=1') ?>" class="btn btn-outline-primary btn-sm w-100">
                    <i class="fas fa-file-csv me-1"></i>Download Template
                </a>
            </div>
        </div>
    </div>
</div>
