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
    <title>My Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .grade-card {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            overflow: hidden;
        }

        .table-custom th {
            background-color: #2c3e50;
            color: white;
            font-weight: 500;
            text-align: center;
            vertical-align: middle;
        }

        .table-custom td {
            vertical-align: middle;
            text-align: center;
            padding: 12px;
        }

        .table-custom td:first-child {
            text-align: left;
            padding-left: 20px;
        }

        .grade-badge {
            font-weight: bold;
            font-size: 1rem;
        }

        .grade-passing {
            color: #198754;
        }

        .grade-failing {
            color: #dc3545;
        }

        .btn-view {
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 20px;
        }
    </style>
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold"><i class="bi bi-journal-bookmark-fill me-2 text-primary"></i>My Report Card</h2>
                <p class="text-muted">View your quarterly grades and academic progress.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i> Print Record
                </button>
            </div>
        </div>

        <div class="grade-card bg-white">
            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Subject / Teacher</th>
                            <th style="width: 15%;">1st Quarter</th>
                            <th style="width: 15%;">2nd Quarter</th>
                            <th style="width: 15%;">3rd Quarter</th>
                            <th style="width: 15%;">4th Quarter</th>
                            <th style="width: 10%;">Final</th>
                        </tr>
                    </thead>
                    <tbody id="gradesBody">
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3 text-muted small fst-italic">* Grades marked as "-" have not been released yet.</div>
    </div>

    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalTitle">Grade Breakdown</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Component</th>
                                <th class="text-end">Score</th>
                            </tr>
                        </thead>
                        <tbody id="modalBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetch('api/get_grades.php')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('gradesBody');
                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">No records found.</td></tr>';
                        return;
                    }

                    tbody.innerHTML = data.map(row => {
                        // Helper to render grade cell with optional button
                        const renderCell = (grade, released, type, quarter) => {
                            if (!released || grade === null) return '<span class="text-muted">-</span>';

                            let html = `<div class="grade-badge ${grade >= 75 ? 'grade-passing' : 'grade-failing'}">${parseFloat(grade).toFixed(2)}</div>`;

                            // If Release Type is FULL, show button
                            if (type === 'full') {
                                html += `<button class="btn btn-sm btn-outline-secondary btn-view mt-1" 
                                    onclick="viewDetails('${row.subject}', ${quarter})">View</button>`;
                            }
                            return html;
                        };

                        return `
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">${row.subject}</div>
                                    <div class="small text-muted">${row.teacher}</div>
                                </td>
                                <td>${renderCell(row.q1, row.is_released_q1, row.type_q1, 1)}</td>
                                <td>${renderCell(row.q2, row.is_released_q2, row.type_q2, 2)}</td>
                                <td>${renderCell(row.q3, row.is_released_q3, row.type_q3, 3)}</td>
                                <td>${renderCell(row.q4, row.is_released_q4, row.type_q4, 4)}</td>
                                <td class="bg-light fw-bold">-</td>
                            </tr>
                        `;
                    }).join('');
                });
        });

        function viewDetails(subject, quarter) {
            const modalBody = document.getElementById('modalBody');
            document.getElementById('modalTitle').innerText = `${subject} - Quarter ${quarter}`;
            modalBody.innerHTML = '<tr><td colspan="2" class="text-center p-3">Loading...</td></tr>';
            new bootstrap.Modal(document.getElementById('detailsModal')).show();

            fetch(`api/get_grade_details.php?subject=${encodeURIComponent(subject)}&quarter=${quarter}`)
                .then(r => r.json())
                .then(data => {
                    if (data.length === 0) {
                        modalBody.innerHTML = '<tr><td colspan="2" class="text-center p-3">No details available.</td></tr>';
                        return;
                    }
                    modalBody.innerHTML = data.map(d => `
                        <tr>
                            <td>
                                <div class="fw-bold">${d.description || 'Activity'}</div>
                                <small class="text-uppercase text-muted">${d.component_type}</small>
                            </td>
                            <td class="text-end fw-bold">
                                ${parseFloat(d.score)} / ${parseFloat(d.max_score)}
                            </td>
                        </tr>
                    `).join('');
                });
        }
    </script>
</body>

</html>