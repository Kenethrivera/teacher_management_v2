<div class="row g-4 fade-in">

    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                Hello, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Teacher'); ?>! 👋
            </h2>
            <p class="text-muted mb-0">Here's what's happening in your classes today.</p>
        </div>
        <div class="d-none d-md-block">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-normal">
                <i data-lucide="calendar" style="width:14px; margin-bottom:2px"></i>
                <?php echo date('F j, Y'); ?>
            </span>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 p-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3">
                    <i data-lucide="users" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Students</h6>
                    <h3 class="mb-0 fw-bold" id="dash_students">-</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 p-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3">
                    <i data-lucide="layers" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Sections Handled</h6>
                    <h3 class="mb-0 fw-bold" id="dash_sections">-</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 p-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 me-3">
                    <i data-lucide="file-text" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Active Activities</h6>
                    <h3 class="mb-0 fw-bold" id="dash_activities">-</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i data-lucide="clock" style="width:16px" class="me-1 text-muted"></i> Recently Posted Activities
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" id="recent_list">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary spinner-border-sm"></div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-center border-0 py-3">
                <button class="btn btn-link text-decoration-none small fw-bold"
                    onclick="document.querySelector('.nav-link[data-section=\'results\']').click()">
                    View All Activities &rarr;
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary text-start d-flex align-items-center p-3"
                        onclick="document.querySelector('.nav-link[data-section=\'activities\']').click()">
                        <div class="bg-primary text-white rounded-circle p-1 me-3 d-flex"><i data-lucide="plus"
                                style="width:16px"></i></div>
                        <div>
                            <div class="fw-bold small">Create Activity</div>
                            <div class="text-muted" style="font-size: 11px;">Quiz or Assignment</div>
                        </div>
                    </button>

                    <button class="btn btn-outline-success text-start d-flex align-items-center p-3"
                        onclick="document.querySelector('.nav-link[data-section=\'masterlist\']').click()">
                        <div class="bg-success text-white rounded-circle p-1 me-3 d-flex"><i data-lucide="user-plus"
                                style="width:16px"></i></div>
                        <div>
                            <div class="fw-bold small">Manage Students</div>
                            <div class="text-muted" style="font-size: 11px;">Enroll or update</div>
                        </div>
                    </button>

                    <button class="btn btn-outline-dark text-start d-flex align-items-center p-3"
                        onclick="document.querySelector('.nav-link[data-section=\'classrecord\']').click()">
                        <div class="bg-dark text-white rounded-circle p-1 me-3 d-flex">
                            <i data-lucide="book-open" style="width:16px"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">Class Record</div>
                            <div class="text-muted" style="font-size: 11px;">View grades</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .fade-in {
        animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .activity-item:hover {
        background-color: #f8f9fa;
        cursor: default;
    }
</style>

<script>
    (function () {
        'use strict';
        if (window.lucide) lucide.createIcons();

        // Fetch Dashboard Data
        fetch('api/get_dashboard_stats.php')
            .then(res => res.json())
            .then(data => {
                // 1. Stats
                animateValue("dash_students", 0, data.students, 1000);
                animateValue("dash_sections", 0, data.sections, 800);
                animateValue("dash_activities", 0, data.activities, 800);

                // 2. Recent Activities List
                const list = document.getElementById('recent_list');
                if (data.recent && data.recent.length > 0) {
                    list.innerHTML = data.recent.map(item => {
                        const icon = item.activity_type === 'quiz' ? 'help-circle' : 'file-text';
                        const badgeColor = item.activity_type === 'quiz' ? 'warning' : 'info';
                        // Clean Date format
                        const due = item.due_date ? new Date(item.due_date).toLocaleDateString() : 'No Due Date';

                        return `
                            <div class="list-group-item border-0 border-bottom py-3 activity-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-light p-2 me-3 text-muted">
                                            <i data-lucide="${icon}" style="width:20px;height:20px"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark small">${item.title}</h6>
                                            <small class="text-muted" style="font-size: 11px;">
                                                ${item.subject_name} • ${item.grade_level}-${item.section_name}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-${badgeColor} bg-opacity-10 text-${badgeColor} rounded-pill" style="font-size: 10px;">
                                            ${item.activity_type.toUpperCase()}
                                        </span>
                                        <div class="text-muted mt-1" style="font-size: 10px;">Due: ${due}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                    if (window.lucide) lucide.createIcons();
                } else {
                    list.innerHTML = `
                        <div class="text-center py-5 text-muted">
                            <i data-lucide="check-circle" style="width:32px; height:32px; opacity:0.3" class="mb-2"></i>
                            <p class="small mb-0">No recent activities found.</p>
                        </div>`;
                    if (window.lucide) lucide.createIcons();
                }
            })
            .catch(err => console.error(err));

        function animateValue(id, start, end, duration) {
            const obj = document.getElementById(id);
            if (!obj) return;
            if (end === 0) { obj.innerText = "0"; return; }
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                obj.innerText = Math.floor(progress * (end - start) + start);
                if (progress < 1) window.requestAnimationFrame(step);
                else obj.innerText = end;
            };
            window.requestAnimationFrame(step);
        }
    })();
</script>