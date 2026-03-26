<?php $pageTitle = 'Add Book to Library'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-plus me-2"></i>Add Book</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('library') ?>">Library</a></li>
                <li class="breadcrumb-item active">Add Book</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('library') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url('library') ?>">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">Book Title</label>
                    <input type="text" class="form-control" name="title" value="<?= e(old('title')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Author(s)</label>
                    <input type="text" class="form-control" name="author" value="<?= e(old('author')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ISBN</label>
                    <input type="text" class="form-control" name="isbn" value="<?= e(old('isbn')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Publisher</label>
                    <input type="text" class="form-control" name="publisher" value="<?= e(old('publisher')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category / Subject</label>
                    <input type="text" class="form-control" name="category" value="<?= e(old('category')) ?>" list="categoriesList">
                    <datalist id="categoriesList">
                        <option value="Computer Science">
                        <option value="Mathematics">
                        <option value="Physics">
                        <option value="Chemistry">
                        <option value="Literature">
                        <option value="Engineering">
                        <option value="Medical">
                        <option value="Business & Management">
                    </datalist>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Total Copies</label>
                    <input type="number" class="form-control" name="total_copies" value="<?= e(old('total_copies', '1')) ?>" min="1" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Book</button>
                    <a href="<?= url('library') ?>" class="btn btn-light ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
