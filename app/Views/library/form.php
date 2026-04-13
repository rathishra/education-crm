<?php $pageTitle = ($editMode ? 'Edit Book' : 'Add Book') . ' — Library'; ?>

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="h3 mb-1">
            <i class="fas fa-<?= $editMode ? 'edit' : 'plus-circle' ?> me-2 text-primary"></i>
            <?= $editMode ? 'Edit Book' : 'Add New Book' ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('library') ?>">Library</a></li>
                <li class="breadcrumb-item active"><?= $editMode ? 'Edit' : 'Add Book' ?></li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('library') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<?php $errors = getFlash('errors') ?? []; ?>
<?php if ($errors): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i>
    <strong>Please fix the following:</strong>
    <ul class="mb-0 mt-1">
        <?php foreach ((array)$errors as $e): ?>
        <li><?= e($e) ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST"
      action="<?= $editMode ? url("library/{$book['id']}/edit") : url('library') ?>">
    <?= csrfField() ?>

    <div class="row g-4">
        <!-- ── Main Details ─────────────────────────────────────── -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold">
                    <i class="fas fa-book me-2 text-primary"></i>Book Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                                   name="title" value="<?= e(old('title') ?? ($book['title'] ?? '')) ?>"
                                   placeholder="Book title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Accession No. <span class="text-danger">*</span></label>
                            <input type="text" class="form-control"
                                   name="accession_number"
                                   value="<?= e(old('accession_number') ?? ($book['accession_number'] ?? '')) ?>"
                                   placeholder="e.g. LIB-0001" required>
                            <div class="form-text">Unique identifier for this copy.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Author <span class="text-danger">*</span></label>
                            <input type="text" class="form-control"
                                   name="author" value="<?= e(old('author') ?? ($book['author'] ?? '')) ?>"
                                   placeholder="Author name(s)" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">ISBN</label>
                            <input type="text" class="form-control"
                                   name="isbn" value="<?= e(old('isbn') ?? ($book['isbn'] ?? '')) ?>"
                                   placeholder="978-3-16-148410-0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Publisher</label>
                            <input type="text" class="form-control"
                                   name="publisher" value="<?= e(old('publisher') ?? ($book['publisher'] ?? '')) ?>"
                                   placeholder="Publisher name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Edition</label>
                            <input type="text" class="form-control"
                                   name="edition" value="<?= e(old('edition') ?? ($book['edition'] ?? '')) ?>"
                                   placeholder="3rd">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Publication Year</label>
                            <input type="number" class="form-control"
                                   name="publication_year"
                                   value="<?= e(old('publication_year') ?? ($book['publication_year'] ?? '')) ?>"
                                   min="1800" max="<?= date('Y') + 1 ?>" placeholder="<?= date('Y') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Category</label>
                            <input type="text" class="form-control" name="category" list="categoryList"
                                   value="<?= e(old('category') ?? ($book['category'] ?? '')) ?>"
                                   placeholder="e.g. Science, Literature, Reference">
                            <datalist id="categoryList">
                                <?php
                                $commonCategories = ['Science','Mathematics','Literature','History','Geography','Physics','Chemistry','Biology','Computer Science','Engineering','Medical','Law','Commerce','Arts','Reference','Periodicals','Fiction','Non-Fiction'];
                                foreach ($commonCategories as $cat): ?>
                                <option value="<?= $cat ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Language</label>
                            <select class="form-select" name="language">
                                <?php
                                $languages = ['English','Hindi','Tamil','Telugu','Malayalam','Kannada','Marathi','Bengali','Gujarati','Urdu','Arabic','French'];
                                $curLang = old('language') ?? ($book['language'] ?? 'English');
                                foreach ($languages as $lang):
                                ?>
                                <option value="<?= $lang ?>" <?= $curLang === $lang ? 'selected' : '' ?>><?= $lang ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Shelf / Rack Location</label>
                            <input type="text" class="form-control"
                                   name="shelf_location"
                                   value="<?= e(old('shelf_location') ?? ($book['shelf_location'] ?? '')) ?>"
                                   placeholder="e.g. A-2, Row 3">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Description / Notes</label>
                            <textarea class="form-control" name="description" rows="3"
                                      placeholder="Brief description, subjects covered, notes…"><?= e(old('description') ?? ($book['description'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Inventory & Status ────────────────────────────────── -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold">
                    <i class="fas fa-cubes me-2 text-success"></i>Inventory
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Total Copies <span class="text-danger">*</span></label>
                        <input type="number" class="form-control"
                               name="quantity"
                               value="<?= e(old('quantity') ?? ($book['quantity'] ?? 1)) ?>"
                               min="1" max="9999" required>
                        <?php if ($editMode && isset($book['issued_count']) && $book['issued_count'] > 0): ?>
                        <div class="form-text text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <?= $book['issued_count'] ?> cop<?= $book['issued_count'] > 1 ? 'ies' : 'y' ?> currently issued.
                            Minimum quantity = <?= $book['issued_count'] ?>.
                        </div>
                        <?php else: ?>
                        <div class="form-text">Number of physical copies owned.</div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Price (per copy)</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" name="price" step="0.01" min="0"
                                   value="<?= e(old('price') ?? ($book['price'] ?? '0.00')) ?>">
                        </div>
                    </div>
                    <?php if ($editMode): ?>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Status</label>
                        <select class="form-select" name="status">
                            <option value="active"   <?= ($book['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($book['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($editMode): ?>
            <div class="card border-0 shadow-sm mt-3 bg-light">
                <div class="card-body small text-muted">
                    <p class="mb-1"><strong>Accession:</strong> <?= e($book['accession_number']) ?></p>
                    <p class="mb-1"><strong>Added:</strong> <?= date('d M Y', strtotime($book['created_at'])) ?></p>
                    <p class="mb-0">
                        <strong>Available:</strong>
                        <span class="badge bg-<?= (int)$book['available_quantity'] > 0 ? 'success' : 'danger' ?>">
                            <?= $book['available_quantity'] ?> / <?= $book['quantity'] ?>
                        </span>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i><?= $editMode ? 'Save Changes' : 'Add Book' ?>
                </button>
                <a href="<?= url('library') ?>" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>
