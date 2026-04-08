<style>
.quiz-take-wrap { max-width:760px; margin:0 auto; }
.qt-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; padding:1.5rem; margin-bottom:1rem; }
.qt-q-num { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:50%; background:#ede9fe; color:#6366f1; font-size:.8rem; font-weight:800; flex-shrink:0 }
.opt-btn { display:flex; align-items:center; gap:.75rem; padding:.65rem .9rem; border-radius:10px; border:1.5px solid #e2e8f0; cursor:pointer; background:#fff; transition:border-color .15s,background .15s; margin-bottom:.5rem; text-align:left; width:100% }
.opt-btn:hover { border-color:#a5b4fc; background:#f5f3ff; }
.opt-btn.selected { border-color:#6366f1; background:#eef2ff; }
.opt-indicator { width:22px; height:22px; border-radius:50%; border:2px solid #d1d5db; flex-shrink:0; transition:all .15s; display:flex; align-items:center; justify-content:center; font-size:.75rem; color:#fff }
.opt-btn.selected .opt-indicator { background:#6366f1; border-color:#6366f1; }
.multi-indicator { border-radius:5px; }
.timer-badge { font-size:1rem; font-weight:800; padding:.3rem .9rem; border-radius:20px; background:#fff3e0; color:#d97706; border:1.5px solid #fbbf24; }
.timer-badge.urgent { background:#fee2e2; color:#dc2626; border-color:#f87171; animation:pulse 1s ease-in-out infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.6} }
.q-nav-dot { width:30px; height:30px; border-radius:50%; border:1.5px solid #e2e8f0; background:#f8fafc; font-size:.72rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748b; transition:all .15s; }
.q-nav-dot.answered { background:#ede9fe; border-color:#a5b4fc; color:#6366f1; }
.q-nav-dot.current { background:#6366f1; border-color:#6366f1; color:#fff; }
</style>

<div class="quiz-take-wrap">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-question-circle me-2 text-primary"></i><?= e($quiz['title']) ?></h5>
        <?php if ($quiz['time_limit_mins']): ?>
        <div class="timer-badge" id="timerBadge"><i class="fas fa-clock me-1"></i><span id="timerDisplay">--:--</span></div>
        <?php endif; ?>
    </div>

    <!-- Question navigator -->
    <div class="bg-white rounded-3 border p-3 mb-3" style="border-color:#e8e3ff!important">
        <div class="d-flex flex-wrap gap-1">
            <?php foreach ($questions as $i => $q): ?>
            <div class="q-nav-dot <?= $i===0?'current':'' ?>" id="dot<?= $i ?>" onclick="jumpTo(<?= $i ?>)"><?= $i+1 ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <form id="quizForm" method="POST" action="<?= url('elms/quizzes/'.$quiz['id'].'/attempt/'.$attempt['id'].'/submit') ?>">
        <?= csrfField() ?>

        <?php foreach ($questions as $i => $q): ?>
        <div class="qt-card" id="qPanel<?= $i ?>" style="<?= $i>0?'display:none':'' ?>">
            <div class="d-flex gap-3 align-items-start mb-3">
                <span class="qt-q-num"><?= $i+1 ?></span>
                <div style="flex:1">
                    <div class="fw-semibold mb-1" style="color:#0f172a;line-height:1.5"><?= nl2br(e($q['question'])) ?></div>
                    <div class="text-muted small"><?= (int)$q['points'] ?> point<?= $q['points']!=1?'s':'' ?></div>
                </div>
            </div>

            <?php if (in_array($q['type'], ['mcq','true_false'])): ?>
            <div class="opt-list" data-type="radio" data-qid="<?= $q['id'] ?>">
                <?php foreach ($q['options'] as $opt): ?>
                <button type="button" class="opt-btn" data-oid="<?= $opt['id'] ?>" onclick="selectOpt(this,'radio',<?= $i ?>)">
                    <div class="opt-indicator"><i class="fas fa-check" style="display:none;font-size:.6rem"></i></div>
                    <span class="small fw-semibold" style="color:#374151"><?= e($opt['option_text']) ?></span>
                </button>
                <?php endforeach; ?>
                <input type="hidden" name="answers[<?= $q['id'] ?>]" id="ans<?= $q['id'] ?>" value="">
            </div>

            <?php elseif ($q['type'] === 'multi'): ?>
            <div class="d-flex align-items-center gap-2 mb-2"><span class="badge bg-light text-muted small">Select all that apply</span></div>
            <div class="opt-list" data-type="multi" data-qid="<?= $q['id'] ?>">
                <?php foreach ($q['options'] as $opt): ?>
                <button type="button" class="opt-btn" data-oid="<?= $opt['id'] ?>" onclick="selectOpt(this,'multi',<?= $i ?>)">
                    <div class="opt-indicator multi-indicator"><i class="fas fa-check" style="display:none;font-size:.6rem"></i></div>
                    <span class="small fw-semibold" style="color:#374151"><?= e($opt['option_text']) ?></span>
                </button>
                <?php endforeach; ?>
            </div>
            <!-- Multi-select hidden inputs rendered on submit -->
            <div id="multiHidden<?= $q['id'] ?>"></div>

            <?php elseif (in_array($q['type'], ['short','fill_blank'])): ?>
            <textarea name="answers[<?= $q['id'] ?>]" class="form-control" rows="3"
                      placeholder="<?= $q['type']==='fill_blank'?'Fill in the blank…':'Type your answer…' ?>"
                      oninput="markAnswered(<?= $i ?>)"></textarea>

            <?php elseif ($q['type'] === 'match'): ?>
            <div class="d-flex align-items-center gap-2 mb-2"><span class="badge bg-light text-muted small">Match each item on the left with the correct item on the right</span></div>
            <?php $rights = array_map(fn($o) => $o['match_pair'], $q['options']); shuffle($rights); ?>
            <?php foreach ($q['options'] as $idx => $opt): ?>
            <div class="d-flex gap-2 align-items-center mb-2">
                <div class="form-control-sm bg-light border rounded px-2 py-1 small fw-semibold" style="min-width:120px;flex:1"><?= e($opt['option_text']) ?></div>
                <i class="fas fa-arrow-right text-muted"></i>
                <select name="answers[<?= $q['id'] ?>][<?= $opt['id'] ?>]" class="form-select form-select-sm" style="flex:1" onchange="markAnswered(<?= $i ?>)">
                    <option value="">— Select —</option>
                    <?php foreach ($rights as $r): ?>
                    <option value="<?= e($r) ?>"><?= e($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <button type="button" class="btn btn-outline-secondary" id="prevBtn" style="border-radius:9px;display:none" onclick="navigate(-1)"><i class="fas fa-chevron-left me-1"></i>Previous</button>
            <button type="button" class="btn btn-primary ms-auto" id="nextBtn" style="border-radius:9px" onclick="navigate(1)">Next <i class="fas fa-chevron-right ms-1"></i></button>
            <button type="button" class="btn btn-success ms-auto" id="submitBtn" style="border-radius:9px;display:none" onclick="confirmSubmit()"><i class="fas fa-paper-plane me-2"></i>Submit Quiz</button>
        </div>
    </form>
</div>

<script>
const totalQ   = <?= count($questions) ?>;
let current    = 0;
const answered = new Array(totalQ).fill(false);

function jumpTo(idx) {
    document.getElementById('qPanel'+current).style.display = 'none';
    document.getElementById('dot'+current).classList.remove('current');
    current = idx;
    document.getElementById('qPanel'+current).style.display = '';
    document.getElementById('dot'+current).classList.add('current');
    document.getElementById('prevBtn').style.display = current > 0 ? '' : 'none';
    document.getElementById('nextBtn').style.display = current < totalQ - 1 ? '' : 'none';
    document.getElementById('submitBtn').style.display = current === totalQ - 1 ? '' : 'none';
}

function navigate(dir) { jumpTo(Math.max(0, Math.min(totalQ - 1, current + dir)); }

function selectOpt(btn, type, idx) {
    const list = btn.closest('.opt-list');
    const qid  = list.dataset.qid;
    if (type === 'radio') {
        list.querySelectorAll('.opt-btn').forEach(b => {
            b.classList.remove('selected');
            b.querySelector('.opt-indicator i').style.display = 'none';
        });
        btn.classList.add('selected');
        btn.querySelector('.opt-indicator i').style.display = '';
        document.getElementById('ans'+qid).value = btn.dataset.oid;
    } else {
        btn.classList.toggle('selected');
        btn.querySelector('.opt-indicator i').style.display = btn.classList.contains('selected') ? '' : 'none';
        // Rebuild hidden inputs
        const cont = document.getElementById('multiHidden'+qid);
        cont.innerHTML = '';
        list.querySelectorAll('.opt-btn.selected').forEach(b => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = `answers[${qid}][]`;
            inp.value = b.dataset.oid;
            cont.appendChild(inp);
        });
    }
    markAnswered(idx);
}

function markAnswered(idx) {
    answered[idx] = true;
    document.getElementById('dot'+idx).classList.add('answered');
}

function confirmSubmit() {
    const unanswered = answered.filter(a => !a).length;
    const msg = unanswered > 0
        ? `You have ${unanswered} unanswered question(s). Submit anyway?`
        : 'Submit your quiz now?';
    if (confirm(msg)) document.getElementById('quizForm').submit();
}

// Fix navigate call syntax
function navigate(dir) {
    const next = Math.max(0, Math.min(totalQ - 1, current + dir));
    jumpTo(next);
}

// Timer
<?php if ($quiz['time_limit_mins']): ?>
(function() {
    const startedAt  = <?= strtotime($attempt['started_at']) * 1000 ?>;
    const limitMs    = <?= $quiz['time_limit_mins'] * 60 * 1000 ?>;
    const endTime    = startedAt + limitMs;

    function tick() {
        const left = Math.max(0, endTime - Date.now());
        const m    = String(Math.floor(left / 60000)).padStart(2,'0');
        const s    = String(Math.floor((left % 60000) / 1000)).padStart(2,'0');
        document.getElementById('timerDisplay').textContent = m + ':' + s;
        if (left < 120000) document.getElementById('timerBadge').classList.add('urgent');
        if (left <= 0) { document.getElementById('quizForm').submit(); return; }
        setTimeout(tick, 1000);
    }
    tick();
})();
<?php endif; ?>
</script>
