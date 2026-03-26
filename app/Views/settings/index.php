<?php $pageTitle = 'Settings'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-cog me-2"></i>Settings</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Settings</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link active" href="<?= url('settings') ?>"><i class="fas fa-sliders-h me-1"></i>General</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?= url('settings/communication') ?>"><i class="fas fa-paper-plane me-1"></i>Communication</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?= url('settings/audit') ?>"><i class="fas fa-shield-alt me-1"></i>Audit Logs</a>
    </li>
</ul>

<form method="POST" action="<?= url('settings') ?>">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-info-circle me-2"></i>General Settings</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Application Name</label>
                            <input type="text" class="form-control" name="app_name"
                                   value="<?= e($settings['app_name'] ?? '') ?>" placeholder="My Education CRM">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">System Email</label>
                            <input type="email" class="form-control" name="app_email"
                                   value="<?= e($settings['app_email'] ?? '') ?>" placeholder="admin@example.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Records Per Page</label>
                            <input type="number" class="form-control" name="per_page"
                                   value="<?= e($settings['per_page'] ?? 15) ?>" min="5" max="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Timezone</label>
                            <select class="form-select" name="timezone">
                                <?php
                                $timezones = ['Asia/Kolkata' => 'India (IST)', 'UTC' => 'UTC', 'America/New_York' => 'US Eastern', 'America/Los_Angeles' => 'US Pacific', 'Europe/London' => 'London (GMT)', 'Asia/Dubai' => 'Dubai (GST)', 'Asia/Singapore' => 'Singapore (SGT)'];
                                foreach ($timezones as $tz => $label):
                                ?>
                                <option value="<?= $tz ?>" <?= ($settings['timezone'] ?? 'Asia/Kolkata') === $tz ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Footer Text</label>
                            <input type="text" class="form-control" name="footer_text"
                                   value="<?= e($settings['footer_text'] ?? '') ?>"
                                   placeholder="© 2024 My Institution. All rights reserved.">
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Settings</button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-lightbulb me-2"></i>Quick Links</div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="<?= url('settings/communication') ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-paper-plane me-2 text-primary"></i>Communication Settings
                        </a>
                        <a href="<?= url('settings/audit') ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-shield-alt me-2 text-warning"></i>Audit Logs
                        </a>
                        <a href="<?= url('users') ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-users-cog me-2 text-info"></i>Manage Users
                        </a>
                        <a href="<?= url('departments') ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-sitemap me-2 text-success"></i>Departments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
