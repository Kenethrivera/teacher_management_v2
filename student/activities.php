<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Activities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .activity-card {
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }

        .activity-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .status-pending {
            border-left-color: #ffc107;
        }

        .status-submitted {
            border-left-color: #0dcaf0;
        }

        .status-graded {
            border-left-color: #198754;
        }

        .status-late {
            border-left-color: #dc3545;
        }

        /* Quiz Player Styles */
        .question-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            margin-bottom: 15px;
        }

        .timer-badge {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
            font-size: 1.2rem;
        }
    </style>
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Classroom Activities</h2>
            <div>
                <select id="filterSubject" class="form-select d-inline-block w-auto me-2">
                    <option value="all">All Subjects</option>
                </select>
                <select id="filterStatus" class="form-select d-inline-block w-auto">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="submitted">Submitted</option>
                    <option value="graded">Graded</option>
                </select>
            </div>
        </div>

        <div id="activityList" class="row g-4">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="activityModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalTitle">Activity Title</h5>
                        <small id="modalSubject" class="opacity-75">Subject Name</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light" id="modalBody">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="submitBtn">
                        <i class="bi bi-send-fill me-2"></i> Submit Work
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let activities = [];
        let currentActivity = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadActivities();
            document.getElementById('filterStatus').addEventListener('change', renderActivities);
            document.getElementById('submitBtn').addEventListener('click', submitWork);
        });

        function loadActivities() {
            fetch('api/get_activities.php')
                .then(res => res.json())
                .then(data => {
                    activities = data;
                    populateSubjectFilter(data); // <--- NEW STEP
                    renderActivities();
                });
        }
        function populateSubjectFilter(data) {
            const subjects = [...new Set(data.map(a => a.subject_name))]; // Get unique subjects
            const select = document.getElementById('filterSubject');

            // Keep "All Subjects" option
            select.innerHTML = '<option value="all">All Subjects</option>';

            subjects.forEach(sub => {
                select.innerHTML += `<option value="${sub}">${sub}</option>`;
            });

            // Add listener
            select.addEventListener('change', renderActivities);
        }

        function renderActivities() {
            const statusFilter = document.getElementById('filterStatus').value;
            const subjectFilter = document.getElementById('filterSubject').value; // <--- NEW
            const container = document.getElementById('activityList');
            container.innerHTML = '';

            const filtered = activities.filter(a => {
                const statusMatch = (statusFilter === 'all') || ((a.status || 'pending') === statusFilter);
                const subjectMatch = (subjectFilter === 'all') || (a.subject_name === subjectFilter); // <--- NEW
                return statusMatch && subjectMatch;
            });

            if (filtered.length === 0) {
                container.innerHTML = '<div class="col-12 text-center text-muted py-5">No activities found.</div>';
                return;
            }

            filtered.forEach(act => {
                const status = act.status || 'pending';
                const statusBadge = getStatusBadge(status, act.score, act.max_score);
                const btnText = status === 'pending' ? 'Start' : 'View';
                const btnClass = status === 'pending' ? 'btn-primary' : 'btn-outline-secondary';

                const html = `
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm activity-card status-${status}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge bg-light text-dark border">${act.subject_name}</span>
                                    ${statusBadge}
                                </div>
                                <h5 class="card-title fw-bold text-truncate">${act.title}</h5>
                                <p class="small text-muted mb-2">
                                    Due: ${new Date(act.due_date).toLocaleDateString()} 
                                    <span class="ms-2">• ${act.activity_type.toUpperCase()}</span>
                                </p>
                                <p class="card-text text-secondary small" style="height: 40px; overflow:hidden;">
                                    ${act.description || 'No instructions provided.'}
                                </p>
                                <button class="btn ${btnClass} w-100 mt-3" onclick="openActivity(${act.activity_id})">
                                    ${btnText} Activity
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += html;
            });
        }

        function getStatusBadge(status, score, max) {
            if (status === 'pending') return '<span class="badge bg-warning text-dark">To Do</span>';
            if (status === 'submitted') return '<span class="badge bg-info text-dark">Submitted</span>';
            if (status === 'graded') return `<span class="badge bg-success">${score} / ${max}</span>`;
            return '<span class="badge bg-secondary">Unknown</span>';
        }

        function openActivity(id) {
            currentActivity = activities.find(a => a.activity_id == id);
            if (!currentActivity) return;

            document.getElementById('modalTitle').innerText = currentActivity.title;
            document.getElementById('modalSubject').innerText = currentActivity.subject_name;

            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border"></div></div>';

            const modal = new bootstrap.Modal(document.getElementById('activityModal'));
            modal.show();

            // Check if already submitted or just starting
            if (currentActivity.status && currentActivity.status !== 'pending') {
                showResultView(modalBody);
            } else {
                if (currentActivity.activity_type === 'quiz') {
                    loadQuiz(currentActivity.activity_id);
                } else {
                    loadFileUpload();
                }
            }
        }

        // --- QUIZ LOGIC ---
        function loadQuiz(activityId) {
            // Need a new API to fetch questions ONLY when starting
            fetch(`api/get_quiz_data.php?activity_id=${activityId}`)
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('modalBody');
                    container.innerHTML = `<div class="alert alert-info small"><i class="bi bi-info-circle me-2"></i>Answer all questions before submitting.</div>`;

                    data.forEach((q, index) => {
                        let inputHtml = '';

                        if (q.question_type === 'multiple_choice') {
                            q.options.forEach(opt => {
                                inputHtml += `
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="q_${q.question_id}" value="${opt.option_id}">
                                        <label class="form-check-label">${opt.option_text}</label>
                                    </div>`;
                            });
                        } else if (q.question_type === 'true_false') {
                            inputHtml = `
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q_${q.question_id}" value="true">
                                    <label class="form-check-label">True</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q_${q.question_id}" value="false">
                                    <label class="form-check-label">False</label>
                                </div>`;
                        } else {
                            inputHtml = `<input type="text" class="form-control" name="q_${q.question_id}" placeholder="Type your answer here...">`;
                        }

                        container.innerHTML += `
                            <div class="question-box">
                                <h6 class="fw-bold">Q${index + 1}: ${q.question_text}</h6>
                                <div class="mt-3">${inputHtml}</div>
                            </div>
                        `;
                    });

                    // Show Submit Button
                    document.getElementById('submitBtn').classList.remove('d-none');
                });
        }

        // --- FILE UPLOAD LOGIC ---
        function loadFileUpload() {
            document.getElementById('modalBody').innerHTML = `
                <div class="p-4 text-center border rounded bg-white">
                    <h5>${currentActivity.description}</h5>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Upload your work (PDF, DOCX, IMG)</label>
                        <input type="file" class="form-control" id="fileUpload">
                    </div>
                    <div class="form-text">Max size: 5MB</div>
                </div>
            `;
            document.getElementById('submitBtn').classList.remove('d-none');
        }

        // --- SUBMISSION ---
        function submitWork() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = 'Submitting...';

            const formData = new FormData();
            formData.append('activity_id', currentActivity.activity_id);
            formData.append('type', currentActivity.activity_type);

            if (currentActivity.activity_type === 'quiz') {
                // Collect answers
                const answers = [];
                document.querySelectorAll('[name^="q_"]').forEach(input => {
                    if (input.type === 'radio' && input.checked) {
                        answers.push({ q_id: input.name.replace('q_', ''), val: input.value });
                    } else if (input.type === 'text') {
                        answers.push({ q_id: input.name.replace('q_', ''), val: input.value });
                    }
                });
                formData.append('answers', JSON.stringify(answers));

            } else {
                // Collect file
                const fileInput = document.getElementById('fileUpload');
                if (fileInput.files.length === 0) {
                    alert('Please select a file before submitting.');
                    btn.disabled = false;
                    btn.innerHTML = 'Submit Work';
                    return; // Stop the function here
                }
                formData.append('file', fileInput.files[0]);
            }

            fetch('actions/submit_activity.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Submitted Successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        btn.disabled = false;
                        btn.innerHTML = 'Submit Work';
                    }
                });
        }

        function showResultView(container) {
            document.getElementById('submitBtn').classList.add('d-none');
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-check-circle-fill text-success display-1"></i>
                    <h3 class="mt-3">Already Submitted</h3>
                    <p class="text-muted">You submitted this on ${new Date(currentActivity.submitted_at).toLocaleString()}</p>
                    ${currentActivity.score ? `<h4 class="fw-bold">Score: ${currentActivity.score} / ${currentActivity.max_score}</h4>` : '<span class="badge bg-warning text-dark">Grading Pending</span>'}
                </div>
            `;
        }
    </script>
</body>

</html>