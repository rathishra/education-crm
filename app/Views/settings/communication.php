<?php $pageTitle = 'Communication Settings'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-paper-plane me-2"></i>Communication Settings</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Settings</a></li>
                <li class="breadcrumb-item active">Communication</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link" href="<?= url('settings') ?>"><i class="fas fa-sliders-h me-1"></i>General</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="<?= url('settings/communication') ?>"><i class="fas fa-paper-plane me-1"></i>Communication</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?= url('settings/audit') ?>"><i class="fas fa-shield-alt me-1"></i>Audit Logs</a>
    </li>
</ul>

<form method="POST" action="<?= url('settings/communication') ?>">
    <?= csrfField() ?>
    <div class="row g-4">

        <!-- SMS Settings -->
        <div class="col-12">
            <div class="card">
                <div class="card-header"><i class="fas fa-sms me-2"></i>SMS Settings</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">SMS Provider</label>
                            <select class="form-select" name="sms_provider">
                                <option value="" <?= empty($settings['sms_provider']) ? 'selected' : '' ?>>— None —</option>
                                <option value="msg91" <?= ($settings['sms_provider'] ?? '') === 'msg91' ? 'selected' : '' ?>>MSG91</option>
                                <option value="twilio" <?= ($settings['sms_provider'] ?? '') === 'twilio' ? 'selected' : '' ?>>Twilio</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">API Key</label>
                            <input type="password" class="form-control" name="sms_api_key"
                                   value="<?= e($settings['sms_api_key'] ?? '') ?>"
                                   placeholder="Enter API key" autocomplete="new-password">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sender ID</label>
                            <input type="text" class="form-control" name="sms_sender_id"
                                   value="<?= e($settings['sms_sender_id'] ?? '') ?>"
                                   placeholder="e.g. MYCRM" maxlength="11">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email (SMTP) Settings -->
        <div class="col-12">
            <div class="card">
                <div class="card-header"><i class="fas fa-envelope me-2"></i>Email (SMTP) Settings</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" class="form-control" name="mail_host"
                                   value="<?= e($settings['mail_host'] ?? '') ?>" placeholder="smtp.gmail.com">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">SMTP Port</label>
                            <input type="number" class="form-control" name="mail_port"
                                   value="<?= e($settings['mail_port'] ?? 587) ?>" placeholder="587">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Encryption</label>
                            <select class="form-select" name="mail_encryption">
                                <option value="tls" <?= ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                <option value="" <?= empty($settings['mail_encryption']) ? 'selected' : '' ?>>None</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SMTP Username</label>
                            <input type="text" class="form-control" name="mail_username"
                                   value="<?= e($settings['mail_username'] ?? '') ?>" placeholder="user@gmail.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SMTP Password</label>
                            <input type="password" class="form-control" name="mail_password"
                                   value="<?= e($settings['mail_password'] ?? '') ?>"
                                   placeholder="App password" autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">From Address</label>
                            <input type="email" class="form-control" name="mail_from_address"
                                   value="<?= e($settings['mail_from_address'] ?? '') ?>" placeholder="noreply@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">From Name</label>
                            <input type="text" class="form-control" name="mail_from_name"
                                   value="<?= e($settings['mail_from_name'] ?? '') ?>" placeholder="My CRM">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WhatsApp Settings -->
        <div class="col-12">
            <div class="card">
                <div class="card-header"><i class="fab fa-whatsapp me-2"></i>WhatsApp API Settings</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">WhatsApp API URL</label>
                            <input type="url" class="form-control" name="whatsapp_api_url"
                                   value="<?= e($settings['whatsapp_api_url'] ?? '') ?>" placeholder="https://api.whatsapp-provider.com/send">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">WhatsApp API Key</label>
                            <input type="password" class="form-control" name="whatsapp_api_key"
                                   value="<?= e($settings['whatsapp_api_key'] ?? '') ?>"
                                   placeholder="Enter API key" autocomplete="new-password">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i>Save Communication Settings
            </button>
        </div>
    </div>
</form>
