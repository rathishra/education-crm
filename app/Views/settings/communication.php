<?php $pageTitle = 'Communication Settings'; ?>

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-paper-plane me-2 text-primary"></i>Communication Settings</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Settings</a></li>
                <li class="breadcrumb-item active">Communication</li>
            </ol>
        </nav>
    </div>
</div>

<?php $flash = getFlash('success'); $flashErr = getFlash('error'); ?>
<?php if ($flash): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?= e($flash) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($flashErr): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i><?= e($flashErr) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Tabs linking back -->
<ul class="nav nav-tabs mb-0 border-bottom-0">
    <li class="nav-item">
        <a class="nav-link" href="<?= url('settings') ?>"><i class="fas fa-sliders-h me-1"></i>General</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="<?= url('settings/communication') ?>"><i class="fas fa-paper-plane me-1"></i>Communication</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?= url('audit-logs') ?>"><i class="fas fa-history me-1"></i>Audit Logs</a>
    </li>
</ul>

<form method="POST" action="<?= url('settings/communication') ?>" id="commForm">
    <?= csrfField() ?>
    <div class="tab-content bg-body border border-top-0 rounded-bottom p-4">
        <div class="row g-4">

            <!-- ── SMTP / Email ──────────────────────────────────── -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <span class="fw-semibold"><i class="fas fa-envelope me-2 text-primary"></i>Email (SMTP) Settings</span>
                        <span id="smtpStatusBadge" class="badge bg-secondary">Not tested</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-medium">SMTP Host</label>
                                <input type="text" class="form-control" name="mail_host" id="smtpHost"
                                       value="<?= e($settings['mail_host'] ?? '') ?>"
                                       placeholder="smtp.gmail.com">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium">SMTP Port</label>
                                <input type="number" class="form-control" name="mail_port" id="smtpPort"
                                       value="<?= e($settings['mail_port'] ?? 587) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Encryption</label>
                                <select class="form-select" name="mail_encryption">
                                    <option value="tls" <?= ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (port 587)</option>
                                    <option value="ssl" <?= ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL (port 465)</option>
                                    <option value=""    <?= ($settings['mail_encryption'] ?? '') === ''    ? 'selected' : '' ?>>None (port 25)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">SMTP Username</label>
                                <input type="text" class="form-control" name="mail_username"
                                       value="<?= e($settings['mail_username'] ?? '') ?>"
                                       placeholder="your@gmail.com" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">SMTP Password / App Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="mail_password" id="smtpPwd"
                                           value="<?= e($settings['mail_password'] ?? '') ?>"
                                           placeholder="Leave blank to keep existing" autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('smtpPwd', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">For Gmail, use an <a href="https://myaccount.google.com/apppasswords" target="_blank">App Password</a>.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">From Address</label>
                                <input type="email" class="form-control" name="mail_from_address"
                                       value="<?= e($settings['mail_from_address'] ?? '') ?>"
                                       placeholder="noreply@institution.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">From Name</label>
                                <input type="text" class="form-control" name="mail_from_name"
                                       value="<?= e($settings['mail_from_name'] ?? '') ?>"
                                       placeholder="Institution CRM">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex align-items-center gap-2 flex-wrap">
                        <div class="input-group" style="max-width:260px">
                            <input type="email" class="form-control form-control-sm" id="testEmailAddr"
                                   placeholder="Send test to..." value="<?= e($settings['mail_from_address'] ?? '') ?>">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="testEmailBtn" onclick="testEmail()">
                                <i class="fas fa-flask me-1"></i>Test SMTP
                            </button>
                        </div>
                        <div id="testEmailResult" class="small ms-2"></div>
                    </div>
                </div>
            </div>

            <!-- ── SMS ──────────────────────────────────────────── -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <span class="fw-semibold"><i class="fas fa-sms me-2 text-success"></i>SMS Settings</span>
                        <span id="smsStatusBadge" class="badge bg-secondary">Not tested</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-medium">SMS Provider</label>
                                <select class="form-select" name="sms_provider" id="smsProvider" onchange="toggleSmsFields()">
                                    <option value=""      <?= empty($settings['sms_provider']) ? 'selected' : '' ?>>— Disabled —</option>
                                    <option value="msg91" <?= ($settings['sms_provider'] ?? '') === 'msg91' ? 'selected' : '' ?>>MSG91 (India)</option>
                                    <option value="twilio"<?= ($settings['sms_provider'] ?? '') === 'twilio' ? 'selected' : '' ?>>Twilio (Global)</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-medium">API Key / Auth Token</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="sms_api_key" id="smsApiKey"
                                           value="<?= e($settings['sms_api_key'] ?? '') ?>"
                                           placeholder="Leave blank to keep existing" autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('smsApiKey', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium">Sender ID</label>
                                <input type="text" class="form-control" name="sms_sender_id"
                                       value="<?= e($settings['sms_sender_id'] ?? '') ?>"
                                       placeholder="MYCRM" maxlength="11">
                            </div>
                            <!-- Twilio extra -->
                            <div class="col-md-6" id="twilioSidRow" style="display:none">
                                <label class="form-label fw-medium">Twilio Account SID</label>
                                <input type="text" class="form-control" name="twilio_account_sid"
                                       value="<?= e($settings['twilio_account_sid'] ?? '') ?>"
                                       placeholder="ACxxxxxxxxxxxxxxxx">
                            </div>
                            <div class="col-md-6" id="twilioFromRow" style="display:none">
                                <label class="form-label fw-medium">Twilio From Number</label>
                                <input type="text" class="form-control" name="twilio_from_number"
                                       value="<?= e($settings['twilio_from_number'] ?? '') ?>"
                                       placeholder="+1xxxxxxxxxx">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex align-items-center gap-2 flex-wrap">
                        <div class="input-group" style="max-width:220px">
                            <input type="tel" class="form-control form-control-sm" id="testSmsNumber"
                                   placeholder="Mobile number...">
                            <button type="button" class="btn btn-sm btn-outline-success" id="testSmsBtn" onclick="testSms()">
                                <i class="fas fa-flask me-1"></i>Test SMS
                            </button>
                        </div>
                        <div id="testSmsResult" class="small ms-2"></div>
                    </div>
                </div>
            </div>

            <!-- ── WhatsApp ──────────────────────────────────────── -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent fw-semibold">
                        <i class="fab fa-whatsapp me-2 text-success" style="font-size:1.1em"></i>WhatsApp API Settings
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-medium">WhatsApp Provider</label>
                                <select class="form-select" name="whatsapp_provider">
                                    <option value=""           <?= empty($settings['whatsapp_provider']) ? 'selected' : '' ?>>— Disabled —</option>
                                    <option value="wati"       <?= ($settings['whatsapp_provider'] ?? '') === 'wati'       ? 'selected' : '' ?>>WATI</option>
                                    <option value="twilio"     <?= ($settings['whatsapp_provider'] ?? '') === 'twilio'     ? 'selected' : '' ?>>Twilio (WA)</option>
                                    <option value="360dialog"  <?= ($settings['whatsapp_provider'] ?? '') === '360dialog'  ? 'selected' : '' ?>>360Dialog</option>
                                    <option value="custom"     <?= ($settings['whatsapp_provider'] ?? '') === 'custom'     ? 'selected' : '' ?>>Custom API</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-medium">API Endpoint URL</label>
                                <input type="url" class="form-control" name="whatsapp_api_url"
                                       value="<?= e($settings['whatsapp_api_url'] ?? '') ?>"
                                       placeholder="https://api.wati.io/v1/sendMessage">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium">API Key / Token</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="whatsapp_api_key" id="waApiKey"
                                           value="<?= e($settings['whatsapp_api_key'] ?? '') ?>"
                                           placeholder="Bearer token..." autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('waApiKey', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">WhatsApp From Number</label>
                                <input type="text" class="form-control" name="whatsapp_from_number"
                                       value="<?= e($settings['whatsapp_from_number'] ?? '') ?>"
                                       placeholder="+91xxxxxxxxxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Default Message Template</label>
                                <input type="text" class="form-control" name="whatsapp_default_template"
                                       value="<?= e($settings['whatsapp_default_template'] ?? '') ?>"
                                       placeholder="admission_confirmation">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Notification Templates ────────────────────────── -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent fw-semibold">
                        <i class="fas fa-bell me-2 text-warning"></i>Notification Triggers
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Choose which events send automatic notifications:</p>
                        <div class="row g-3">
                            <?php
                            $triggers = [
                                'notify_new_enquiry'      => 'New enquiry submitted',
                                'notify_admission_confirm'=> 'Admission confirmed',
                                'notify_fee_paid'         => 'Fee payment received',
                                'notify_fee_due'          => 'Fee payment due reminder',
                                'notify_attendance_low'   => 'Attendance below threshold',
                                'notify_exam_result'      => 'Exam results published',
                                'notify_new_assignment'   => 'New LMS assignment posted',
                                'notify_leave_approved'   => 'Leave request approved',
                            ];
                            foreach ($triggers as $key => $label):
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="<?= $key ?>" value="1"
                                           id="<?= $key ?>" <?= !empty($settings[$key]) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="<?= $key ?>"><?= $label ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Save Communication Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// ── Toggle password visibility ────────────────────────────────────
function togglePwd(fieldId, btn) {
    var f = document.getElementById(fieldId);
    if (!f) return;
    var show = f.type === 'password';
    f.type = show ? 'text' : 'password';
    btn.innerHTML = '<i class="fas fa-' + (show ? 'eye-slash' : 'eye') + '"></i>';
}

// ── Show Twilio extra fields ──────────────────────────────────────
function toggleSmsFields() {
    var prov = document.getElementById('smsProvider').value;
    var sidRow  = document.getElementById('twilioSidRow');
    var fromRow = document.getElementById('twilioFromRow');
    if (sidRow)  sidRow.style.display  = prov === 'twilio' ? '' : 'none';
    if (fromRow) fromRow.style.display = prov === 'twilio' ? '' : 'none';
}
toggleSmsFields();

// ── Test Email ────────────────────────────────────────────────────
function testEmail() {
    var email = document.getElementById('testEmailAddr').value.trim();
    if (!email) { showResult('testEmailResult', 'Enter a recipient email.', false); return; }

    var btn = document.getElementById('testEmailBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Testing…';
    document.getElementById('testEmailResult').innerHTML = '';

    fetch('<?= url('settings/test-email') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-Token': '<?= csrfToken() ?>'},
        body: JSON.stringify({email: email})
    })
    .then(r => r.json())
    .then(d => {
        showResult('testEmailResult', d.message, d.success);
        var badge = document.getElementById('smtpStatusBadge');
        if (badge) {
            badge.textContent = d.success ? '✓ Connected' : '✗ Failed';
            badge.className = 'badge ' + (d.success ? 'bg-success' : 'bg-danger');
        }
    })
    .catch(() => showResult('testEmailResult', 'Request failed. Check browser console.', false))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-flask me-1"></i>Test SMTP';
    });
}

// ── Test SMS ──────────────────────────────────────────────────────
function testSms() {
    var mobile = document.getElementById('testSmsNumber').value.trim();
    if (!mobile) { showResult('testSmsResult', 'Enter a mobile number.', false); return; }

    var btn = document.getElementById('testSmsBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Testing…';
    document.getElementById('testSmsResult').innerHTML = '';

    fetch('<?= url('settings/test-sms') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-Token': '<?= csrfToken() ?>'},
        body: JSON.stringify({mobile: mobile})
    })
    .then(r => r.json())
    .then(d => {
        showResult('testSmsResult', d.message, d.success);
        var badge = document.getElementById('smsStatusBadge');
        if (badge) {
            badge.textContent = d.success ? '✓ OK' : '✗ Failed';
            badge.className = 'badge ' + (d.success ? 'bg-success' : 'bg-danger');
        }
    })
    .catch(() => showResult('testSmsResult', 'Request failed.', false))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-flask me-1"></i>Test SMS';
    });
}

// ── Utility ───────────────────────────────────────────────────────
function showResult(id, msg, ok) {
    var el = document.getElementById(id);
    if (!el) return;
    el.innerHTML = '<span class="badge ' + (ok ? 'bg-success' : 'bg-danger') + ' py-2 px-3">'
        + '<i class="fas fa-' + (ok ? 'check' : 'times') + '-circle me-1"></i>'
        + escHtml(msg) + '</span>';
}
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
