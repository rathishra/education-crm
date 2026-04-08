<?php $types = ['mcq'=>'Multiple Choice','multi'=>'Multi-Select','true_false'=>'True / False','short'=>'Short Answer','fill_blank'=>'Fill in Blank','match'=>'Matching']; ?>
<style>
.qb-card { background:#fff; border-radius:12px; border:1px solid #e8e3ff; margin-bottom:.75rem; box-shadow:0 1px 4px rgba(99,102,241,.05); transition:box-shadow .15s; }
.qb-card:hover { box-shadow:0 4px 14px rgba(99,102,241,.1); }
.qb-type-badge { font-size:.68rem; font-weight:700; padding:.2rem .55rem; border-radius:20px; background:#ede9fe; color:#6366f1; }
.qb-drag-handle { cursor:grab; color:#cbd5e1; padding:0 .5rem; font-size:1.1rem; }
.opt-row { display:flex; gap:.5rem; align-items:center; margin-bottom:.4rem; }
.correct-dot { width:18px; height:18px; border-radius:50%; border:2px solid #d1d5db; cursor:pointer; flex-shrink:0; transition:background .15s,border-color .15s; }
.correct-dot.active { background:#22c55e; border-color:#22c55e; }
</style>

<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <a href="<?= url('elms/quizzes/'.$quiz['id'].'/edit') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <div style="flex:1;min-width:0">
        <h4 class="fw-bold mb-0" style="color:#0f172a;font-size:1rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis">
            <i class="fas fa-tools me-2 text-primary"></i><?= e($quiz['title']) ?> — Question Builder
        </h4>
        <div class="text-muted small mt-1"><span id="qCount"><?= count($questions) ?></span> questions &middot; <span id="totalPts"><?= array_sum(array_column($questions, 'points')) ?></span> pts total</div>
    </div>
    <button class="btn btn-primary" style="border-radius:9px" onclick="openAddModal()"><i class="fas fa-plus me-2"></i>Add Question</button>
    <a href="<?= url('elms/quizzes') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px"><i class="fas fa-list me-1"></i>All Quizzes</a>
</div>

<div id="questionList" class="mb-4">
<?php if (empty($questions)): ?>
<div id="emptyState" class="text-center py-5">
    <div style="font-size:3rem;opacity:.12;color:#6366f1"><i class="fas fa-question"></i></div>
    <h6 class="fw-bold mt-3 text-muted">No questions yet</h6>
    <p class="text-muted small">Click "Add Question" to get started</p>
</div>
<?php endif; ?>
<?php foreach ($questions as $i => $q): ?>
<?php $typeName = $types[$q['type']] ?? $q['type']; ?>
<div class="qb-card p-3 d-flex gap-3 align-items-start" data-qid="<?= $q['id'] ?>" id="qCard<?= $q['id'] ?>">
    <span class="qb-drag-handle mt-1"><i class="fas fa-grip-vertical"></i></span>
    <div style="flex:1;min-width:0">
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <span class="qb-type-badge"><?= $typeName ?></span>
            <span class="text-muted small"><?= $q['points'] ?> pt<?= $q['points']!=1?'s':'' ?></span>
        </div>
        <div class="fw-semibold small" style="color:#0f172a"><?= e($q['question']) ?></div>
        <?php if (!empty($q['options'])): ?>
        <div class="mt-2 d-flex flex-wrap gap-1">
            <?php foreach ($q['options'] as $opt): ?>
            <span class="badge" style="font-size:.7rem;background:<?= $opt['is_correct']?'#dcfce7':'#f1f5f9' ?>;color:<?= $opt['is_correct']?'#166534':'#475569' ?>;border-radius:6px;font-weight:600">
                <?php if ($opt['is_correct']): ?><i class="fas fa-check me-1"></i><?php endif; ?>
                <?= e(mb_strimwidth($opt['option_text'], 0, 40, '…')) ?>
                <?php if ($opt['match_pair']): ?><i class="fas fa-arrow-right mx-1"></i><?= e($opt['match_pair']) ?><?php endif; ?>
            </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($q['explanation']): ?>
        <div class="text-muted small mt-1"><i class="fas fa-lightbulb me-1 text-warning"></i><?= e(mb_strimwidth($q['explanation'], 0, 80, '…')) ?></div>
        <?php endif; ?>
    </div>
    <div class="d-flex gap-1 flex-shrink-0">
        <button class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.75rem" onclick="editQuestion(<?= htmlspecialchars(json_encode($q)) ?>)"><i class="fas fa-edit"></i></button>
        <button class="btn btn-sm btn-outline-danger" style="border-radius:8px;font-size:.75rem" onclick="deleteQuestion(<?= $q['id'] ?>)"><i class="fas fa-trash"></i></button>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Question Modal -->
<div class="modal fade" id="qModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:14px;border:none">
            <div class="modal-header" style="background:#f8f7ff;border-bottom:1px solid #e8e3ff">
                <h5 class="modal-title fw-bold" id="qModalTitle"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="editQid" value="">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Question Type</label>
                    <select id="qType" class="form-select" onchange="renderOptionsUI()">
                        <?php foreach ($types as $v => $l): ?>
                        <option value="<?= $v ?>"><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Question Text <span class="text-danger">*</span></label>
                    <textarea id="qText" class="form-control" rows="3" placeholder="Enter your question…"></textarea>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Points</label>
                        <input type="number" id="qPoints" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Explanation (shown after answer)</label>
                        <input type="text" id="qExplanation" class="form-control" placeholder="Optional…">
                    </div>
                </div>

                <!-- Options builder -->
                <div id="optionsSection">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label fw-semibold small mb-0">Answer Options <span class="text-muted small fw-normal" id="optHint">(click circle to mark correct)</span></label>
                        <button type="button" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.75rem" id="addOptBtn" onclick="addOption()"><i class="fas fa-plus me-1"></i>Add Option</button>
                    </div>
                    <div id="optionRows"></div>
                </div>
                <div id="shortSection" class="d-none">
                    <div class="alert alert-info py-2 small"><i class="fas fa-info-circle me-1"></i>Short answer / fill-in-blank questions require manual grading by the instructor.</div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e8e3ff">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius:9px">Cancel</button>
                <button type="button" class="btn btn-primary" style="border-radius:9px" onclick="saveQuestion()"><i class="fas fa-save me-2"></i>Save Question</button>
            </div>
        </div>
    </div>
</div>

<script>
const SAVE_URL  = '<?= url("elms/quizzes/{$quiz['id']}/questions/save") ?>';
const DEL_URL   = '<?= url("elms/quizzes/{$quiz['id']}/questions") ?>';
const REORD_URL = '<?= url("elms/quizzes/{$quiz['id']}/questions/reorder") ?>';
const CSRF      = '<?= csrfToken() ?>';
const qModal    = new bootstrap.Modal(document.getElementById('qModal'));

function openAddModal() {
    document.getElementById('qModalTitle').innerHTML = '<i class="fas fa-plus-circle me-2 text-primary"></i>Add Question';
    document.getElementById('editQid').value = '';
    document.getElementById('qType').value = 'mcq';
    document.getElementById('qText').value = '';
    document.getElementById('qPoints').value = 1;
    document.getElementById('qExplanation').value = '';
    renderOptionsUI();
    qModal.show();
}

function editQuestion(q) {
    document.getElementById('qModalTitle').innerHTML = '<i class="fas fa-edit me-2 text-primary"></i>Edit Question';
    document.getElementById('editQid').value = q.id;
    document.getElementById('qType').value = q.type;
    document.getElementById('qText').value = q.question;
    document.getElementById('qPoints').value = q.points;
    document.getElementById('qExplanation').value = q.explanation || '';
    renderOptionsUI(q.options || []);
    qModal.show();
}

function renderOptionsUI(existing = []) {
    const type = document.getElementById('qType').value;
    const optSec   = document.getElementById('optionsSection');
    const shortSec = document.getElementById('shortSection');
    const addBtn   = document.getElementById('addOptBtn');
    const hint     = document.getElementById('optHint');

    if (['short','fill_blank'].includes(type)) {
        optSec.classList.add('d-none');
        shortSec.classList.remove('d-none');
        return;
    }
    optSec.classList.remove('d-none');
    shortSec.classList.add('d-none');

    const rows = document.getElementById('optionRows');
    rows.innerHTML = '';

    if (type === 'true_false') {
        addBtn.style.display = 'none';
        hint.textContent = '(click circle to mark correct)';
        const defaults = existing.length ? existing : [{option_text:'True',is_correct:1,match_pair:''},{option_text:'False',is_correct:0,match_pair:''}];
        defaults.forEach(o => addOptionRow(o.option_text, o.is_correct, o.match_pair||'', type === 'multi'));
        return;
    }
    addBtn.style.display = '';
    const isMulti = type === 'multi';
    const isMatch = type === 'match';
    hint.textContent = isMulti ? '(select all correct answers)' : isMatch ? '(enter matching pair)' : '(click circle to mark correct)';

    if (existing.length) {
        existing.forEach(o => addOptionRow(o.option_text, o.is_correct, o.match_pair||'', isMulti, isMatch));
    } else {
        addOptionRow('', false, '', isMulti, isMatch);
        addOptionRow('', false, '', isMulti, isMatch);
    }
}

function addOption() {
    const type = document.getElementById('qType').value;
    addOptionRow('', false, '', type === 'multi', type === 'match');
}

function addOptionRow(text='', correct=false, matchPair='', isMulti=false, isMatch=false) {
    const rows = document.getElementById('optionRows');
    const div  = document.createElement('div');
    div.className = 'opt-row';
    div.innerHTML = `
        <div class="correct-dot ${correct?'active':''}" onclick="toggleCorrect(this, ${isMulti})" title="Mark correct"></div>
        <input type="text" class="form-control form-control-sm opt-text" value="${text.replace(/"/g,'&quot;')}" placeholder="Option text…">
        ${isMatch ? `<input type="text" class="form-control form-control-sm opt-match" value="${matchPair.replace(/"/g,'&quot;')}" placeholder="Matches with…" style="max-width:160px">` : ''}
        <button type="button" class="btn btn-sm btn-outline-danger" style="border-radius:7px;padding:.2rem .5rem" onclick="this.closest('.opt-row').remove()"><i class="fas fa-times"></i></button>`;
    rows.appendChild(div);
}

function toggleCorrect(dot, isMulti) {
    if (!isMulti) {
        dot.closest('#optionRows').querySelectorAll('.correct-dot').forEach(d => d.classList.remove('active'));
    }
    dot.classList.toggle('active');
}

function getOptions() {
    const type = document.getElementById('qType').value;
    if (['short','fill_blank'].includes(type)) return [];
    const isMatch = type === 'match';
    return [...document.querySelectorAll('#optionRows .opt-row')].map(row => ({
        text:       row.querySelector('.opt-text')?.value.trim() || '',
        is_correct: row.querySelector('.correct-dot')?.classList.contains('active') ? 1 : 0,
        match_pair: isMatch ? (row.querySelector('.opt-match')?.value.trim() || '') : '',
    })).filter(o => o.text);
}

function saveQuestion() {
    const qid = document.getElementById('editQid').value;
    const q   = document.getElementById('qText').value.trim();
    if (!q) { alert('Question text is required.'); return; }

    const payload = {
        _csrf:       CSRF,
        question_id: qid || 0,
        type:        document.getElementById('qType').value,
        question:    q,
        points:      document.getElementById('qPoints').value,
        explanation: document.getElementById('qExplanation').value.trim(),
        options:     getOptions(),
    };

    fetch(SAVE_URL, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        qModal.hide();
        location.reload();
    })
    .catch(() => alert('Failed to save question'));
}

function deleteQuestion(qid) {
    if (!confirm('Delete this question?')) return;
    fetch(`${DEL_URL}/${qid}/delete`, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
        body: '_csrf=' + encodeURIComponent(CSRF),
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'ok') {
            document.getElementById('qCard' + qid)?.remove();
            const empState = document.getElementById('emptyState');
            const remaining = document.querySelectorAll('.qb-card').length;
            document.getElementById('qCount').textContent = remaining;
            if (remaining === 0 && empState) empState.style.display = '';
        }
    })
    .catch(() => alert('Failed to delete question'));
}

// Drag-to-reorder
(function initSortable() {
    const list = document.getElementById('questionList');
    let dragging = null;
    list.addEventListener('mousedown', e => {
        const handle = e.target.closest('.qb-drag-handle');
        if (!handle) return;
        dragging = handle.closest('.qb-card');
    });
    list.addEventListener('dragstart', e => { if (dragging) e.dataTransfer.effectAllowed = 'move'; });
    list.querySelectorAll('.qb-card').forEach(card => {
        card.setAttribute('draggable', 'true');
        card.addEventListener('dragover', e => { e.preventDefault(); list.insertBefore(dragging, card); });
    });
    list.addEventListener('dragend', () => {
        dragging = null;
        const order = [...list.querySelectorAll('.qb-card')].map((c,i) => c.dataset.qid);
        fetch(REORD_URL, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({_csrf: CSRF, order}),
        });
    });
})();
</script>
