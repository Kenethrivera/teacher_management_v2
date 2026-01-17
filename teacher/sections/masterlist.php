<div class="container-fluid px-0">
    <div id="alertBox"></div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 fw-bold mb-0">Student Masterlist</h1>
            <small class="text-muted">Manage your Classes & Students</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" onclick="openAddClassModal()">
                <i data-lucide="plus-circle" style="width:16px"></i> Add Class
            </button>

            <button class="btn btn-primary" onclick="openAddStudentModal()">
                <i data-lucide="user-plus" style="width:16px"></i> Add Student
            </button>

            <button class="btn btn-dark" onclick="printMasterlist()" id="printBtn" disabled>
                <i data-lucide="printer" style="width:16px"></i> Print
            </button>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body bg-light rounded">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">School Year</label>
                    <select id="ml_schoolYear" class="form-select border-0 shadow-sm"></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Subject</label>
                    <select id="ml_subject" class="form-select border-0 shadow-sm" disabled></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Section</label>
                    <select id="ml_section" class="form-select border-0 shadow-sm" disabled></select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">LRN</th>
                        <th>Student Name</th>
                        <th>Sex</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody id="masterlistBody">
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Select filters to load students...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Add Class / Subject</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addClassForm">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">School Year</label>
                        <select id="new_class_sy" class="form-select" required></select>
                    </div>

                    <div class="bg-light p-3 rounded border mb-3">
                        <label class="form-label small fw-bold text-primary">Subject Details</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="text" id="new_class_code" class="form-control form-control-sm"
                                    placeholder="Code (e.g. PROG1)">
                            </div>
                            <div class="col-8">
                                <input type="text" id="new_class_subject" class="form-control form-control-sm"
                                    placeholder="Name (e.g. Programming)" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Assign to Sections</label>
                        <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;"
                            id="sectionCheckboxList">
                            <div class="text-muted small text-center py-2">Loading sections...</div>
                        </div>
                    </div>

                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-white">Or New Section:</span>
                        <input type="number" id="quick_grade" class="form-control" placeholder="Gr"
                            style="max-width: 60px;">
                        <input type="text" id="quick_section" class="form-control" placeholder="Section Name">
                    </div>

                    <button type="submit" class="btn btn-success w-100">Save Class</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="studentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="stModalTitle">Add Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="studentForm">
                    <input type="hidden" id="st_id"><input type="hidden" id="st_action">

                    <div class="alert alert-light border small mb-3">
                        <div class="fw-bold mb-2 text-primary">Enrollment Details</div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small text-muted">School Year</label>
                                <select id="st_enroll_sy" class="form-select form-select-sm" required></select>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted">Quarter (Optional)</label>
                                <select id="st_quarter" class="form-select form-select-sm">
                                    <option value="">None</option>
                                    <option value="1">1st</option>
                                    <option value="2">2nd</option>
                                    <option value="3">3rd</option>
                                    <option value="4">4th</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted">Subject</label>
                                <select id="st_enroll_sub" class="form-select form-select-sm" required>
                                    <option value="">Select Subject</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted">Section</label>
                                <select id="st_enroll_sec" class="form-select form-select-sm" required>
                                    <option value="">Select Section</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6"><label class="small fw-bold">LRN</label><input type="text" id="st_lrn"
                                class="form-control" required></div>
                        <div class="col-6"><label class="small fw-bold">Status</label><select id="st_status"
                                class="form-select">
                                <option value="Enrolled">Enrolled</option>
                                <option value="Dropped">Dropped</option>
                            </select></div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6"><label class="small fw-bold">First Name</label><input type="text"
                                id="st_fname" class="form-control" required></div>
                        <div class="col-6"><label class="small fw-bold">Last Name</label><input type="text"
                                id="st_lname" class="form-control" required></div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6"><label class="small fw-bold">Sex</label><select id="st_sex"
                                class="form-select">
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select></div>
                        <div class="col-6"><label class="small fw-bold">Age</label><input type="number" id="st_age"
                                class="form-control" required></div>
                    </div>

                    <div id="credsSection" class="bg-light p-3 rounded border mb-3">
                        <h6 class="small fw-bold text-primary mb-2">Login Credentials</h6>
                        <div class="mb-2"><input type="email" id="st_email" class="form-control form-control-sm"
                                placeholder="Email" required></div>
                        <div><input type="password" id="st_pass" class="form-control form-control-sm"
                                placeholder="Password" required></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Save Student</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';
        const els = {
            sy: document.getElementById('ml_schoolYear'), sub: document.getElementById('ml_subject'), sec: document.getElementById('ml_section'),
            addBtn: document.getElementById('addStudentBtn'), printBtn: document.getElementById('printBtn'), tbody: document.getElementById('masterlistBody'),
            // Modal Elements
            newClassSy: document.getElementById('new_class_sy'),
            sectionCheckboxList: document.getElementById('sectionCheckboxList'),
            stEnrollSy: document.getElementById('st_enroll_sy'),
            stEnrollSub: document.getElementById('st_enroll_sub'),
            stEnrollSec: document.getElementById('st_enroll_sec')
        };

        if (window.lucide) lucide.createIcons();
        initData();

        // Filters
        els.sy.addEventListener('change', () => { if (els.sy.value) { els.sub.disabled = false; loadSubjects(); } else { els.sub.disabled = true; els.sec.disabled = true; } checkLoad(); });
        els.sub.addEventListener('change', () => { if (els.sub.value) { els.sec.disabled = false; loadSections(); } else { els.sec.disabled = true; } checkLoad(); });
        els.sec.addEventListener('change', () => { checkLoad(); if (els.sec.value) loadMasterlist(); });
        function checkLoad() { els.printBtn.disabled = !(els.sy.value && els.sub.value && els.sec.value); }

        // Data Loaders
        function initData() {
            fetch('api/get_school_years.php').then(r => r.json()).then(d => {
                const opts = '<option value="">Select School Year</option>' + d.map(i => `<option value="${i.id}">${i.school_year}</option>`).join('');
                els.sy.innerHTML = opts;
                els.newClassSy.innerHTML = opts;
                els.stEnrollSy.innerHTML = opts;
            });
            loadAllSectionsForCheckboxes();
        }
        function loadSubjects() { fetch('api/get_subjects.php').then(r => r.json()).then(d => els.sub.innerHTML = '<option value="">Select Subject</option>' + d.map(i => `<option value="${i.subject_id}">${i.subject_name}</option>`).join('')); }
        function loadSections() { fetch(`api/get_sections.php?subject_id=${els.sub.value}&school_year_id=${els.sy.value}`).then(r => r.json()).then(d => els.sec.innerHTML = '<option value="">Select Section</option>' + d.map(i => `<option value="${i.section_id}">${i.grade_level} - ${i.section_name}</option>`).join('')); }

        // --- ADD CLASS ---
        function loadAllSectionsForCheckboxes() {
            fetch('api/get_all_sections.php').then(r => r.json()).then(d => {
                if (d.length === 0) { els.sectionCheckboxList.innerHTML = '<div class="text-muted small">No sections found.</div>'; return; }
                els.sectionCheckboxList.innerHTML = d.map(s => `<div class="form-check"><input class="form-check-input sec-checkbox" type="checkbox" value="${s.section_id}" id="chk_${s.section_id}"><label class="form-check-label small" for="chk_${s.section_id}">Grade ${s.grade_level} - ${s.section_name}</label></div>`).join('');
            });
        }
        window.openAddClassModal = function () {
            if (els.sy.value) els.newClassSy.value = els.sy.value;
            loadAllSectionsForCheckboxes();
            new bootstrap.Modal(document.getElementById('addClassModal')).show();
        };
        document.getElementById('addClassForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const checkedIds = Array.from(document.querySelectorAll('.sec-checkbox:checked')).map(cb => cb.value);
            const payload = {
                school_year_id: document.getElementById('new_class_sy').value,
                subject_code: document.getElementById('new_class_code').value,
                subject_name: document.getElementById('new_class_subject').value,
                existing_section_ids: checkedIds,
                new_section_grade: document.getElementById('quick_grade').value,
                new_section_name: document.getElementById('quick_section').value
            };
            fetch('api/setup_class.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
                .then(r => r.json()).then(d => { if (d.success) { alert(d.message); bootstrap.Modal.getInstance(document.getElementById('addClassModal')).hide(); location.reload(); } else { alert(d.message); } });
        });

        // --- ADD STUDENT ---
        window.openAddStudentModal = function () {
            document.getElementById('studentForm').reset();
            document.getElementById('st_action').value = 'add';
            document.getElementById('stModalTitle').innerText = 'Add New Student';
            document.getElementById('credsSection').style.display = 'block';

            if (els.sy.value) els.stEnrollSy.value = els.sy.value;
            fetch('api/get_subjects.php').then(r => r.json()).then(d => {
                els.stEnrollSub.innerHTML = '<option value="">Subject</option>' + d.map(i => `<option value="${i.subject_id}">${i.subject_name}</option>`).join('');
                if (els.sub.value) els.stEnrollSub.value = els.sub.value;
                if (els.stEnrollSub.value) loadModalSections();
            });
            els.stEnrollSub.addEventListener('change', loadModalSections);
            els.stEnrollSy.addEventListener('change', loadModalSections);
            new bootstrap.Modal(document.getElementById('studentModal')).show();
        };
        function loadModalSections() {
            const sub = els.stEnrollSub.value; const sy = els.stEnrollSy.value; if (!sub || !sy) return;
            fetch(`api/get_sections.php?subject_id=${sub}&school_year_id=${sy}`).then(r => r.json()).then(d => {
                els.stEnrollSec.innerHTML = '<option value="">Section</option>' + d.map(i => `<option value="${i.section_id}">${i.grade_level} - ${i.section_name}</option>`).join('');
                if (els.sec.value) els.stEnrollSec.value = els.sec.value;
            });
        }
        document.getElementById('studentForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const payload = {
                action: document.getElementById('st_action').value,
                student_id: document.getElementById('st_id').value,
                lrn: document.getElementById('st_lrn').value,
                first_name: document.getElementById('st_fname').value,
                last_name: document.getElementById('st_lname').value,
                sex: document.getElementById('st_sex').value,
                age: document.getElementById('st_age').value,
                status: document.getElementById('st_status').value,
                school_year_id: document.getElementById('st_enroll_sy').value,
                subject_id: document.getElementById('st_enroll_sub').value,
                section_id: document.getElementById('st_enroll_sec').value,
                quarter: document.getElementById('st_quarter').value,
                email: document.getElementById('st_email').value,
                password: document.getElementById('st_pass').value
            };
            fetch('api/manage_student.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
                .then(r => r.json()).then(d => { if (d.success) { bootstrap.Modal.getInstance(document.getElementById('studentModal')).hide(); loadMasterlist(); alert(d.message); } else { alert(d.message); } });
        });

        // --- LIST ---
        function loadMasterlist() {
            els.tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>';
            fetch(`api/get_masterlist.php?school_year_id=${els.sy.value}&subject_id=${els.sub.value}&section_id=${els.sec.value}`)
                .then(r => r.json()).then(data => {
                    if (data.length === 0) { els.tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No students found.</td></tr>'; return; }
                    let html = '', sex = '';
                    data.forEach(s => {
                        if (s.sex !== sex) { sex = s.sex; html += `<tr class="table-light"><td colspan="5" class="fw-bold ps-4 small text-primary">${sex === 'M' ? 'MALE' : 'FEMALE'}</td></tr>`; }
                        const sData = encodeURIComponent(JSON.stringify(s));
                        html += `<tr><td class="ps-4 font-monospace small">${s.lrn}</td><td class="fw-bold">${s.last_name}, ${s.first_name}</td><td>${s.sex}</td><td><span class="badge ${s.status === 'Enrolled' ? 'bg-success' : 'bg-danger'} bg-opacity-75">${s.status}</span></td><td class="text-end pe-4"><button class="btn btn-sm btn-outline-secondary" onclick="openEditStudent('${sData}')"><i data-lucide="edit-2" style="width:14px"></i></button></td></tr>`;
                    });
                    els.tbody.innerHTML = html; if (window.lucide) lucide.createIcons();
                });
        }
        window.openEditStudent = function (jsonStr) {
            const s = JSON.parse(decodeURIComponent(jsonStr));
            document.getElementById('st_id').value = s.student_id;
            document.getElementById('st_action').value = 'edit';
            document.getElementById('st_lrn').value = s.lrn;
            document.getElementById('st_fname').value = s.first_name;
            document.getElementById('st_lname').value = s.last_name;
            document.getElementById('st_sex').value = s.sex;
            document.getElementById('st_age').value = s.age;
            document.getElementById('credsSection').style.display = 'none';
            document.getElementById('st_enroll_sy').closest('.alert').style.display = 'none';
            document.getElementById('stModalTitle').innerText = 'Edit Student Info';
            new bootstrap.Modal(document.getElementById('studentModal')).show();
        };
        window.printMasterlist = function () { /* Print Logic */ };
    })();
</script>