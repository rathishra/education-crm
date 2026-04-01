<?php $pageTitle = 'Exam Schemes'; ?>

<style>
.schema-tree-wrap   { height: calc(100vh - 180px); overflow-y: auto; }
.schema-node        { cursor: pointer; border-left: 3px solid transparent; transition: all .15s; }
.schema-node:hover  { background: #f8f9ff; border-left-color: #c7d2fe; }
.schema-node.active { background: #eef2ff; border-left-color: #4f46e5; }
.schema-node .badge-code { font-size:.65rem; letter-spacing:.04em; }
.child-comp         { font-size:.78rem; color:#6b7280; padding:.15rem 0 .15rem 1.4rem; }
.child-comp i       { font-size:.65rem; width:12px; }
.right-panel        { min-height: calc(100vh - 180px); }
.tab-pane-custom    { min-height: 300px; }
.rule-pass   { background:#f0fdf4; }
.rule-fail   { background:#fef2f2; }
#schemaTree .expand-btn { font-size:.7rem; color:#9ca3af; cursor:pointer; user-select:none; }
.comp-type-badge { font-size:.68rem; text-transform:uppercase; letter-spacing:.06em; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold">Exam Schemes</h4>
        <p class="text-muted small mb-0">Configure Internal / External mark structures and grade rules per scheme</p>
    </div>
    <button class="btn btn-primary" onclick="openNewSchemaModal()">
        <i class="fas fa-plus me-1"></i>New Exam Scheme
    </button>
</div>

<div class="row g-0 border rounded-3 overflow-hidden shadow-sm bg-white">

    <!-- ── LEFT: Schema Tree ── -->
    <div class="col-lg-3 border-end">
        <div class="px-3 py-2 border-bottom bg-light d-flex justify-content-between align-items-center">
            <span class="small fw-bold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em">Scheme List</span>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><?= count($schemas) ?></span>
        </div>
        <div class="schema-tree-wrap p-2" id="schemaTree">
            <?php if(empty($schemas)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-layer-group fa-2x d-block mb-2 opacity-25"></i>
                <div class="small">No schemes yet.<br>Click <strong>New Exam Scheme</strong> to begin.</div>
            </div>
            <?php else: foreach($schemas as $s):
                $comps = $components[$s['id']] ?? [];
            ?>
            <div class="schema-node rounded-2 p-2 mb-1" data-sid="<?= $s['id'] ?>" onclick="loadSchema(<?= $s['id'] ?>)">
                <div class="d-flex align-items-center gap-2">
                    <?php if(!empty($comps)): ?>
                    <span class="expand-btn" onclick="toggleChildren(event,<?= $s['id'] ?>)">
                        <i class="fas fa-chevron-right" id="expandIcon<?= $s['id'] ?>"></i>
                    </span>
                    <?php else: ?>
                    <span style="width:12px;display:inline-block"></span>
                    <?php endif; ?>
                    <i class="fas fa-clipboard-list text-primary" style="font-size:.8rem"></i>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold small text-truncate"><?= e($s['code']) ?></div>
                        <div class="text-muted" style="font-size:.68rem"><?= e($s['name']) ?></div>
                    </div>
                    <?php if($s['status']==='active'): ?>
                    <i class="fas fa-check-circle text-success" style="font-size:.75rem"></i>
                    <?php endif; ?>
                </div>
                <?php if(!empty($comps)): ?>
                <div id="children<?= $s['id'] ?>" style="display:none">
                    <?php foreach($comps as $c): ?>
                    <div class="child-comp">
                        <i class="fas fa-circle-dot" style="color:<?= ['internal'=>'#10b981','external'=>'#3b82f6','practical'=>'#f59e0b','viva'=>'#8b5cf6','project'=>'#ec4899','other'=>'#6b7280'][$c['component_type']]??'#6b7280' ?>"></i>
                        <?= e($c['name']) ?> (<?= number_format($c['min_mark'],2) ?> – <?= number_format($c['max_mark'],2) ?>)
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- ── RIGHT: Detail Panel ── -->
    <div class="col-lg-9 right-panel" id="detailPanel">
        <div id="emptyState" class="d-flex flex-column align-items-center justify-content-center h-100 text-muted py-5">
            <i class="fas fa-hand-pointer fa-3x mb-3 opacity-25"></i>
            <div class="fw-semibold">Select a scheme from the list</div>
            <div class="small opacity-75">or create a new one to get started</div>
        </div>

        <div id="schemaDetail" style="display:none">
            <!-- Header -->
            <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between bg-light">
                <div>
                    <span class="badge bg-primary me-2 schema-hdr-code fw-bold" style="font-size:.85rem"></span>
                    <span class="schema-hdr-name fw-semibold"></span>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle schema-hdr-status" style="display:none">Active</span>
                    <span id="hdrEmbeddedBadge" class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="display:none">Embedded</span>
                    <span id="hdrRatioBadge"    class="badge bg-info-subtle text-info border border-info-subtle"               style="display:none">Max Ratio</span>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs px-4 pt-2" id="schemaTabList">
                <?php foreach(['scheme'=>['Exam Scheme','fa-clipboard-list','primary'],'category'=>['Category','fa-folder','secondary'],'component'=>['Component','fa-code-branch','info'],'subcomponent'=>['Sub Component','fa-list-ul','warning'],'rules'=>['Grade Rules','fa-star','success']] as $tab=>[$label,$icon,$color]): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $tab==='scheme'?'active':'' ?> px-3 py-2 small fw-semibold" href="#"
                       data-tab="<?= $tab ?>" onclick="switchTab(event,'<?= $tab ?>')">
                        <i class="fas <?= $icon ?> me-1 text-<?= $color ?>"></i><?= $label ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

            <!-- Tab Content -->
            <div class="p-4" id="tabContentWrap"></div>
        </div>
    </div>

</div>

<!-- ──────────────── NEW SCHEMA MODAL ──────────────── -->
<div class="modal fade" id="modalNewSchema" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2 text-primary"></i>New Exam Scheme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                        <input type="text" id="ns_code" class="form-control text-uppercase" placeholder="e.g. 21_THEORY">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" id="ns_name" class="form-control" placeholder="e.g. Theory 2021">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Min Mark (Pass)</label>
                        <input type="number" id="ns_min" class="form-control" value="50" step="0.5">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Max Mark (Total)</label>
                        <input type="number" id="ns_max" class="form-control" value="100" step="0.5">
                    </div>
                    <div class="col-12 d-flex gap-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ns_embedded">
                            <label class="form-check-label" for="ns_embedded">Embedded</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ns_ratio">
                            <label class="form-check-label" for="ns_ratio">Max Ratio Mark</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="saveNewSchema()"><i class="fas fa-save me-1"></i>Save Scheme</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE = '<?= url('') ?>';
const INST_ID = <?= $this->institutionId ?? 0 ?>;
let activeSchemaId = null;
let activeData = null;
let activeTab = 'scheme';
let editingComponentId = null;

// ─── TREE ────────────────────────────────────────────────────────
function toggleChildren(e, id) {
    e.stopPropagation();
    const el = document.getElementById('children' + id);
    const ic = document.getElementById('expandIcon' + id);
    if (!el) return;
    const open = el.style.display !== 'none';
    el.style.display = open ? 'none' : 'block';
    ic.className = open ? 'fas fa-chevron-right' : 'fas fa-chevron-down';
}

// ─── LOAD SCHEMA ─────────────────────────────────────────────────
async function loadSchema(id) {
    document.querySelectorAll('.schema-node').forEach(n => n.classList.remove('active'));
    const node = document.querySelector('[data-sid="' + id + '"]');
    if (node) node.classList.add('active');

    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('schemaDetail').style.display = 'block';
    document.getElementById('tabContentWrap').innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';

    const resp = await fetch(BASE + 'academic/grading-schemas/' + id + '/detail');
    activeData = await resp.json();
    activeSchemaId = id;

    // Header
    document.querySelector('.schema-hdr-code').textContent = activeData.schema.code;
    document.querySelector('.schema-hdr-name').textContent = activeData.schema.name;
    document.getElementById('hdrEmbeddedBadge').style.display = activeData.schema.is_embedded ? 'inline-block' : 'none';
    document.getElementById('hdrRatioBadge').style.display    = activeData.schema.max_ratio_mark ? 'inline-block' : 'none';

    switchTabContent(activeTab);
}

// ─── TAB SWITCHING ───────────────────────────────────────────────
function switchTab(e, tab) {
    e.preventDefault();
    document.querySelectorAll('#schemaTabList .nav-link').forEach(a => a.classList.remove('active'));
    e.target.closest('.nav-link').classList.add('active');
    activeTab = tab;
    switchTabContent(tab);
}

function switchTabContent(tab) {
    const wrap = document.getElementById('tabContentWrap');
    if (tab === 'scheme')       wrap.innerHTML = renderSchemeTab();
    else if (tab === 'category')     wrap.innerHTML = renderCategoryTab();
    else if (tab === 'component')    wrap.innerHTML = renderComponentTab();
    else if (tab === 'subcomponent') wrap.innerHTML = renderSubComponentTab();
    else if (tab === 'rules')        wrap.innerHTML = renderRulesTab();
}

// ─── SCHEME TAB ──────────────────────────────────────────────────
function renderSchemeTab() {
    const s = activeData.schema;
    return `
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
            <input type="text" id="st_code" class="form-control text-uppercase" value="${esc(s.code)}">
        </div>
        <div class="col-md-8">
            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
            <input type="text" id="st_name" class="form-control" value="${esc(s.name)}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Min Mark (Pass Total)</label>
            <input type="number" id="st_min" class="form-control" value="${s.min_mark}" step="0.5">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Max Mark (Total)</label>
            <input type="number" id="st_max" class="form-control" value="${s.max_mark}" step="0.5">
        </div>
        <div class="col-12 d-flex gap-4 align-items-center">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="st_embedded" ${s.is_embedded ? 'checked' : ''}>
                <label class="form-check-label" for="st_embedded">Embedded</label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="st_ratio" ${s.max_ratio_mark ? 'checked' : ''}>
                <label class="form-check-label" for="st_ratio">Max Ratio Mark</label>
            </div>
        </div>
        <div class="col-12 d-flex justify-content-between align-items-center pt-2 border-top">
            <button class="btn btn-outline-danger btn-sm" onclick="deleteSchema()"><i class="fas fa-trash me-1"></i>Delete</button>
            <div class="d-flex gap-2">
                <button class="btn btn-light btn-sm" onclick="loadSchema(activeSchemaId)"><i class="fas fa-undo me-1"></i>Clear</button>
                <button class="btn btn-primary" onclick="saveScheme()"><i class="fas fa-save me-1"></i>Save</button>
            </div>
        </div>
    </div>`;
}

async function saveScheme() {
    const data = {
        code: document.getElementById('st_code').value,
        name: document.getElementById('st_name').value,
        min_mark: document.getElementById('st_min').value,
        max_mark: document.getElementById('st_max').value,
        is_embedded: document.getElementById('st_embedded').checked ? 1 : 0,
        max_ratio_mark: document.getElementById('st_ratio').checked ? 1 : 0,
    };
    const resp = await postJSON(BASE + 'academic/grading-schemas/update/' + activeSchemaId, data);
    if (resp.status === 'success') {
        toast('success', resp.message);
        activeData.schema = resp.schema;
        document.querySelector('.schema-hdr-code').textContent = resp.schema.code;
        document.querySelector('.schema-hdr-name').textContent = resp.schema.name;
        document.getElementById('hdrEmbeddedBadge').style.display = resp.schema.is_embedded ? 'inline-block' : 'none';
        document.getElementById('hdrRatioBadge').style.display    = resp.schema.max_ratio_mark ? 'inline-block' : 'none';
        // Update tree label
        const node = document.querySelector('[data-sid="' + activeSchemaId + '"]');
        if (node) {
            node.querySelector('.fw-semibold').textContent = resp.schema.code;
            node.querySelector('.text-muted').textContent  = resp.schema.name;
        }
    } else {
        toast('error', resp.message);
    }
}

async function deleteSchema() {
    if (!confirm('Delete this scheme and all its components, rules, and sub-components?')) return;
    const resp = await postJSON(BASE + 'academic/grading-schemas/delete/' + activeSchemaId, {});
    if (resp.status === 'success') {
        toast('success', resp.message);
        document.querySelector('[data-sid="' + activeSchemaId + '"]')?.remove();
        document.getElementById('schemaDetail').style.display = 'none';
        document.getElementById('emptyState').style.display = 'flex';
        activeSchemaId = null; activeData = null;
    } else {
        toast('error', resp.message);
    }
}

// ─── CATEGORY TAB ────────────────────────────────────────────────
function renderCategoryTab() {
    const cats = activeData.categories || [];
    let rows = cats.map(c => `
        <tr>
            <td class="fw-semibold small">${esc(c.code)}</td>
            <td class="small">${esc(c.name)}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(${c.id})"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`).join('') || `<tr><td colspan="3" class="text-center text-muted py-3 small">No categories yet.</td></tr>`;

    return `
    <div class="mb-4">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Code</th><th>Name</th><th class="text-end">Action</th></tr>
                </thead>
                <tbody id="catTbody">${rows}</tbody>
            </table>
        </div>
    </div>
    <div class="card border-0 bg-light">
        <div class="card-body">
            <h6 class="fw-bold mb-3"><i class="fas fa-plus me-2 text-secondary"></i>Add Category</h6>
            <div class="row g-2">
                <div class="col-md-4"><input type="text" id="cat_code" class="form-control form-control-sm text-uppercase" placeholder="Code"></div>
                <div class="col-md-6"><input type="text" id="cat_name" class="form-control form-control-sm" placeholder="Category Name"></div>
                <div class="col-md-2"><button class="btn btn-sm btn-primary w-100" onclick="addCategory()"><i class="fas fa-plus"></i></button></div>
            </div>
        </div>
    </div>`;
}

async function addCategory() {
    const resp = await postJSON(BASE + 'academic/grading-schemas/categories/store', {
        schema_id: activeSchemaId,
        code: document.getElementById('cat_code').value,
        name: document.getElementById('cat_name').value,
    });
    if (resp.status === 'success') {
        activeData.categories.push(resp.category);
        toast('success', resp.message);
        document.getElementById('tabContentWrap').innerHTML = renderCategoryTab();
    } else { toast('error', resp.message); }
}

async function deleteCategory(id) {
    if (!confirm('Delete this category?')) return;
    const resp = await postJSON(BASE + 'academic/grading-schemas/categories/delete/' + id, {});
    if (resp.status === 'success') {
        activeData.categories = activeData.categories.filter(c => c.id != id);
        toast('success', resp.message);
        document.getElementById('tabContentWrap').innerHTML = renderCategoryTab();
    } else { toast('error', resp.message); }
}

// ─── COMPONENT TAB ───────────────────────────────────────────────
const compTypeColor = {internal:'#10b981',external:'#3b82f6',practical:'#f59e0b',viva:'#8b5cf6',project:'#ec4899',other:'#6b7280'};
const compTypeLabel = {internal:'Internal',external:'External',practical:'Practical',viva:'Viva',project:'Project',other:'Other'};

function renderComponentTab() {
    const comps = activeData.components || [];
    editingComponentId = null;

    let rows = comps.map(c => {
        const clr = compTypeColor[c.component_type] || '#6b7280';
        return `
        <tr id="compRow${c.id}">
            <td><span class="badge rounded-pill comp-type-badge" style="background:${clr}20;color:${clr};border:1px solid ${clr}40">${compTypeLabel[c.component_type]||c.component_type}</span></td>
            <td class="fw-semibold small">${esc(c.code)}</td>
            <td class="small">${esc(c.name)}</td>
            <td class="small text-center">${parseFloat(c.min_mark).toFixed(2)}</td>
            <td class="small text-center fw-bold">${parseFloat(c.max_mark).toFixed(2)}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editComponent(${c.id})"><i class="fas fa-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteComponent(${c.id})"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;
    }).join('') || `<tr><td colspan="6" class="text-center text-muted py-3 small">No components yet.</td></tr>`;

    const catOptions = (activeData.categories || []).map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');

    return `
    <div class="table-responsive mb-4">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Type</th><th>Code</th><th>Name</th><th class="text-center">Min Mark</th><th class="text-center">Max Mark</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody id="compTbody">${rows}</tbody>
        </table>
    </div>
    <div class="card border-0 bg-light">
        <div class="card-body">
            <h6 class="fw-bold mb-3" id="compFormTitle"><i class="fas fa-plus me-2 text-info"></i>Add Component</h6>
            <div class="row g-2">
                <div class="col-md-3">
                    <select id="comp_type" class="form-select form-select-sm">
                        <option value="internal">Internal</option>
                        <option value="external">External</option>
                        <option value="practical">Practical</option>
                        <option value="viva">Viva</option>
                        <option value="project">Project</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-2"><input type="text" id="comp_code" class="form-control form-control-sm text-uppercase" placeholder="Code (INT)"></div>
                <div class="col-md-3"><input type="text" id="comp_name" class="form-control form-control-sm" placeholder="Name (Internal)"></div>
                <div class="col-md-2"><input type="number" id="comp_min" class="form-control form-control-sm" placeholder="Min" value="0" step="0.5"></div>
                <div class="col-md-2"><input type="number" id="comp_max" class="form-control form-control-sm" placeholder="Max" value="40" step="0.5"></div>
                ${catOptions ? `<div class="col-md-4"><select id="comp_cat" class="form-select form-select-sm"><option value="">— No Category —</option>${catOptions}</select></div>` : ''}
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-sm btn-primary" onclick="saveComponent()"><i class="fas fa-save me-1"></i>Save Component</button>
                    <button class="btn btn-sm btn-light" onclick="cancelCompEdit()" id="cancelCompBtn" style="display:none">Cancel</button>
                </div>
            </div>
        </div>
    </div>`;
}

function editComponent(id) {
    const c = activeData.components.find(x => x.id == id);
    if (!c) return;
    editingComponentId = id;
    document.getElementById('comp_type').value = c.component_type;
    document.getElementById('comp_code').value = c.code;
    document.getElementById('comp_name').value = c.name;
    document.getElementById('comp_min').value  = c.min_mark;
    document.getElementById('comp_max').value  = c.max_mark;
    document.getElementById('compFormTitle').innerHTML = '<i class="fas fa-pencil me-2 text-warning"></i>Edit Component';
    document.getElementById('cancelCompBtn').style.display = 'inline-block';
    document.querySelector('.card.bg-light').scrollIntoView({behavior:'smooth'});
}

function cancelCompEdit() {
    editingComponentId = null;
    document.getElementById('tabContentWrap').innerHTML = renderComponentTab();
}

async function saveComponent() {
    const data = {
        schema_id: activeSchemaId,
        component_type: document.getElementById('comp_type').value,
        code: document.getElementById('comp_code').value,
        name: document.getElementById('comp_name').value,
        min_mark: document.getElementById('comp_min').value,
        max_mark: document.getElementById('comp_max').value,
        category_id: document.getElementById('comp_cat')?.value || '',
    };
    let url, msg;
    if (editingComponentId) {
        url = BASE + 'academic/grading-schemas/components/update/' + editingComponentId;
    } else {
        url = BASE + 'academic/grading-schemas/components/store';
    }
    const resp = await postJSON(url, data);
    if (resp.status === 'success') {
        toast('success', resp.message);
        if (editingComponentId) {
            activeData.components = activeData.components.map(c => c.id == editingComponentId ? resp.component : c);
        } else {
            activeData.components.push(resp.component);
        }
        editingComponentId = null;
        document.getElementById('tabContentWrap').innerHTML = renderComponentTab();
        refreshTreeChildren();
    } else { toast('error', resp.message); }
}

async function deleteComponent(id) {
    if (!confirm('Delete this component and all its sub-components?')) return;
    const resp = await postJSON(BASE + 'academic/grading-schemas/components/delete/' + id, {});
    if (resp.status === 'success') {
        activeData.components = activeData.components.filter(c => c.id != id);
        activeData.sub_components = activeData.sub_components.filter(s => s.component_id != id);
        toast('success', resp.message);
        document.getElementById('tabContentWrap').innerHTML = renderComponentTab();
        refreshTreeChildren();
    } else { toast('error', resp.message); }
}

// ─── SUB-COMPONENT TAB ───────────────────────────────────────────
function renderSubComponentTab() {
    const comps = activeData.components || [];
    const subs  = activeData.sub_components || [];
    if (!comps.length) return '<div class="text-center py-5 text-muted"><i class="fas fa-info-circle me-2"></i>Add Components first before adding Sub-Components.</div>';

    // Group subs by component
    const grouped = {};
    subs.forEach(s => { if (!grouped[s.component_id]) grouped[s.component_id] = []; grouped[s.component_id].push(s); });

    let html = '';
    comps.forEach(c => {
        const subsForComp = grouped[c.id] || [];
        const clr = compTypeColor[c.component_type] || '#6b7280';
        html += `
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge comp-type-badge" style="background:${clr}20;color:${clr};border:1px solid ${clr}40">${compTypeLabel[c.component_type]||c.component_type}</span>
                <span class="fw-bold small">${esc(c.name)}</span>
                <span class="text-muted small">(Max: ${parseFloat(c.max_mark).toFixed(2)})</span>
            </div>
            <div class="table-responsive mb-2">
                <table class="table table-sm align-middle mb-0 border rounded-2">
                    <thead class="table-light">
                        <tr><th>Code</th><th>Name</th><th class="text-center">Max Mark</th><th class="text-end">Action</th></tr>
                    </thead>
                    <tbody>
                        ${subsForComp.map(s => `
                        <tr>
                            <td class="fw-semibold small">${esc(s.code)}</td>
                            <td class="small">${esc(s.name)}</td>
                            <td class="text-center small">${parseFloat(s.max_mark).toFixed(2)}</td>
                            <td class="text-end"><button class="btn btn-xs btn-outline-danger py-0 px-1" onclick="deleteSubComponent(${s.id})"><i class="fas fa-trash" style="font-size:.7rem"></i></button></td>
                        </tr>`).join('') || `<tr><td colspan="4" class="text-center text-muted py-2 small">No sub-components.</td></tr>`}
                    </tbody>
                </table>
            </div>
            <div class="d-flex gap-2 align-items-center ps-1">
                <input type="text" class="form-control form-control-sm text-uppercase" id="sc_code_${c.id}" placeholder="Code" style="width:90px">
                <input type="text" class="form-control form-control-sm" id="sc_name_${c.id}" placeholder="Sub-component name" style="width:200px">
                <input type="number" class="form-control form-control-sm" id="sc_max_${c.id}" placeholder="Max" value="0" step="0.5" style="width:80px">
                <button class="btn btn-sm btn-outline-primary" onclick="addSubComponent(${c.id})"><i class="fas fa-plus me-1"></i>Add</button>
            </div>
        </div>`;
    });
    return html;
}

async function addSubComponent(compId) {
    const resp = await postJSON(BASE + 'academic/grading-schemas/sub-components/store', {
        component_id: compId,
        schema_id:    activeSchemaId,
        code:     document.getElementById('sc_code_' + compId).value,
        name:     document.getElementById('sc_name_' + compId).value,
        max_mark: document.getElementById('sc_max_' + compId).value,
    });
    if (resp.status === 'success') {
        activeData.sub_components.push(resp.sub_component);
        toast('success', resp.message);
        document.getElementById('tabContentWrap').innerHTML = renderSubComponentTab();
    } else { toast('error', resp.message); }
}

async function deleteSubComponent(id) {
    if (!confirm('Delete this sub-component?')) return;
    const resp = await postJSON(BASE + 'academic/grading-schemas/sub-components/delete/' + id, {});
    if (resp.status === 'success') {
        activeData.sub_components = activeData.sub_components.filter(s => s.id != id);
        toast('success', resp.message);
        document.getElementById('tabContentWrap').innerHTML = renderSubComponentTab();
    } else { toast('error', resp.message); }
}

// ─── GRADE RULES TAB ─────────────────────────────────────────────
const PRESETS = {
    '10pt': [
        {grade_label:'O',  grade_point:10, min_percentage:91, max_percentage:100, description:'Outstanding',   is_pass:1},
        {grade_label:'A+', grade_point:9,  min_percentage:81, max_percentage:90,  description:'Excellent',     is_pass:1},
        {grade_label:'A',  grade_point:8,  min_percentage:71, max_percentage:80,  description:'Very Good',     is_pass:1},
        {grade_label:'B+', grade_point:7,  min_percentage:61, max_percentage:70,  description:'Good',          is_pass:1},
        {grade_label:'B',  grade_point:6,  min_percentage:56, max_percentage:60,  description:'Above Average', is_pass:1},
        {grade_label:'C',  grade_point:5,  min_percentage:50, max_percentage:55,  description:'Average',       is_pass:1},
        {grade_label:'RA', grade_point:0,  min_percentage:0,  max_percentage:49,  description:'Reappear',      is_pass:0},
    ],
    '4pt': [
        {grade_label:'A',  grade_point:4.0, min_percentage:90, max_percentage:100, description:'Excellent',    is_pass:1},
        {grade_label:'A-', grade_point:3.7, min_percentage:85, max_percentage:89,  description:'Very Good',    is_pass:1},
        {grade_label:'B+', grade_point:3.3, min_percentage:80, max_percentage:84,  description:'Good',         is_pass:1},
        {grade_label:'B',  grade_point:3.0, min_percentage:75, max_percentage:79,  description:'Above Avg',    is_pass:1},
        {grade_label:'B-', grade_point:2.7, min_percentage:70, max_percentage:74,  description:'Average',      is_pass:1},
        {grade_label:'C',  grade_point:2.0, min_percentage:50, max_percentage:69,  description:'Pass',         is_pass:1},
        {grade_label:'F',  grade_point:0,   min_percentage:0,  max_percentage:49,  description:'Fail',         is_pass:0},
    ],
    'pct': [
        {grade_label:'Distinction', grade_point:0, min_percentage:75, max_percentage:100, description:'Distinction', is_pass:1},
        {grade_label:'First Class', grade_point:0, min_percentage:60, max_percentage:74,  description:'First Class', is_pass:1},
        {grade_label:'Second Class',grade_point:0, min_percentage:50, max_percentage:59,  description:'Second Class',is_pass:1},
        {grade_label:'Pass',        grade_point:0, min_percentage:40, max_percentage:49,  description:'Pass',        is_pass:1},
        {grade_label:'Fail',        grade_point:0, min_percentage:0,  max_percentage:39,  description:'Fail',        is_pass:0},
    ]
};

function renderRulesTab() {
    const rules = activeData.rules || [];
    const ruleRows = rules.map(r => `
        <tr class="${r.is_pass ? 'rule-pass' : 'rule-fail'}">
            <td><span class="badge fw-bold ${r.is_pass ? 'bg-success' : 'bg-danger'}">${esc(r.grade_label)}</span></td>
            <td class="fw-bold text-center">${parseFloat(r.grade_point).toFixed(1)}</td>
            <td class="text-center small">${parseFloat(r.min_percentage).toFixed(1)}%</td>
            <td class="text-center small">${parseFloat(r.max_percentage).toFixed(1)}%</td>
            <td class="small text-muted">${esc(r.description||'')}</td>
            <td class="text-center">${r.is_pass ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</td>
            <td class="text-end"><button class="btn btn-xs btn-outline-danger py-0 px-1" onclick="deleteRule(${r.id})"><i class="fas fa-trash" style="font-size:.7rem"></i></button></td>
        </tr>`).join('') || `<tr><td colspan="7" class="text-center text-muted py-3 small">No grade rules yet. Load a preset or add manually.</td></tr>`;

    return `
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-star me-2 text-success"></i>Grade Bands</h6>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="loadPreset('10pt')">10-Point (Anna Univ)</button>
            <button class="btn btn-sm btn-outline-secondary" onclick="loadPreset('4pt')">4-Point (GPA)</button>
            <button class="btn btn-sm btn-outline-secondary" onclick="loadPreset('pct')">% Based</button>
        </div>
    </div>
    <div class="table-responsive mb-4">
        <table class="table table-sm align-middle border rounded-2 overflow-hidden mb-0">
            <thead class="table-dark">
                <tr><th>Grade</th><th class="text-center">Grade Point</th><th class="text-center">Min %</th><th class="text-center">Max %</th><th>Description</th><th class="text-center">Pass</th><th></th></tr>
            </thead>
            <tbody id="ruleTbody">${ruleRows}</tbody>
        </table>
    </div>
    <div class="card border-0 bg-light">
        <div class="card-body">
            <h6 class="fw-bold mb-3 small">Add Grade Rule</h6>
            <div class="row g-2 align-items-end">
                <div class="col-md-2"><label class="form-label small mb-1">Grade Label</label><input type="text" id="r_label" class="form-control form-control-sm text-uppercase" placeholder="A+" maxlength="10"></div>
                <div class="col-md-2"><label class="form-label small mb-1">Grade Point</label><input type="number" id="r_point" class="form-control form-control-sm" placeholder="9.0" step="0.1" value="0"></div>
                <div class="col-md-2"><label class="form-label small mb-1">Min %</label><input type="number" id="r_min" class="form-control form-control-sm" placeholder="81" step="0.1" value="0"></div>
                <div class="col-md-2"><label class="form-label small mb-1">Max %</label><input type="number" id="r_max" class="form-control form-control-sm" placeholder="90" step="0.1" value="100"></div>
                <div class="col-md-2"><label class="form-label small mb-1">Description</label><input type="text" id="r_desc" class="form-control form-control-sm" placeholder="Excellent"></div>
                <div class="col-md-1">
                    <label class="form-label small mb-1 d-block">Pass?</label>
                    <div class="form-check mt-1"><input class="form-check-input" type="checkbox" id="r_pass" checked></div>
                </div>
                <div class="col-md-1"><button class="btn btn-sm btn-primary w-100" onclick="addRule()"><i class="fas fa-plus"></i></button></div>
            </div>
        </div>
    </div>`;
}

async function addRule() {
    const resp = await postJSON(BASE + 'academic/grading-schemas/rules/store', {
        schema_id: activeSchemaId,
        grade_label:    document.getElementById('r_label').value,
        grade_point:    document.getElementById('r_point').value,
        min_percentage: document.getElementById('r_min').value,
        max_percentage: document.getElementById('r_max').value,
        description:    document.getElementById('r_desc').value,
        is_pass:        document.getElementById('r_pass').checked ? 1 : 0,
    });
    if (resp.status === 'success') {
        activeData.rules.push(resp.rule);
        activeData.rules.sort((a,b) => b.min_percentage - a.min_percentage);
        toast('success', resp.message);
        document.getElementById('tabContentWrap').innerHTML = renderRulesTab();
    } else { toast('error', resp.message); }
}

async function deleteRule(id) {
    if (!confirm('Remove this grade rule?')) return;
    const resp = await postJSON(BASE + 'academic/grading-schemas/rules/delete/' + id, {});
    if (resp.status === 'success') {
        activeData.rules = activeData.rules.filter(r => r.id != id);
        toast('success', resp.message);
        document.getElementById('tabContentWrap').innerHTML = renderRulesTab();
    } else { toast('error', resp.message); }
}

async function loadPreset(key) {
    if (!confirm('This will add the preset rules to this scheme (existing rules are kept). Proceed?')) return;
    const preset = PRESETS[key];
    let i = 0;
    for (const rule of preset) {
        const resp = await postJSON(BASE + 'academic/grading-schemas/rules/store', {
            schema_id: activeSchemaId, ...rule
        });
        if (resp.status === 'success') { activeData.rules.push(resp.rule); i++; }
    }
    activeData.rules.sort((a,b) => b.min_percentage - a.min_percentage);
    toast('success', `${i} grade rules added.`);
    document.getElementById('tabContentWrap').innerHTML = renderRulesTab();
}

// ─── NEW SCHEMA MODAL ────────────────────────────────────────────
function openNewSchemaModal() {
    new bootstrap.Modal(document.getElementById('modalNewSchema')).show();
}

async function saveNewSchema() {
    const resp = await postJSON(BASE + 'academic/grading-schemas/store', {
        code:     document.getElementById('ns_code').value,
        name:     document.getElementById('ns_name').value,
        min_mark: document.getElementById('ns_min').value,
        max_mark: document.getElementById('ns_max').value,
        is_embedded:    document.getElementById('ns_embedded').checked ? 1 : 0,
        max_ratio_mark: document.getElementById('ns_ratio').checked ? 1 : 0,
    });
    if (resp.status === 'success') {
        toast('success', resp.message);
        bootstrap.Modal.getInstance(document.getElementById('modalNewSchema')).hide();
        // Append to tree
        const s = resp.schema;
        const tree = document.getElementById('schemaTree');
        const empty = tree.querySelector('.text-center');
        if (empty) empty.remove();
        const div = document.createElement('div');
        div.className = 'schema-node rounded-2 p-2 mb-1';
        div.dataset.sid = s.id;
        div.onclick = () => loadSchema(s.id);
        div.innerHTML = `<div class="d-flex align-items-center gap-2">
            <span style="width:12px;display:inline-block"></span>
            <i class="fas fa-clipboard-list text-primary" style="font-size:.8rem"></i>
            <div class="flex-grow-1">
                <div class="fw-semibold small">${esc(s.code)}</div>
                <div class="text-muted" style="font-size:.68rem">${esc(s.name)}</div>
            </div>
            <i class="fas fa-check-circle text-success" style="font-size:.75rem"></i>
        </div>`;
        tree.appendChild(div);
        loadSchema(s.id);
    } else { toast('error', resp.message); }
}

// ─── TREE REFRESH HELPER ─────────────────────────────────────────
function refreshTreeChildren() {
    const node = document.querySelector('[data-sid="' + activeSchemaId + '"]');
    if (!node) return;
    // Remove old children div
    const old = node.querySelector('[id^="children"]');
    if (old) old.remove();
    // Remove expand btn if no components
    const comps = activeData.components;
    if (!comps.length) {
        const btn = node.querySelector('.expand-btn');
        if (btn) btn.remove();
        return;
    }
    // Rebuild children div
    const childDiv = document.createElement('div');
    childDiv.id = 'children' + activeSchemaId;
    childDiv.style.display = 'block';
    childDiv.innerHTML = comps.map(c => {
        const clr = compTypeColor[c.component_type] || '#6b7280';
        return `<div class="child-comp"><i class="fas fa-circle-dot" style="color:${clr}"></i> ${esc(c.name)} (${parseFloat(c.min_mark).toFixed(2)} – ${parseFloat(c.max_mark).toFixed(2)})</div>`;
    }).join('');
    node.appendChild(childDiv);
}

// ─── UTILITIES ───────────────────────────────────────────────────
function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function postJSON(url, data) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const body = new URLSearchParams({...data, csrf_token: csrf});
    const resp = await fetch(url, {method: 'POST', body});
    return resp.json();
}

function toast(type, msg) {
    if (typeof toastr !== 'undefined') {
        toastr[type](msg);
    } else {
        alert(msg);
    }
}
</script>
