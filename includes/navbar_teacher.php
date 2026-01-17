<style>
    .section {
        display: none;
    }

    .section.active {
        display: block;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid px-4">

        <a class="navbar-brand fw-semibold" href="#">
            <i class="bi bi-mortarboard-fill me-1"></i>
            Classroom Management
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#teacherNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="teacherNavbar">

            <ul class="navbar-nav mx-auto gap-lg-4">
                <li class="nav-item">
                    <a href="#" class="nav-link active" data-section="dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="masterlist">Masterlist</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="classrecord">Class Record</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="activities">Activities</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="results">Results</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center"
                            style="width: 32px; height: 32px; font-weight: bold;">
                            <?= strtoupper(substr($_SESSION['name'] ?? 'T', 0, 1)) ?>
                        </div>
                        <span><?= htmlspecialchars($_SESSION['name'] ?? 'Teacher') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="../logout.php">
                                <i data-lucide="log-out" style="width:16px"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

        </div>
    </div>
</nav>
