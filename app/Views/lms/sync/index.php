<style>
.sync-card   { border-radius:16px; border:1px solid #e8e3ff; background:#fff; padding:1.5rem; }
.sync-header { display:flex; align-items:center; justify-content:space-between; gap:.75rem; }
.stat-pill   { font-size:.72rem; font-weight:700; padding:.2rem .65rem; border-radius:20px; }
.arrow-bar   { position:relative; height:6px; border-radius:3px; background:#e2e8f0; overflow:hidden; margin:.5rem 0; }
.arrow-fill  { position:absolute; left:0; top:0; height:100%; border-radius:3px; transition:width .6s; }
.log-dot     { width:8px; height:8px; border-radius:50%; flex-shrink:0; margin-top:.35rem; }
.sync-btn    { border-radius:10px; font-size:.82rem; font-weight:600; padding:.45rem 1.1rem; transition:all .15s; }
.badge-ok    { background:#d1fae5; color:#059669; }
.badge-warn  { background:#fef3c7; color:#d97706; }
.badge-info  { background:#dbeafe; color:#2563eb; }
</style>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0" style="color:#0f172a">
        <i class="fas fa-sync-alt me-2 text-primary"></i>Academic → LMS Sync
    </h4>
    <button class="btn btn-primary sync-btn" id="syncAllBtn" onclick="runSync('all')">
        <i class="fas fa-bolt me-1"></i>Sync Everything
    </button>
</div>

<!-- Info banner -->
<div class="alert rounded-3 small mb-4" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af">
    <i class="fas fa-info-circle me-2"></i>
    This syncs your <strong>Academic module data</strong> into the LMS:
    <strong>Subjects → LMS Courses</strong>,
    <strong>Students → LMS Learners</strong>,
    <strong>Faculty → LMS Instructors</strong>,
    <strong>Section Enrollments → LMS Enrollments</strong>.
    Run in the order shown below, or use <em>Sync Everything</em>.
</div>

<!-- ── 4 Sync Cards ─────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <!-- Students -->
    <div class="col-md-6">
        <div class="sync-card h-100">
            <div class="sync-header mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:40px;height:40px;border-radius:12px;background:#ede9fe;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-user-graduate" style="color:#6366f1"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="color:#0f172a;font-size:.95rem">Students</div>
                        <div class="text-muted" style="font-size:.72rem">Academic students → LMS Learners</div>
                    </div>
                </div>
                <button class="btn btn-outline-primary sync-btn" onclick="runSync('students')" id="btn-students">
                    <i class="fas fa-sync-alt me-1"></i>Sync
                </button>
            </div>
            <div class="d-flex justify-content-between align-items-center small text-muted mb-1">
                <span>Academic (active)</span>
                <span class="stat-pill badge-info" id="s-academic"><?= number_format($stats['students']['academic']) ?></span>
            </div>
            <div class="arrow-bar"><div class="arrow-fill" style="background:#6366f1;width:<?= $stats['students']['academic'] > 0 ? min(100, round($stats['students']['lms']/$stats['students']['academic']*100)) : 0 ?>%"></div></div>
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>Synced to LMS</span>
                <span class="stat-pill badge-ok" id="s-lms"><?= number_format($stats['students']['lms']) ?></span>
            </div>
            <div id="res-students" class="mt-2 small" style="display:none"></div>
        </div>
    </div>

    <!-- Faculty -->
    <div class="col-md-6">
        <div class="sync-card h-100">
            <div class="sync-header mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:40px;height:40px;border-radius:12px;background:#fef3c7;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-chalkboard-teacher" style="color:#d97706"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="color:#0f172a;font-size:.95rem">Faculty</div>
                        <div class="text-muted" style="font-size:.72rem">Academic faculty → LMS Instructors</div>
                    </div>
                </div>
                <button class="btn btn-outline-warning sync-btn" onclick="runSync('faculty')" id="btn-faculty">
                    <i class="fas fa-sync-alt me-1"></i>Sync
                </button>
            </div>
            <div class="d-flex justify-content-between align-items-center small text-muted mb-1">
                <span>Academic (active)</span>
                <span class="stat-pill badge-info" id="f-academic"><?= number_format($stats['faculty']['academic']) ?></span>
            </div>
            <div class="arrow-bar"><div class="arrow-fill" style="background:#d97706;width:<?= $stats['faculty']['academic'] > 0 ? min(100, round($stats['faculty']['lms']/$stats['faculty']['academic']*100)) : 0 ?>%"></div></div>
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>Synced to LMS</span>
                <span class="stat-pill badge-ok" id="f-lms"><?= number_format($stats['faculty']['lms']) ?></span>
            </div>
            <div id="res-faculty" class="mt-2 small" style="display:none"></div>
        </div>
    </div>

    <!-- Courses (Subjects) -->
    <div class="col-md-6">
        <div class="sync-card h-100">
            <div class="sync-header mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:40px;height:40px;border-radius:12px;background:#d1fae5;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-book" style="color:#059669"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="color:#0f172a;font-size:.95rem">Courses</div>
                        <div class="text-muted" style="font-size:.72rem">Academic subjects → LMS Courses</div>
                    </div>
                </div>
                <button class="btn btn-outline-success sync-btn" onclick="runSync('courses')" id="btn-courses">
                    <i class="fas fa-sync-alt me-1"></i>Sync
                </button>
            </div>
            <div class="d-flex justify-content-between align-items-center small text-muted mb-1">
                <span>Academic subjects (active)</span>
                <span class="stat-pill badge-info" id="c-academic"><?= number_format($stats['courses']['academic']) ?></span>
            </div>
            <div class="arrow-bar"><div class="arrow-fill" style="background:#059669;width:<?= $stats['courses']['academic'] > 0 ? min(100, round($stats['courses']['lms']/$stats['courses']['academic']*100)) : 0 ?>%"></div></div>
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>Synced LMS courses</span>
                <span class="stat-pill badge-ok" id="c-lms"><?= number_format($stats['courses']['lms']) ?></span>
            </div>
            <div id="res-courses" class="mt-2 small" style="display:none"></div>
        </div>
    </div>

    <!-- Enrollments -->
    <div class="col-md-6">
        <div class="sync-card h-100">
            <div class="sync-header mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:40px;height:40px;border-radius:12px;background:#fee2e2;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-list-check" style="color:#dc2626"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="color:#0f172a;font-size:.95rem">Enrollments</div>
                        <div class="text-muted" style="font-size:.72rem">Section enrollments → LMS enrollments</div>
                    </div>
                </div>
                <button class="btn btn-outline-danger sync-btn" onclick="runSync('enrollments')" id="btn-enrollments">
                    <i class="fas fa-sync-alt me-1"></i>Sync
                </button>
            </div>
            <div class="d-flex justify-content-between align-items-center small text-muted mb-1">
                <span>Academic (active)</span>
                <span class="stat-pill badge-info" id="e-academic"><?= number_format($stats['enrollments']['academic']) ?></span>
            </div>
            <div class="arrow-bar"><div class="arrow-fill" style="background:#dc2626;width:<?= $stats['enrollments']['academic'] > 0 ? min(100, round($stats['enrollments']['lms']/$stats['enrollments']['academic']*100)) : 0 ?>%"></div></div>
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>LMS enrollments</span>
                <span class="stat-pill badge-ok" id="e-lms"><?= number_format($stats['enrollments']['lms']) ?></span>
            </div>
            <div id="res-enrollments" class="mt-2 small" style="display:none"></div>
        </div>
    </div>
</div>

<!-- Global progress bar (shown during Sync All) -->
<div id="syncProgress" style="display:none" class="mb-4">
    <div class="sync-card" style="padding:1rem 1.25rem">
        <div class="d-flex align-items-center gap-3">
            <div class="spinner-border spinner-border-sm text-primary" style="flex-shrink:0"></div>
            <div style="flex:1">
                <div class="fw-semibold small mb-1" id="syncProgressLabel">Syncing…</div>
                <div class="progress" style="height:8px;border-radius:4px">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="syncProgressBar" style="width:0%;background:#6366f1"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Sync Log ──────────────────────────────────────────────────── -->
<div class="sync-card">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="fw-bold" style="color:#0f172a"><i class="fas fa-history me-2 text-muted"></i>Recent Sync Log</div>
        <span class="text-muted small">Last 30 operations</span>
    </div>
    <?php if (empty($logs)): ?>
    <div class="text-center py-4" style="color:#94a3b8">
        <i class="fas fa-clock" style="font-size:2rem;opacity:.2"></i>
        <p class="small mt-2">No sync operations yet. Run a sync to see the log here.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm small mb-0" style="color:#374151">
            <thead style="background:#f8fafc;color:#64748b;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em">
                <tr>
                    <th style="border:none;padding:.6rem .75rem">Type</th>
                    <th style="border:none;padding:.6rem .75rem">Synced By</th>
                    <th style="border:none;padding:.6rem .75rem;text-align:center">Created</th>
                    <th style="border:none;padding:.6rem .75rem;text-align:center">Updated</th>
                    <th style="border:none;padding:.6rem .75rem;text-align:center">Skipped</th>
                    <th style="border:none;padding:.6rem .75rem;text-align:center">Errors</th>
                    <th style="border:none;padding:.6rem .75rem">When</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
            <?php
            $typeColors = [
                'students'    => '#6366f1',
                'faculty'     => '#d97706',
                'courses'     => '#059669',
                'enrollments' => '#dc2626',
                'all'         => '#0284c7',
            ];
            $tc = $typeColors[$log['sync_type']] ?? '#64748b';
            $byName = trim(($log['first_name'] ?? '').' '.($log['last_name'] ?? '')) ?: 'System';
            $diff = time() - strtotime($log['created_at']);
            $ago  = $diff < 60 ? 'just now'
                  : ($diff < 3600 ? floor($diff/60).'m ago'
                  : ($diff < 86400 ? floor($diff/3600).'h ago'
                  : date('d M Y', strtotime($log['created_at']))));
            ?>
            <tr style="border-top:1px solid #f1f5f9">
                <td style="padding:.55rem .75rem">
                    <span class="fw-semibold" style="color:<?= $tc ?>;font-size:.75rem;text-transform:capitalize"><?= e($log['sync_type']) ?></span>
                </td>
                <td style="padding:.55rem .75rem;color:#64748b"><?= e($byName) ?></td>
                <td style="padding:.55rem .75rem;text-align:center">
                    <?php if ($log['created_count'] > 0): ?>
                    <span class="stat-pill badge-ok"><?= $log['created_count'] ?></span>
                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                </td>
                <td style="padding:.55rem .75rem;text-align:center">
                    <?php if ($log['updated_count'] > 0): ?>
                    <span class="stat-pill badge-info"><?= $log['updated_count'] ?></span>
                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                </td>
                <td style="padding:.55rem .75rem;text-align:center">
                    <?php if ($log['skipped_count'] > 0): ?>
                    <span class="stat-pill badge-warn"><?= $log['skipped_count'] ?></span>
                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                </td>
                <td style="padding:.55rem .75rem;text-align:center">
                    <?php if ($log['error_count'] > 0): ?>
                    <span class="stat-pill" style="background:#fee2e2;color:#dc2626"><?= $log['error_count'] ?></span>
                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                </td>
                <td style="padding:.55rem .75rem;color:#94a3b8;font-size:.7rem"><?= $ago ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
const CSRF  = '<?= csrfToken() ?>';
const BASE  = '<?= url('elms/sync') ?>';

// Map type → stat DOM ids + colors
const TYPE_MAP = {
    students:    { acEl:'s-academic', lmsEl:'s-lms', bar:'#6366f1', label:'Syncing students…',    pct:25  },
    faculty:     { acEl:'f-academic', lmsEl:'f-lms', bar:'#d97706', label:'Syncing faculty…',     pct:50  },
    courses:     { acEl:'c-academic', lmsEl:'c-lms', bar:'#059669', label:'Syncing courses…',     pct:75  },
    enrollments: { acEl:'e-academic', lmsEl:'e-lms', bar:'#dc2626', label:'Syncing enrollments…', pct:100 },
};

function setBtn(type, loading) {
    const btn = document.getElementById('btn-'+type);
    if (!btn) return;
    btn.disabled = loading;
    const icon = btn.querySelector('i');
    if (icon) icon.className = loading ? 'fas fa-spinner fa-spin me-1' : 'fas fa-sync-alt me-1';
}

function showResult(type, d) {
    const el = document.getElementById('res-'+type);
    if (!el) return;
    el.style.display = 'block';
    const parts = [];
    if (d.created)  parts.push(`<span class="fw-semibold text-success">${d.created} created</span>`);
    if (d.updated)  parts.push(`<span class="fw-semibold text-primary">${d.updated} updated</span>`);
    if (d.skipped)  parts.push(`<span class="text-warning">${d.skipped} skipped</span>`);
    if (d.errors)   parts.push(`<span class="text-danger">${d.errors} errors</span>`);
    el.innerHTML = parts.length
        ? '<i class="fas fa-check-circle text-success me-1"></i>'+parts.join(' &middot; ')
        : '<i class="fas fa-check-circle text-success me-1"></i>Nothing to update — already in sync';
}

async function runSync(type) {
    if (type === 'all') return runSyncAll();

    setBtn(type, true);
    const resEl = document.getElementById('res-'+type);
    if (resEl) { resEl.style.display='none'; resEl.innerHTML=''; }

    try {
        const r = await fetch(`${BASE}/${type}`, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest' },
            body: JSON.stringify({ _csrf: CSRF }),
        });
        const d = await r.json();
        showResult(type, d);
        // Refresh stats
        const stats = await fetch(`${BASE}/stats`, { headers:{'X-Requested-With':'XMLHttpRequest'} }).then(r=>r.json());
        refreshStats(stats);
    } catch (err) {
        const resEl = document.getElementById('res-'+type);
        if (resEl) { resEl.style.display='block'; resEl.innerHTML='<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Request failed</span>'; }
    } finally {
        setBtn(type, false);
    }
}

async function runSyncAll() {
    const allBtn = document.getElementById('syncAllBtn');
    allBtn.disabled = true;
    allBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Syncing…';

    const prog = document.getElementById('syncProgress');
    const label = document.getElementById('syncProgressLabel');
    const bar   = document.getElementById('syncProgressBar');
    prog.style.display = 'block';

    const steps = ['students','faculty','courses','enrollments'];
    let pct = 0;

    for (const step of steps) {
        const m = TYPE_MAP[step];
        label.textContent = m.label;
        bar.style.width   = m.pct + '%';
        setBtn(step, true);

        try {
            const r = await fetch(`${BASE}/${step}`, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
                body: JSON.stringify({_csrf:CSRF}),
            });
            const d = await r.json();
            showResult(step, d);
        } catch(e) {}
        setBtn(step, false);
    }

    label.textContent = 'Sync complete ✓';
    bar.style.width   = '100%';
    bar.classList.remove('progress-bar-animated');

    // Refresh all stats
    try {
        const stats = await fetch(`${BASE}/stats`,{headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json());
        refreshStats(stats);
    } catch(e) {}

    setTimeout(() => { prog.style.display='none'; }, 3000);
    allBtn.disabled = false;
    allBtn.innerHTML = '<i class="fas fa-bolt me-1"></i>Sync Everything';
}

function refreshStats(s) {
    const set = (id, val) => { const el = document.getElementById(id); if(el) el.textContent = Number(val).toLocaleString(); };
    set('s-academic', s.students_academic); set('s-lms', s.students_lms);
    set('f-academic', s.faculty_academic);  set('f-lms', s.faculty_lms);
    set('c-academic', s.courses_academic);  set('c-lms', s.courses_lms);
    set('e-academic', s.enroll_academic);   set('e-lms', s.enroll_lms);
}
</script>
