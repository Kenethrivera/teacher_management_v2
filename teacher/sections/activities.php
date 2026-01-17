<style>
    .card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .question-card {
        position: relative;
        padding-right: 3rem !important;
        border-left: 4px solid #3b82f6;
    }

    .remove-question {
        position: absolute;
        top: 1.25rem;
        right: 1.25rem;
        color: #9ca3af;
        border: none;
        background: none;
        cursor: pointer;
    }

    .remove-question:hover {
        color: #ef4444;
    }

    .alert-fixed {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
    }

    .modal {
        z-index: 1055 !important;
    }

    .modal-backdrop {
        z-index: 1050 !important;
    }
</style>

<div class="container" style="max-width: 900px;">
    <div id="alertContainer"></div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Create Activity</h1>
            <p class="text-muted small">Design quizzes or assignments</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-secondary d-flex align-items-center gap-2"
                onclick="document.querySelector('.nav-link[data-section=\'results\']').click()">
                <i data-lucide="arrow-left" style="width:16px"></i> Back
            </button>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" id="saveBtn">
                <i data-lucide="save" style="width:16px"></i> Save Activity
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white fw-bold py-3">Activity Details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label small fw-bold">Title *</label>
                    <input type="text" id="actTitle" class="form-control" placeholder="e.g. Chapter 1 Quiz" required>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">Description</label>
                    <textarea id="actDesc" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">School Year *</label>
                    <select id="actSchoolYear" class="form-select" required></select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Subject *</label>
                    <select id="actSubject" class="form-select" disabled required></select>
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Assign to Section(s) *</label>
                    <div id="actSectionContainer" class="border rounded p-2 bg-light text-muted small text-center"
                        style="max-height: 150px; overflow-y: auto;">
                        Select a subject first...
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Quarter *</label>
                    <select id="actQuarter" class="form-select" required>
                        <option value="">Select Quarter</option>
                        <option value="1">1st Quarter</option>
                        <option value="2">2nd Quarter</option>
                        <option value="3">3rd Quarter</option>
                        <option value="4">4th Quarter</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Type</label>
                    <select id="actType" class="form-select">
                        <option value="file">File Submission</option>
                        <option value="quiz">Quiz (Auto-graded)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Category</label>
                    <select id="actCategory" class="form-select">
                        <option value="ww">Written Work</option>
                        <option value="pt">Performance Task</option>
                        <option value="qa">Quarterly Assessment</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Item #</label>
                    <select id="actNumber" class="form-select"></select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Max Score</label>
                    <input type="number" id="actMaxScore" class="form-control" value="100">
                    <small class="text-muted d-none" id="autoScoreNote">Auto-calculated from questions</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Due Date</label>
                    <input type="datetime-local" id="actDueDate" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div id="quizBuilder" class="d-none">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 fw-bold mb-0">Quiz Questions</h2>
            <button class="btn btn-dark btn-sm d-flex align-items-center gap-2" id="addQuestionBtn">
                <i data-lucide="plus" style="width:16px"></i> Add Question
            </button>
        </div>
        <div id="questionsContainer"></div>
        <div id="emptyState" class="text-center py-5 bg-white rounded border border-dashed text-muted">
            No questions added yet. Click "Add Question" to start.
        </div>
    </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="mb-3">
                    <i data-lucide="check-circle" style="width:64px;height:64px;color:#10b981"></i>
                </div>
                <h5 class="fw-bold mb-2">Activity Created!</h5>
                <p class="text-muted mb-4">Your activity has been saved successfully.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                        onclick="resetForm()">
                        Create Another
                    </button>

                    <button type="button" class="btn btn-primary" onclick="goToResults()">
                        View Activities
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    (function () {
        'use strict';
        
        const activity = { questions: [] };
        let elements = {};
        
        function init() {
            elements = {
                schoolYear: document.getElementById('actSchoolYear'),
                subject: document.getElementById('actSubject'),
                sectionContainer: document.getElementById('actSectionContainer'),
                quarter: document.getElementById('actQuarter'),
                actType: document.getElementById('actType'),
                category: document.getElementById('actCategory'),
                itemNumber: document.getElementById('actNumber'),
                maxScore: document.getElementById('actMaxScore'),
                title: document.getElementById('actTitle'),
                description: document.getElementById('actDesc'),
                dueDate: document.getElementById('actDueDate'),
                quizBuilder: document.getElementById('quizBuilder'),
                addQuestionBtn: document.getElementById('addQuestionBtn'),
                questionsContainer: document.getElementById('questionsContainer'),
                emptyState: document.getElementById('emptyState'),
                autoScoreNote: document.getElementById('autoScoreNote')
            };

            // Listeners
            if(elements.schoolYear) elements.schoolYear.addEventListener('change', handleSchoolYearChange);
            if(elements.subject) elements.subject.addEventListener('change', handleSubjectChange);
            if(elements.actType) elements.actType.addEventListener('change', toggleQuizBuilder);
            if(elements.category) elements.category.addEventListener('change', updateItemNumbers);
            if(elements.addQuestionBtn) elements.addQuestionBtn.addEventListener('click', addQuestion);

            // Save Delegate
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('#saveBtn');
                if (btn) handleSave(e, btn);
            });

            loadSchoolYears();
            updateItemNumbers();
            if(window.lucide) lucide.createIcons();
        }

        // --- NEW: Go To Results (Fixes Grey Screen) ---
        window.goToResults = function() {
            // 1. Force Close Modal
            const modalEl = document.getElementById('successModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            // 2. Kill Backdrop Manually
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';

            // 3. Reset Button & Navigate
            resetForm(); 
            document.querySelector('.nav-link[data-section="results"]').click();
        };

        // --- NEW: Reset Form (Fixes Stuck Button) ---
        window.resetForm = function() {
            // 1. FIX: Wake up the Save Button
            const btn = document.getElementById('saveBtn');
            if(btn) {
                btn.innerHTML = '<i data-lucide="save" style="width:16px"></i> Save Activity';
                btn.disabled = false;
                if(window.lucide) lucide.createIcons();
            }

            // 2. Clear Inputs
            elements.title.value = '';
            elements.description.value = '';
            elements.dueDate.value = '';
            // Reset Dropdowns (Optional, depends on preference)
            // elements.actType.value = 'file'; 
            
            // 3. Clear Checkboxes
            elements.sectionContainer.innerHTML = 'Select a subject first...';
            elements.subject.innerHTML = '';
            elements.subject.disabled = true;
            elements.schoolYear.value = '';

            // 4. Reset Quiz Data
            activity.questions = [];
            renderQuestions();
            toggleQuizBuilder();
        };

        // --- VALIDATION & SAVE ---
        function handleSave(e, btn) {
            e.preventDefault();
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Saving...';
            btn.disabled = true;

            const checkedSections = Array.from(document.querySelectorAll('.section-checkbox:checked')).map(cb => cb.value);

            if(!elements.title.value || checkedSections.length === 0) {
                showAlert('Please fill in Title and select at least one Section.', 'warning');
                revertBtn(btn, originalText);
                return;
            }

            if (elements.actType.value === 'quiz' && activity.questions.length === 0) {
                showAlert('Please add at least one question.', 'warning');
                revertBtn(btn, originalText);
                return;
            }

            const payload = {
                school_year_id: elements.schoolYear.value,
                subject_id: elements.subject.value,
                section_ids: checkedSections,
                quarter: elements.quarter.value,
                component_type: elements.category.value,
                item_number: elements.itemNumber.value,
                title: elements.title.value,
                description: elements.description.value,
                activity_type: elements.actType.value,
                max_score: elements.maxScore.value,
                due_date: elements.dueDate.value,
                questions: activity.questions
            };

            fetch('actions/create_activity.php', {
                method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    new bootstrap.Modal(document.getElementById('successModal')).show();
                    // Button stays disabled until user clicks a modal option
                } else {
                    showAlert(data.message, 'danger');
                    revertBtn(btn, originalText);
                }
            })
            .catch(err => {
                showAlert('System Error: ' + err.message, 'danger');
                revertBtn(btn, originalText);
            });
        }

        // --- LOADERS ---
        function handleSchoolYearChange(e) {
            fetch(`api/get_subjects.php?school_year_id=${e.target.value}`).then(r=>r.json()).then(data => {
                elements.subject.innerHTML = '<option value="">Select Subject</option>';
                elements.subject.disabled = false;
                data.forEach(s => elements.subject.innerHTML += `<option value="${s.subject_id}">${s.subject_name}</option>`);
            });
        }

        function handleSubjectChange(e) {
            const syId = elements.schoolYear.value;
            const container = elements.sectionContainer;
            container.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Loading...';

            fetch(`api/get_sections.php?subject_id=${e.target.value}&school_year_id=${syId}`)
            .then(r=>r.json()).then(data => {
                container.innerHTML = '';
                if(data.length === 0) { container.innerHTML = '<span class="text-danger small">No sections found.</span>'; return; }
                
                // Select All Checkbox
                const divAll = document.createElement('div');
                divAll.className = 'form-check border-bottom mb-2 pb-1';
                divAll.innerHTML = `<input class="form-check-input" type="checkbox" id="selectAllSec" onchange="toggleAllSections(this)"><label class="form-check-label fw-bold small" for="selectAllSec">Select All</label>`;
                container.appendChild(divAll);

                data.forEach(sec => {
                    const div = document.createElement('div');
                    div.className = 'form-check text-start';
                    div.innerHTML = `<input class="form-check-input section-checkbox" type="checkbox" value="${sec.section_id}" id="sec_${sec.section_id}"><label class="form-check-label" for="sec_${sec.section_id}">${sec.grade_level} - ${sec.section_name}</label>`;
                    container.appendChild(div);
                });
            });
        }

        window.toggleAllSections = function(source) {
            document.querySelectorAll('.section-checkbox').forEach(cb => cb.checked = source.checked);
        }

        // --- QUIZ HELPERS ---
        function toggleQuizBuilder() {
            const isQuiz = elements.actType.value === 'quiz';
            elements.quizBuilder.classList.toggle('d-none', !isQuiz);
            elements.maxScore.disabled = isQuiz;
            elements.autoScoreNote.classList.toggle('d-none', !isQuiz);
            if (isQuiz) recalculateScore(); else elements.maxScore.value = 100;
        }

        function recalculateScore() {
            if(elements.actType.value !== 'quiz') return;
            const total = activity.questions.reduce((sum, q) => sum + (parseFloat(q.points) || 0), 0);
            elements.maxScore.value = total;
        }

        window.addQuestion = function() {
            activity.questions.push({ id: Date.now(), text: '', type: 'multiple_choice', points: 5, options: ['', '', '', ''], correctAnswer: '' });
            renderQuestions(); recalculateScore();
        };

        window.removeQuestion = function(index) {
            activity.questions.splice(index, 1); renderQuestions(); recalculateScore();
        };

        window.updateQuestion = function(index, field, value) {
            activity.questions[index][field] = value; if (field === 'points') recalculateScore();
        };

        window.updateOption = function(qIndex, optIndex, value) {
            activity.questions[qIndex].options[optIndex] = value;
        };

        window.changeQuestionType = function(index, type) {
            activity.questions[index].type = type;
            if(type === 'multiple_choice') { activity.questions[index].options = ['', '', '', '']; activity.questions[index].correctAnswer = ''; }
            else if (type === 'true_false') { activity.questions[index].correctAnswer = 'true'; }
            else { activity.questions[index].correctAnswer = ''; }
            renderQuestions();
        };

        function renderQuestions() {
            if (activity.questions.length === 0) { elements.questionsContainer.innerHTML = ''; elements.emptyState.classList.remove('d-none'); return; }
            elements.emptyState.classList.add('d-none');
            
            elements.questionsContainer.innerHTML = activity.questions.map((q, i) => `
                <div class="card mb-3 question-card">
                    <div class="card-body">
                        <button class="remove-question" onclick="removeQuestion(${i})"><i data-lucide="trash-2" style="width:18px"></i></button>
                        <div class="row g-3">
                            <div class="col-md-8"><label class="form-label small fw-bold">Question ${i + 1}</label><input type="text" class="form-control" placeholder="Enter question text" value="${q.text}" oninput="updateQuestion(${i}, 'text', this.value)"></div>
                            <div class="col-md-2"><label class="form-label small fw-bold">Type</label><select class="form-select" onchange="changeQuestionType(${i}, this.value)"><option value="multiple_choice" ${q.type==='multiple_choice'?'selected':''}>Multiple Choice</option><option value="true_false" ${q.type==='true_false'?'selected':''}>True/False</option><option value="short_answer" ${q.type==='short_answer'?'selected':''}>Short Answer</option></select></div>
                            <div class="col-md-2"><label class="form-label small fw-bold">Points</label><input type="number" class="form-control" value="${q.points}" oninput="updateQuestion(${i}, 'points', parseFloat(this.value))"></div>
                            <div class="col-12"><div class="p-3 bg-light rounded border">${renderAnswerSection(q, i)}</div></div>
                        </div>
                    </div>
                </div>
            `).join('');
            if(window.lucide) lucide.createIcons();
        }

        function renderAnswerSection(q, i) {
            if (q.type === 'multiple_choice') {
                return `<label class="small text-muted mb-2 d-block">Options (Select correct)</label>` + q.options.map((opt, optIdx) => `
                    <div class="input-group mb-2"><div class="input-group-text bg-white"><input type="radio" name="q_${q.id}" ${q.correctAnswer.toString() === optIdx.toString() ? 'checked' : ''} onchange="updateQuestion(${i}, 'correctAnswer', ${optIdx})"></div><input type="text" class="form-control" placeholder="Option ${optIdx + 1}" value="${opt}" oninput="updateOption(${i}, ${optIdx}, this.value)" onfocus="updateQuestion(${i}, 'correctAnswer', ${optIdx})"></div>
                `).join('');
            } else if (q.type === 'true_false') {
                return `<label class="small text-muted mb-2">Correct Answer</label><div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q_${q.id}" value="true" ${q.correctAnswer === 'true' ? 'checked' : ''} onchange="updateQuestion(${i}, 'correctAnswer', 'true')"><label class="form-check-label">True</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q_${q.id}" value="false" ${q.correctAnswer === 'false' ? 'checked' : ''} onchange="updateQuestion(${i}, 'correctAnswer', 'false')"><label class="form-check-label">False</label></div></div>`;
            } else {
                return `<label class="small text-muted mb-2">Correct Answer</label><input type="text" class="form-control" value="${q.correctAnswer}" oninput="updateQuestion(${i}, 'correctAnswer', this.value)">`;
            }
        }

        function revertBtn(btn, txt) { btn.innerHTML = txt; btn.disabled = false; if(window.lucide) lucide.createIcons(); }
        function showAlert(msg, type) { document.getElementById('alertContainer').innerHTML = `<div class="alert alert-${type} alert-dismissible fade show">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`; }
        function loadSchoolYears() { fetch('api/get_school_years.php').then(r=>r.json()).then(data => { elements.schoolYear.innerHTML = '<option value="">Select School Year</option>'; data.forEach(sy => elements.schoolYear.innerHTML += `<option value="${sy.id}">${sy.school_year}</option>`); }); }
        function updateItemNumbers() { const cat = elements.category.value; elements.itemNumber.innerHTML = ''; for(let i=1; i<=10; i++) elements.itemNumber.innerHTML += `<option value="${i}">${cat.toUpperCase()} ${i}</option>`; }

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
        else init();
    })();
</script>