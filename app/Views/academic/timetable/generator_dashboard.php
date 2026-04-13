<?php
$pageTitle = 'Timetable Generator';
$breadcrumbs = [['label'=>'Academic'], ['label'=>'Timetable','url'=>url('academic/timetable')], ['label'=>'Generator']];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-magic text-primary me-2"></i>Timetable Generator</h4>
        <p class="text-muted small mb-0">Automatically generate optimised timetables using constraint-based scheduling.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('academic/timetable/generator/configure') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Configuration
        </a>
        <a href="<?= url('academic/timetable/generator/analytics') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-chart-bar me-1"></i> Analytics
        </a>
    </div>
</div>

<?php flash_alerts(); ?>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                    <i class="fas fa-layer-group fa-lg text-primary"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= (int)($stats['total_sections'] ?? 0) ?></div>
                    <div class="text-muted small">Total Sections</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 p-3">
                    <i class="fas fa-calendar-check fa-lg text-success"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= (int)($stats['sections_with_timetable'] ?? 0) ?></div>
                    <div class="text-muted small">With Timetable</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                    <i class="fas fa-list-check fa-lg text-warning"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= (int)($stats['total_requirements'] ?? 0) ?></div>
                    <div class="text-muted small">Requirements</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 p-3">
                    <i class="fas fa-check-circle fa-lg text-info"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= (int)($stats['approved_runs'] ?? 0) ?></div>
                    <div class="text-muted small">Approved Runs</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Configurations -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-sliders-h me-2 text-muted"></i>Configurations</h6>
                <a href="<?= url('academic/timetable/generator/configure') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($configs)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-cog fa-2x mb-2 d-block opacity-25"></i>
                        No configurations yet.<br>
                        <a href="<?= url('academic/timetable/generator/configure') ?>" class="btn btn-sm btn-primary mt-2">Create First Config</a>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($configs as $cfg): ?>
                            <a href="<?= url('academic/timetable/generator/configure/' . $cfg['id']) ?>"
                               class="list-group-item list-group-item-action px-3 py-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold"><?= e($cfg['name']) ?></div>
                                        <small class="text-muted">
                                            <?php
                                                $bits = (int)$cfg['working_days'];
                                                $days = [];
                                                $map = ['Mon'=>1,'Tue'=>2,'Wed'=>4,'Thu'=>8,'Fri'=>16,'Sat'=>32,'Sun'=>64];
                                                foreach ($map as $d => $b) { if ($bits & $b) $days[] = $d; }
                                                echo implode(' · ', $days);
                                            ?>
                                            &middot; <?= (int)$cfg['max_periods_per_day'] ?> periods/day
                                        </small>
                                    </div>
                                    <button class="btn btn-sm btn-primary ms-2 generate-btn"
                                            data-config="<?= $cfg['id'] ?>"
                                            data-name="<?= e($cfg['name']) ?>">
                                        <i class="fas fa-bolt"></i> Generate
                                    </button>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($configs)): ?>
                <div class="card-footer bg-transparent text-center">
                    <a href="<?= url('academic/timetable/generator/unavailability') ?>" class="text-muted small">
                        <i class="fas fa-user-clock me-1"></i> Manage Teacher Unavailability
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Runs -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-history me-2 text-muted"></i>Recent Generation Runs</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($runs)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-magic fa-2x mb-2 d-block opacity-25"></i>
                        No runs yet. Select a configuration and click <strong>Generate</strong>.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Run</th>
                                    <th>Config</th>
                                    <th class="text-center">Score</th>
                                    <th class="text-center">Slots</th>
                                    <th class="text-center">Conflicts</th>
                                    <th>Status</th>
                                    <th>When</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($runs as $run): ?>
                                    <?php
                                        $statusMap = [
                                            'pending'   => ['warning','clock'],
                                            'running'   => ['info','spinner fa-spin'],
                                            'completed' => ['primary','check'],
                                            'approved'  => ['success','check-circle'],
                                            'failed'    => ['danger','times-circle'],
                                            'discarded' => ['secondary','ban'],
                                        ];
                                        [$sc, $ic] = $statusMap[$run['status']] ?? ['secondary','question'];
                                        $pct = (float)($run['score'] ?? 0);
                                        $scoreColor = $pct >= 90 ? 'success' : ($pct >= 70 ? 'warning' : 'danger');
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold small"><?= e($run['run_name']) ?></div>
                                            <div class="text-muted" style="font-size:.7rem">#<?= $run['id'] ?> &middot; <?= e($run['algorithm'] ?? '') ?></div>
                                        </td>
                                        <td><small><?= e($run['config_name'] ?? '—') ?></small></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $scoreColor ?> bg-opacity-10 text-<?= $scoreColor ?> fw-semibold">
                                                <?= number_format((float)$run['score'], 1) ?>%
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-success fw-semibold"><?= (int)$run['assigned_count'] ?></span>
                                            <span class="text-muted">/<?= (int)$run['total_requirements'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ((int)$run['conflict_count'] > 0): ?>
                                                <span class="badge bg-danger"><?= (int)$run['conflict_count'] ?></span>
                                            <?php else: ?>
                                                <span class="text-success"><i class="fas fa-check"></i></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-<?= $sc ?> bg-opacity-10 text-<?= $sc ?>"><i class="fas fa-<?= $ic ?> me-1"></i><?= ucfirst($run['status']) ?></span></td>
                                        <td><small class="text-muted"><?= date('d M, H:i', strtotime($run['created_at'])) ?></small></td>
                                        <td>
                                            <?php if ($run['status'] === 'completed'): ?>
                                                <a href="<?= url('academic/timetable/generator/run/' . $run['id']) ?>" class="btn btn-sm btn-outline-primary">Review</a>
                                            <?php elseif ($run['status'] === 'approved'): ?>
                                                <a href="<?= url('academic/timetable/generator/run/' . $run['id']) ?>" class="btn btn-sm btn-outline-success">View</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Generate Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fas fa-magic me-2 text-primary"></i>Generate Timetable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="genForm">
                    <input type="hidden" id="genConfigId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Run Name</label>
                        <input type="text" id="genRunName" class="form-control"
                               value="Auto Run <?= date('d M Y H:i') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Scope</label>
                        <div class="form-text mb-2">Leave blank to generate for all sections in this config, or select specific sections.</div>
                        <select id="genSections" class="form-select" multiple style="height:140px">
                            <!-- populated dynamically -->
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple. Leave unselected = all sections.</div>
                    </div>
                </div>
                <div id="genProgress" class="d-none text-center py-3">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <div class="fw-semibold">Generating timetable…</div>
                    <div class="text-muted small mt-1">This may take a few seconds for large datasets.</div>
                </div>
                <div id="genResult" class="d-none"></div>
            </div>
            <div class="modal-footer border-0" id="genFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="runGenerate()">
                    <i class="fas fa-bolt me-1"></i> Generate Now
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.generate-btn').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        const configId = btn.dataset.config;
        document.getElementById('genConfigId').value = configId;
        document.getElementById('genRunName').value = 'Auto Run <?= date('d M Y H:i') ?>';
        document.getElementById('genForm').classList.remove('d-none');
        document.getElementById('genProgress').classList.add('d-none');
        document.getElementById('genResult').classList.add('d-none');
        document.getElementById('genFooter').classList.remove('d-none');
        new bootstrap.Modal(document.getElementById('generateModal')).show();
    });
});

function runGenerate() {
    const configId = document.getElementById('genConfigId').value;
    const runName  = document.getElementById('genRunName').value.trim() || 'Auto Run';
    const selEl    = document.getElementById('genSections');
    const sections = Array.from(selEl.selectedOptions).map(o => parseInt(o.value));

    document.getElementById('genForm').classList.add('d-none');
    document.getElementById('genProgress').classList.remove('d-none');
    document.getElementById('genFooter').classList.add('d-none');

    fetch('<?= url('academic/timetable/generator/generate') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= csrfToken() ?>' },
        body: JSON.stringify({ config_id: parseInt(configId), run_name: runName, sections }),
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('genProgress').classList.add('d-none');
        const res = document.getElementById('genResult');
        res.classList.remove('d-none');

        if (d.success) {
            const scoreColor = d.score >= 90 ? 'success' : d.score >= 70 ? 'warning' : 'danger';
            res.innerHTML = `
                <div class="alert alert-success border-0 mb-3">
                    <i class="fas fa-check-circle me-2"></i> ${d.message}
                </div>
                <div class="row g-3 text-center mb-3">
                    <div class="col-4">
                        <div class="fs-3 fw-bold text-${scoreColor}">${d.score}%</div>
                        <div class="text-muted small">Quality Score</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-3 fw-bold text-success">${d.assigned}</div>
                        <div class="text-muted small">Slots Filled</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-3 fw-bold text-${d.conflicts>0?'danger':'success'}">${d.conflicts}</div>
                        <div class="text-muted small">Conflicts</div>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="<?= url('academic/timetable/generator/run/') ?>${d.run_id}" class="btn btn-primary">
                        <i class="fas fa-eye me-1"></i> Review & Approve
                    </a>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>`;
        } else {
            res.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>${d.message || 'Generation failed.'}</div>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`;
        }
    })
    .catch(() => {
        document.getElementById('genProgress').classList.add('d-none');
        document.getElementById('genResult').innerHTML =
            `<div class="alert alert-danger">Network error. Please try again.</div>`;
        document.getElementById('genResult').classList.remove('d-none');
    });
}
</script>
