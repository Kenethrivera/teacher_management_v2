<style>
    /* 1. Page Spacing */
    .class-record-container {
        padding-bottom: 300px;
        /* Extra space at bottom for scrolling */
    }

    .table-container {
        overflow-x: auto;
        border: 1px solid #dee2e6;
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border-radius: 8px;
    }

    table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    /* 2. Sticky Headers */
    thead th {
        background: #2c3e50;
        /* Default Dark Background */
        color: white;
        /* Default White Text */
        font-weight: 600;
        font-size: 0.85rem;
        text-align: center;
        padding: 10px 4px;
        border: 1px solid #34495e;
        white-space: nowrap;
        vertical-align: middle;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    /* 3. Sticky First Column (Learner Name) */
    .sticky-col {
        position: sticky;
        left: 0;
        background-color: #fff;
        z-index: 11;
        border-right: 2px solid #dee2e6 !important;
        font-weight: 600;
        padding: 8px 12px !important;
        min-width: 220px;
        color: #212529;
    }

    /* Sticky Header for First Column */
    thead th.sticky-col {
        z-index: 12;
        background-color: #2c3e50;
        /* Match main header */
        color: white;
    }

    /* 4. COLOR CODING & TEXT FIXES */
    /* Written Works (Blue) */
    .bg-ww {
        background-color: #e7f5ff !important;
    }

    thead th.bg-ww {
        color: #004085;
        /* Dark Blue Text for Header */
        border-color: #b8daff;
    }

    /* Performance Tasks (Purple) */
    .bg-pt {
        background-color: #f3f0ff !important;
    }

    thead th.bg-pt {
        color: #3d007a;
        /* Dark Purple Text for Header */
        border-color: #d6d8db;
    }

    /* QA (Yellow) */
    .bg-qa {
        background-color: #fff9db !important;
    }

    thead th.bg-qa {
        color: #856404;
        /* Dark Yellow/Brown Text for Header */
        border-color: #ffeeba;
    }

    /* Final Grade (Green) */
    .bg-final {
        background-color: #d3f9d8 !important;
    }

    thead th.bg-final {
        color: #155724;
        /* Dark Green Text for Header */
        border-color: #c3e6cb;
    }

    /* 5. Inputs */
    .score-input,
    .max-score-input {
        width: 100%;
        max-width: 60px;
        border: 1px solid #ced4da;
        text-align: center;
        font-size: 0.95rem;
        font-weight: 600;
        color: #212529;
        padding: 6px 2px;
        border-radius: 4px;
    }

    .max-score-input {
        background: #fff3cd;
        border-color: #ffe69c;
        color: #856404;
    }

    .score-input:focus {
        outline: 2px solid #3498db;
        background: #ebf5fb;
    }

    /* 6. Buttons (+ and x) */
    .col-header-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .add-col-btn {
        background: #2c3e50;
        /* Dark button */
        border: none;
        color: white;
        border-radius: 4px;
        width: 20px;
        height: 20px;
        font-size: 14px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: 8px;
        cursor: pointer;
        transition: 0.2s;
    }

    .add-col-btn:hover {
        background: #000;
    }

    .del-col-btn {
        font-size: 14px;
        color: #dc3545;
        /* Red */
        cursor: pointer;
        font-weight: bold;
        margin-top: 4px;
        padding: 0 4px;
        border-radius: 4px;
    }

    .del-col-btn:hover {
        background-color: #ffebee;
    }

    /* 7. Computed Cells */
    .computed-cell {
        background-color: #f8f9fa;
        font-weight: 700;
        color: #495057;
    }

    /* Max Score Row Style */
    .max-score-row td {
        background: #fff9e6;
        font-weight: 700;
        border-bottom: 2px solid #dee2e6;
        vertical-align: middle;
    }
</style>

<div class="container-fluid px-0 class-record-container">
    <div id="alertContainerClassRecord"></div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 fw-bold mb-0">Class Record</h1>
            <small class="text-muted">DepEd Grading Sheet</small>
        </div>
        <div>
            <button class="btn btn-success me-2" onclick="saveAllGrades()" id="saveGradesBtn" disabled>
                <i data-lucide="save" style="width:16px;height:16px"></i> Save Changes
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#releaseModal" id="releaseBtn"
                disabled>
                <i data-lucide="send" style="width:16px;height:16px"></i> Release Grades
            </button>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold small">School Year</label>
                    <select id="cr_schoolYear" class="form-select"></select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Subject</label>
                    <select id="cr_subject" class="form-select" disabled></select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Section</label>
                    <select id="cr_section" class="form-select" disabled></select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Quarter</label>
                    <select id="cr_quarter" class="form-select" disabled>
                        <option value="">Select</option>
                        <option value="1">1st</option>
                        <option value="2">2nd</option>
                        <option value="3">3rd</option>
                        <option value="4">4th</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button id="cr_loadBtn" class="btn btn-primary w-100" disabled>Load</button>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info small mb-3 shadow-sm" id="weightInfo" style="display:none;">
        <strong><i data-lucide="info" style="width:14px"></i> DepEd Grading:</strong>
        Written Works (30%) | Performance Tasks (50%) | QA (20%)
        <span class="float-end text-muted">Yellow row = Max Scores (Edit to change total)</span>
    </div>

    <div class="table-container">
        <table id="gradeTable">
            <thead id="gradeThead"></thead>
            <tbody id="gradingTableBody">
                <tr>
                    <td colspan="20" class="text-center text-muted py-5">
                        <i data-lucide="table" style="width:32px;height:32px;opacity:0.5" class="mb-2"></i><br>
                        Select filters and click <b>Load</b> to view class record
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="releaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Release Grades</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning d-flex align-items-center">
                    <i data-lucide="alert-triangle" class="me-2"></i>
                    <div>Grades will be visible to students immediately.</div>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="releaseType" value="final" id="releaseFinal"
                        checked>
                    <label class="form-check-label" for="releaseFinal"><strong>Final Grade Only</strong></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="releaseType" value="full" id="releaseFull">
                    <label class="form-check-label" for="releaseFull"><strong>Full Breakdown</strong> (Show all
                        quizzes)</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmRelease()">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        const els = {
            sy: document.getElementById('cr_schoolYear'),
            sub: document.getElementById('cr_subject'),
            sec: document.getElementById('cr_section'),
            q: document.getElementById('cr_quarter'),
            load: document.getElementById('cr_loadBtn'),
            save: document.getElementById('saveGradesBtn'),
            release: document.getElementById('releaseBtn')
        };

        const WEIGHTS = { ww: 0.30, pt: 0.50, qa: 0.20 };
        let gradingData = [];
        let components = { ww: [], pt: [], qa: null };
        let assignmentId = null;
        let hasChanges = false;

        // INIT
        if (window.lucide) lucide.createIcons();

        fetch('api/get_school_years.php').then(r => r.json()).then(d => {
            els.sy.innerHTML = '<option value="">Select School Year</option>';
            d.forEach(i => els.sy.innerHTML += `<option value="${i.id}">${i.school_year}</option>`);
        });

        // EVENTS
        els.sy.addEventListener('change', () => {
            const on = !!els.sy.value;
            els.sub.disabled = !on; els.sec.disabled = true; els.q.disabled = !on;
            if (on) loadSubjects();
            validate();
        });

        els.sub.addEventListener('change', () => {
            if (els.sub.value) { loadSections(); els.sec.disabled = false; }
            else els.sec.disabled = true;
            validate();
        });

        [els.sec, els.q].forEach(e => e.addEventListener('change', validate));

        function validate() {
            els.load.disabled = !(els.sy.value && els.sub.value && els.sec.value && els.q.value);
        }

        // LOADERS
        function loadSubjects() {
            fetch('api/get_subjects.php').then(r => r.json()).then(d => {
                els.sub.innerHTML = '<option value="">Select Subject</option>';
                d.forEach(i => els.sub.innerHTML += `<option value="${i.subject_id}">${i.subject_name}</option>`);
            });
        }

        function loadSections() {
            fetch(`api/get_sections.php?subject_id=${els.sub.value}&school_year_id=${els.sy.value}`)
                .then(r => r.json()).then(d => {
                    els.sec.innerHTML = '<option value="">Select Section</option>';
                    d.forEach(i => els.sec.innerHTML += `<option value="${i.section_id}">${i.grade_level} - ${i.section_name}</option>`);
                });
        }

        // MAIN LOAD
        els.load.addEventListener('click', () => {
            els.load.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            const url = `api/get_class_record.php?school_year_id=${els.sy.value}&section_id=${els.sec.value}&subject_id=${els.sub.value}&quarter=${els.q.value}`;

            fetch(url).then(r => r.json()).then(d => {
                els.load.innerHTML = 'Load';
                if (d.error) return showAlert(d.error, 'danger');

                gradingData = d.students || [];
                components = d.components;
                assignmentId = d.assignment_id;

                renderTable();
                document.getElementById('weightInfo').style.display = 'block';
                els.save.disabled = false;
                els.release.disabled = false;
                hasChanges = false;
            });
        });

        // TABLE RENDERER
        function renderTable() {
            const thead = document.getElementById('gradeThead');
            const tbody = document.getElementById('gradingTableBody');

            // Header 1
            let h1 = `<tr><th rowspan="2" class="sticky-col">Learner Name</th>`;
            h1 += `<th colspan="${components.ww.length + 2}" class="bg-ww">
                    Written Works (30%) 
                    <button class="add-col-btn" onclick="addComponent('ww')" title="Add Column">+</button>
                   </th>`;
            h1 += `<th colspan="${components.pt.length + 2}" class="bg-pt">
                    Performance Tasks (50%) 
                    <button class="add-col-btn" onclick="addComponent('pt')" title="Add Column">+</button>
                   </th>`;
            h1 += `<th colspan="2" class="bg-qa">QA (20%)</th>`;
            h1 += `<th rowspan="2" class="bg-final">FINAL</th></tr>`;

            // Header 2 (Columns with Delete)
            let h2 = `<tr>`;
            // WW Cols
            components.ww.forEach((c, i) => {
                h2 += `<th class="bg-ww">
                        <div class="col-header-content">
                            <span>${i + 1}</span>
                            <div class="del-col-btn" onclick="deleteComponent(${c.component_id})" title="Delete Column">×</div>
                        </div>
                       </th>`;
            });
            h2 += `<th class="bg-ww text-muted small">%</th><th class="bg-ww text-muted small">WS</th>`;

            // PT Cols
            components.pt.forEach((c, i) => {
                h2 += `<th class="bg-pt">
                        <div class="col-header-content">
                            <span>${i + 1}</span>
                            <div class="del-col-btn" onclick="deleteComponent(${c.component_id})" title="Delete Column">×</div>
                        </div>
                       </th>`;
            });
            h2 += `<th class="bg-pt text-muted small">%</th><th class="bg-pt text-muted small">WS</th>`;

            // QA Cols
            h2 += `<th class="bg-qa">Score</th><th class="bg-qa text-muted small">WS</th></tr>`;

            thead.innerHTML = h1 + h2;

            // Max Score Row
            let maxRow = `<tr class="max-score-row"><td class="sticky-col text-end pe-3 text-muted">MAX SCORE</td>`;
            components.ww.forEach(c => {
                maxRow += `<td><input type="number" class="max-score-input" value="${c.max_score}" 
                    data-component-id="${c.component_id}" data-type="ww" onchange="updateMaxScore(this)"></td>`;
            });
            maxRow += `<td colspan="2" class="bg-ww"></td>`;

            components.pt.forEach(c => {
                maxRow += `<td><input type="number" class="max-score-input" value="${c.max_score}" 
                    data-component-id="${c.component_id}" data-type="pt" onchange="updateMaxScore(this)"></td>`;
            });
            maxRow += `<td colspan="2" class="bg-pt"></td>`;

            if (components.qa) {
                maxRow += `<td><input type="number" class="max-score-input" value="${components.qa.max_score}" 
                    data-component-id="${components.qa.component_id}" data-type="qa" onchange="updateMaxScore(this)"></td>`;
                maxRow += `<td class="bg-qa"></td>`;
            } else {
                maxRow += `<td class="bg-qa text-muted small">Set in Activities</td><td class="bg-qa"></td>`;
            }
            maxRow += `<td class="bg-final">100.00</td></tr>`;
            tbody.innerHTML = maxRow;

            // Student Rows
            if (gradingData.length === 0) {
                tbody.innerHTML += `<tr><td colspan="20" class="text-center py-4">No students found.</td></tr>`;
            } else {
                gradingData.forEach((st, sIdx) => {
                    let tr = `<tr><td class="sticky-col text-start ps-3">${st.full_name}</td>`;

                    // WW
                    components.ww.forEach((c, i) => {
                        const s = st.ww.find(x => x.component_id === c.component_id);
                        const val = s && s.score !== null ? s.score : '';
                        tr += `<td><input type="number" class="score-input" value="${val}" 
                            data-student="${sIdx}" data-type="ww" data-index="${i}" data-component-id="${c.component_id}" 
                            onchange="handleScoreChange(this)"></td>`;
                    });
                    tr += `<td class="computed-cell bg-ww" id="ww-pct-${sIdx}">0</td><td class="computed-cell bg-ww" id="ww-ws-${sIdx}">0</td>`;

                    // PT
                    components.pt.forEach((c, i) => {
                        const s = st.pt.find(x => x.component_id === c.component_id);
                        const val = s && s.score !== null ? s.score : '';
                        tr += `<td><input type="number" class="score-input" value="${val}" 
                            data-student="${sIdx}" data-type="pt" data-index="${i}" data-component-id="${c.component_id}" 
                            onchange="handleScoreChange(this)"></td>`;
                    });
                    tr += `<td class="computed-cell bg-pt" id="pt-pct-${sIdx}">0</td><td class="computed-cell bg-pt" id="pt-ws-${sIdx}">0</td>`;

                    // QA
                    if (components.qa) {
                        const s = st.qa;
                        const val = s && s.score !== null ? s.score : '';
                        tr += `<td><input type="number" class="score-input" value="${val}" 
                            data-student="${sIdx}" data-type="qa" data-component-id="${components.qa.component_id}" 
                            onchange="handleScoreChange(this)"></td>`;
                        tr += `<td class="computed-cell bg-qa" id="qa-ws-${sIdx}">0</td>`;
                    } else {
                        tr += `<td class="bg-qa"></td><td class="bg-qa"></td>`;
                    }

                    tr += `<td class="computed-cell bg-final text-primary" id="final-${sIdx}">0</td></tr>`;
                    tbody.innerHTML += tr;
                    recomputeStudent(sIdx);
                });
            }
            if (window.lucide) lucide.createIcons();
        }

        // COMPUTATIONS
        window.handleScoreChange = function (input) {
            const sIdx = input.dataset.student;
            const type = input.dataset.type;
            const idx = input.dataset.index;
            const val = input.value === '' ? null : parseFloat(input.value);

            if (type === 'qa') {
                if (!gradingData[sIdx].qa) gradingData[sIdx].qa = { component_id: input.dataset.componentId };
                gradingData[sIdx].qa.score = val;
                gradingData[sIdx].qa.max_score = components.qa.max_score;
            } else {
                if (!gradingData[sIdx][type][idx]) gradingData[sIdx][type][idx] = { component_id: input.dataset.componentId };
                gradingData[sIdx][type][idx].score = val;
                gradingData[sIdx][type][idx].max_score = components[type][idx].max_score;
            }
            recomputeStudent(sIdx);
            hasChanges = true;
        };

        window.updateMaxScore = function (input) {
            const compId = input.dataset.componentId;
            const type = input.dataset.type;
            const val = parseFloat(input.value);

            if (type === 'qa') components.qa.max_score = val;
            else {
                const c = components[type].find(x => x.component_id == compId);
                if (c) c.max_score = val;
            }
            gradingData.forEach((_, i) => recomputeStudent(i));
            hasChanges = true;
        };

        function recomputeStudent(idx) {
            const st = gradingData[idx];

            // WW
            const wwScore = st.ww.reduce((a, b) => a + (parseFloat(b.score) || 0), 0);
            const wwMax = components.ww.reduce((a, b) => a + (parseFloat(b.max_score) || 0), 0);
            const wwPct = wwMax ? (wwScore / wwMax) * 100 : 0;
            const wwWs = wwPct * WEIGHTS.ww;

            document.getElementById(`ww-pct-${idx}`).innerText = wwPct.toFixed(0);
            document.getElementById(`ww-ws-${idx}`).innerText = wwWs.toFixed(2);

            // PT
            const ptScore = st.pt.reduce((a, b) => a + (parseFloat(b.score) || 0), 0);
            const ptMax = components.pt.reduce((a, b) => a + (parseFloat(b.max_score) || 0), 0);
            const ptPct = ptMax ? (ptScore / ptMax) * 100 : 0;
            const ptWs = ptPct * WEIGHTS.pt;

            document.getElementById(`pt-pct-${idx}`).innerText = ptPct.toFixed(0);
            document.getElementById(`pt-ws-${idx}`).innerText = ptWs.toFixed(2);

            // QA
            let qaWs = 0;
            if (components.qa && st.qa) {
                const qaScore = parseFloat(st.qa.score) || 0;
                const qaMax = parseFloat(components.qa.max_score) || 0;
                const qaPct = qaMax ? (qaScore / qaMax) * 100 : 0;
                qaWs = qaPct * WEIGHTS.qa;
                document.getElementById(`qa-ws-${idx}`).innerText = qaWs.toFixed(2);
            }

            // Final
            const final = wwWs + ptWs + qaWs;
            st.final_grade = final;
            document.getElementById(`final-${idx}`).innerText = final.toFixed(2);
        }

        // ACTIONS
        window.addComponent = function (type) {
            if (!assignmentId) return;
            const btn = document.querySelector(`.add-col-btn[onclick="addComponent('${type}')"]`);
            const old = btn.innerHTML; btn.innerHTML = '...';

            fetch('api/add_component.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ assignment_id: assignmentId, quarter: els.q.value, component_type: type })
            }).then(r => r.json()).then(d => {
                if (d.success) els.load.click();
                else showAlert(d.message, 'danger');
                btn.innerHTML = old;
            });
        };

        window.deleteComponent = function (id) {
            if (!confirm("Are you sure? This will delete the column and all grades in it.")) return;
            fetch('api/delete_component.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ component_id: id })
            }).then(r => r.json()).then(d => {
                if (d.success) els.load.click();
                else showAlert(d.message, 'danger');
            });
        };

        window.saveAllGrades = function () {
            if (!hasChanges) return showAlert('No changes to save', 'info');
            els.save.innerHTML = 'Saving...'; els.save.disabled = true;

            const payload = {
                assignment_id: assignmentId,
                quarter: els.q.value,
                components: components,
                students: gradingData.map(st => ({
                    subject_enrollment_id: st.subject_enrollment_id,
                    final_grade: st.final_grade,
                    grades: [
                        ...st.ww.map(x => ({ component_id: x.component_id, score: x.score })),
                        ...st.pt.map(x => ({ component_id: x.component_id, score: x.score })),
                        ...(st.qa ? [{ component_id: st.qa.component_id, score: st.qa.score }] : [])
                    ]
                }))
            };

            fetch('actions/save_grades.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(r => r.json()).then(d => {
                if (d.success) { showAlert('Saved!', 'success'); hasChanges = false; }
                else showAlert('Save failed', 'danger');
                els.save.innerHTML = '<i data-lucide="save"></i> Save Changes'; els.save.disabled = false;
                if (window.lucide) lucide.createIcons();
            });
        };

        window.confirmRelease = function() {
            const type = document.querySelector('input[name="releaseType"]:checked').value;
            const btn = document.querySelector('#releaseModal .btn-primary');
            const originalText = btn.innerHTML;
            
            // 1. Gather Data: ID + The Calculated Final Grade from the UI
            // gradingData contains the live calculated values from the table
            const releaseData = gradingData.map(st => ({
                id: st.subject_enrollment_id,
                grade: st.final_grade || 0 // Use the value currently shown in the "Final" column
            }));

            if (releaseData.length === 0) {
                alert("No students to release.");
                return;
            }

            btn.innerHTML = 'Releasing...';
            btn.disabled = true;

            fetch('actions/release_grades.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    students: releaseData, // Sending Array of {id, grade}
                    quarter: els.q.value,
                    release_type: type
                })
            }).then(r => r.json()).then(d => {
                if(d.success) {
                    showAlert('Grades Released & Saved Successfully!', 'success');
                    
                    // Force close modal
                    const modalEl = document.getElementById('releaseModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    
                    setTimeout(() => {
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = 'auto'; 
                    }, 300);

                } else {
                    showAlert(d.message || 'Error releasing grades', 'danger');
                }
            })
            .catch(err => showAlert('System Error', 'danger'))
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        };

        function showAlert(msg, type) {
            const d = document.createElement('div');
            d.className = `alert alert-${type} alert-dismissible fade show fixed-top m-3 shadow`;
            d.style.zIndex = 9999;
            d.innerHTML = `${msg} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(d);
            setTimeout(() => d.remove(), 3000);
        }
    })();
</script>