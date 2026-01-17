<style>
    /* Scoped styles for Results Section */
    #results_section .activity-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    #results_section .activity-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    #results_section .badge-ww {
        background-color: #d6eaf8;
        color: #0c5460;
    }

    #results_section .badge-pt {
        background-color: #f3d9fa;
        color: #563d7c;
    }

    #results_section .badge-qa {
        background-color: #fff3cd;
        color: #856404;
    }

    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }
</style>

<div id="results_section" class="container-fluid px-0">

    <div id="results_list_view">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Results & Grading</h1>
                <p class="text-muted small">View student submissions and scores</p>
            </div>
        </div>

        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">School Year</label>
                        <select id="res_schoolYear" class="form-select"></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Subject</label>
                        <select id="res_subject" class="form-select"></select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Quarter</label>
                        <select id="res_quarter" class="form-select">
                            <option value="">All</option>
                            <option value="1">Q1</option>
                            <option value="2">Q2</option>
                            <option value="3">Q3</option>
                            <option value="4">Q4</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Type</label>
                        <select id="res_type" class="form-select">
                            <option value="">All</option>
                            <option value="quiz">Quiz</option>
                            <option value="file">File</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Status</label>
                        <select id="res_status" class="form-select">
                            <option value="">All</option>
                            <option value="1">Published</option>
                            <option value="0">Draft</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="res_activitiesContainer" class="row g-3"></div>
    </div>

    <div id="results_detail_view" class="d-none">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <button class="btn btn-secondary btn-sm mb-2" id="res_backBtn">
                    <i data-lucide="arrow-left" style="width:14px"></i> Back to Activities
                </button>
                <h3 class="h4 fw-bold mb-0" id="res_detailTitle">Activity Title</h3>
                <p class="text-muted small mb-0" id="res_detailMeta">Math - 10 Burgundy</p>
            </div>
            <div class="text-end">
                <h2 class="h3 fw-bold text-primary mb-0" id="res_detailMax">50</h2>
                <small class="text-muted">Max Score</small>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Student</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                            <th>Score</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody id="res_studentTable"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="res_breakdownModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Submission Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div id="res_modalContent" class="d-flex flex-column gap-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        const els = {
            schoolYear: document.getElementById('res_schoolYear'),
            subject: document.getElementById('res_subject'),
            quarter: document.getElementById('res_quarter'),
            type: document.getElementById('res_type'),
            status: document.getElementById('res_status'),
            container: document.getElementById('res_activitiesContainer'),
            listView: document.getElementById('results_list_view'),
            detailView: document.getElementById('results_detail_view'),
            backBtn: document.getElementById('res_backBtn'),
            detailTitle: document.getElementById('res_detailTitle'),
            detailMeta: document.getElementById('res_detailMeta'),
            detailMax: document.getElementById('res_detailMax'),
            studentTable: document.getElementById('res_studentTable'),
            modalContent: document.getElementById('res_modalContent'),
            // REMOVED: breakdownModal initialization here (it causes the bug)
        };

        function init() {
            if (!els.container) return;
            loadFilters();
            [els.schoolYear, els.subject, els.quarter, els.type, els.status].forEach(el => {
                el.addEventListener('change', loadActivities);
            });
            els.backBtn.addEventListener('click', () => {
                els.detailView.classList.add('d-none');
                els.listView.classList.remove('d-none');
                els.listView.classList.add('fade-in');
            });
        }

        // --- LOADERS ---
        function loadFilters() {
            fetch('api/get_school_years.php').then(r => r.json()).then(data => {
                els.schoolYear.innerHTML = '<option value="">All School Years</option>';
                data.forEach(sy => els.schoolYear.innerHTML += `<option value="${sy.id}">${sy.school_year}</option>`);
                loadActivities();
            });
            fetch('api/get_subjects.php').then(r => r.json()).then(data => {
                els.subject.innerHTML = '<option value="">All Subjects</option>';
                data.forEach(s => els.subject.innerHTML += `<option value="${s.subject_id}">${s.subject_name}</option>`);
            });
        }

        function loadActivities() {
            const params = new URLSearchParams({
                school_year_id: els.schoolYear.value,
                subject_id: els.subject.value,
                quarter: els.quarter.value,
                activity_type: els.type.value,
                is_published: els.status.value
            });

            els.container.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';

            fetch(`api/get_activities.php?${params}`).then(r => r.json()).then(data => {
                if (data.length === 0) {
                    els.container.innerHTML = '<div class="col-12 text-center text-muted py-5">No activities found. Select filters above.</div>';
                    return;
                }

                els.container.innerHTML = data.map(act => `
                    <div class="col-md-6 col-lg-4">
                        <div class="card activity-card h-100 p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge badge-${act.component_type}">${act.component_type.toUpperCase()}${act.item_number}</span>
                                <span class="badge ${act.is_published == 1 ? 'bg-success' : 'bg-secondary'}">${act.is_published == 1 ? 'Published' : 'Draft'}</span>
                            </div>
                            <h5 class="fw-bold mb-1">${act.title}</h5>
                            <p class="text-muted small mb-2">${act.subject_name} • ${act.grade_level}-${act.section_name}</p>
                            
                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                <div class="small fw-bold text-primary">${parseFloat(act.max_score)} pts</div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-secondary edit-activity-btn"
                                        data-act='${JSON.stringify(act).replace(/'/g, "&apos;")}'>
                                        <i data-lucide="edit-2" style="width:14px"></i> Edit Max Score
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary view-results-btn" 
                                        data-id="${act.activity_id}"
                                        data-title="${act.title}"
                                        data-meta="${act.subject_name} • ${act.grade_level}-${act.section_name}">
                                        View Results
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');

                // Listeners
                document.querySelectorAll('.view-results-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        openDetailView(btn.dataset.id, btn.dataset.title, btn.dataset.meta);
                    });
                });

                document.querySelectorAll('.edit-activity-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        editActivityBridge(JSON.parse(btn.dataset.act));
                    });
                });

                if (window.lucide) lucide.createIcons();
            });
        }

        // --- LOGIC 2: EDIT ACTIVITY (Bridge to Create Tab) ---
        window.editActivityBridge = function (data) {
            const createTab = document.querySelector('.nav-link[data-section="activities"]');
            if (createTab) createTab.click();

            setTimeout(() => {
                if (window.loadForEdit) window.loadForEdit(data);
                else console.error("loadForEdit function not found");
            }, 100);
        };

        // --- LOGIC 3: VIEW STUDENT LIST ---
        function openDetailView(activityId, title, meta) {
            els.detailTitle.textContent = title;
            els.detailMeta.textContent = meta;
            els.studentTable.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div> Loading students...</td></tr>';
            els.listView.classList.add('d-none');
            els.detailView.classList.remove('d-none');
            els.detailView.classList.add('fade-in');

            fetch(`api/get_activity_results.php?activity_id=${activityId}`).then(r => r.json()).then(data => {
                els.detailMax.textContent = parseFloat(data.max_score);
                if (data.students.length === 0) {
                    els.studentTable.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No students found.</td></tr>';
                    return;
                }
                els.studentTable.innerHTML = data.students.map(s => {
                    const statusBadge = getStatusBadge(s.status, s.submission_id);
                    const scoreDisplay = s.score !== null ? `<span class="fw-bold">${s.score}</span> / ${data.max_score}` : '--';

                    const actionBtn = s.submission_id
                        ? `<button class="btn btn-sm btn-light border view-breakdown-btn" data-sub="${s.submission_id}">View</button>`
                        : '--';

                    return `<tr>
                        <td class="ps-4 fw-bold">${s.last_name}, ${s.first_name}</td>
                        <td>${statusBadge}</td>
                        <td>${s.submitted_at ? new Date(s.submitted_at).toLocaleDateString() : '--'}</td>
                        <td>${scoreDisplay}</td>
                        <td class="text-end pe-4">${actionBtn}</td>
                    </tr>`;
                }).join('');

                document.querySelectorAll('.view-breakdown-btn').forEach(btn =>
                    btn.addEventListener('click', () => openBreakdown(btn.dataset.sub))
                );
            });
        }

        function getStatusBadge(status, subId) {
            if (!subId) return '<span class="badge bg-secondary">Missing</span>';
            if (status === 'graded') return '<span class="badge bg-success">Graded</span>';
            return '<span class="badge bg-warning text-dark">Submitted</span>';
        }

        // --- CRITICAL FIX: MODAL INITIALIZATION ON DEMAND ---
        function openBreakdown(submissionId) {
            els.modalContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border"></div></div>';

            // 1. Get the Modal Element
            const modalEl = document.getElementById('res_breakdownModal');

            // 2. Safe Initialization (Check if bootstrap exists, check if instance exists)
            let modalInstance;
            if (window.bootstrap) {
                modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalEl);
                }
                modalInstance.show();
            } else {
                console.error("Bootstrap is not loaded yet.");
                return;
            }

            fetch(`api/get_submission_details.php?submission_id=${submissionId}`).then(r => r.json()).then(data => {

                // === IF FILE SUBMISSION: SHOW FILE + GRADING INPUT ===
                if (data.type === 'file') {
                    const path = '../uploads/student_submissions/' + data.file_path;
                    const ext = data.file_path.split('.').pop().toLowerCase();
                    const isImg = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);

                    const fileDisplay = isImg
                        ? `<img src="${path}" class="img-fluid border mb-3 rounded shadow-sm" style="max-height: 300px;">`
                        : `<div class="p-4 bg-light border rounded mb-3 text-center"><i data-lucide="file-text" style="width:48px; height:48px;"></i><br>${data.file_path}</div>`;

                    // Render Grading Interface
                    els.modalContent.innerHTML = `
                        <div class="row">
                            <div class="col-md-8 border-end text-center">
                                <h6 class="text-muted small fw-bold mb-3">Student File</h6>
                                ${fileDisplay}
                                <a href="${path}" download class="btn btn-outline-dark btn-sm mt-2"><i data-lucide="download" style="width:14px"></i> Download File</a>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-primary fw-bold mb-3">Grade Submission</h6>
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <label class="form-label small fw-bold">Score (Max: ${data.max_score})</label>
                                        <input type="number" id="manualGradeInput" class="form-control mb-3 text-center fw-bold" value="${data.score || ''}" min="0" max="${data.max_score}">
                                        <button class="btn btn-primary w-100" onclick="saveManualGrade(${submissionId}, ${data.max_score})">
                                            <i data-lucide="save" style="width:16px"></i> Save Grade
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                }
                // === IF QUIZ SUBMISSION: SHOW QUESTION BREAKDOWN (NO GRADING) ===
                else {
                    els.modalContent.innerHTML = data.map((q, i) => `
                        <div class="card mb-2 ${q.is_correct == 1 ? 'border-success' : 'border-danger'} border-start border-4">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold small">Question ${i + 1}</span>
                                    <span class="badge ${q.is_correct == 1 ? 'bg-success' : 'bg-danger'}">${q.points_earned} pts</span>
                                </div>
                                <p class="mb-1 mt-1 small">${q.question_text}</p>
                                <div class="bg-light p-2 rounded small">
                                    <strong>Answer:</strong> ${q.answer_text || q.selected_option_text || '(No Answer)'}
                                </div>
                            </div>
                        </div>`).join('');
                }
                if (window.lucide) lucide.createIcons();
            });
        }

        // --- MANUAL GRADE SAVE ---
        window.saveManualGrade = function (subId, max) {
            const input = document.getElementById('manualGradeInput');
            const score = parseFloat(input.value);

            if (isNaN(score) || score < 0 || score > max) {
                return alert(`Invalid score. Must be between 0 and ${max}.`);
            }

            const btn = document.querySelector('button[onclick^="saveManualGrade"]');
            const oldText = btn.innerHTML;
            btn.innerHTML = 'Saving...';
            btn.disabled = true;

            fetch('actions/update_grade.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ submission_id: subId, score: score })
            })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        alert('Grade Saved Successfully!');
                        // Use the modal instance created in openBreakdown logic
                        const modalEl = document.getElementById('res_breakdownModal');
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) modalInstance.hide();
                    } else {
                        alert(d.message);
                    }
                })
                .catch(e => alert('System Error'))
                .finally(() => {
                    btn.innerHTML = oldText;
                    btn.disabled = false;
                });
        };

        init();
    })();
</script>