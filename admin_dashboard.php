<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "administrator") {
    header("Location: login.php");
    exit;
}

$adminName = $_SESSION["user_name"] ?? "Admin";
$initials = "";
foreach (preg_split('/\s+/', trim($adminName)) as $part) {
    $initials .= strtoupper(substr($part, 0, 1));
}
$initials = substr($initials, 0, 2);

/* Placeholder data - replace with database queries */
$stats = [
    ["icon" => "fa-users",          "tone" => "green", "label" => "Total Accounts",   "value" => "1,284", "note" => "+24 this semester"],
    ["icon" => "fa-graduation-cap", "tone" => "green", "label" => "Active Instructors", "value" => "48",  "note" => "12 departments"],
    ["icon" => "fa-clipboard-list", "tone" => "amber", "label" => "Pending Approvals", "value" => "18",   "note" => "5 need attention"],
    ["icon" => "fa-heartbeat",      "tone" => "green", "label" => "System Status",    "value" => "Online", "note" => "All services operational"],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="argo.css?v=2"/>
    <link rel="stylesheet" href="responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
       
    </style>
</head>
<body>
<div class="admin-shell">

    <!-- sidebar -->
    <div class="sidebar">
        <!-- brand -->
        <div class="brand">
            <div class="sidebar-logo">
                <img class="logo" src="images/cvsulogo.png" alt="CvSU logo">
            </div>
            <div class="sys-title-card">
                <div class="sys-title">A.R.G.O</div>
                <div class="sys-description">Academic Record Grades Organizer</div>
            </div>
        </div>

        <!-- profile -->
        <div class="student_card">
            <div class="student_info">
                <div class="initial"><?= htmlspecialchars($initials) ?></div>
                <span><?= htmlspecialchars($adminName) ?></span>
            </div>
            <small>System Administrator</small>
        </div>

        <ul class="menu">
            <li class="menu-label">Administration</li>
            <li>
                <a href="admin_dashboard.php" class="active">
                    <i class="fas fa-th-large"></i>
                    <span>Overview</span>
                </a>
            </li>
            <li>
                <a href="user_management.php">
                    <i class="fas fa-users-cog"></i>
                    <span>User Management</span>
                </a>
            </li>
            <li>
                <a href="activity_logs.php">
                    <i class="fas fa-history"></i>
                    <span>Activity Logs</span>
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Log out</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- main content -->
    <main class="admin-main">
        <!-- top header -->
        <div class="topbar">
            <div class="topbar-left">
                <img src="images/cvsulogo.png" alt="CvSU logo">
                <h1 class="topbar-title">Admin Dashboard</h1>
            </div>
            <div class="topbar-right">
                <div class="topbar-user">
                    <div class="topbar-avatar"><i class="far fa-user"></i></div>
                    <div class="topbar-name">
                        <?= htmlspecialchars($adminName) ?>
                        <span class="topbar-role">Administrator</span>
                    </div>
                </div>
                <button type="button" class="bell" aria-label="Notifications">
                    <i class="far fa-bell"></i><span class="badge-dot"></span>
                </button>
            </div>
        </div>

        <div class="admin-content">
        <header class="page-head">
            <div>
                <div class="crumb">ADMINISTRATION</div>
                <h2>System Overview</h2>
                <p>Monitor users, records, and portal activity.</p>
            </div>
            <a href="add_account.php" class="btn-primary"><i class="fas fa-plus"></i> Add account</a>
        </header>

        <!-- role portals -->
        <section class="card portal-access" id="portal-access">
            <div class="card-head">
                <div>
                    <h3>Access role interfaces</h3>
                    <p class="sub">Open any portal as an administrator to inspect its current UI and workflow.</p>
                </div>
                <span class="pill"><i class="fas fa-shield-alt"></i> Administrator access</span>
            </div>
            <div class="portal-grid">
                <div class="portal-card featured">
                    <div class="portal-top">
                        <div class="icon-box tone-green"><i class="fas fa-graduation-cap"></i></div>
                        <small>STUDENT VIEW</small>
                    </div>
                    <h4>Student Portal</h4>
                    <p>Review the student dashboard, enrolled subjects, and detailed grade reports.</p>
                    <a href="student.php">Open Student Portal <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="portal-card">
                    <div class="portal-top">
                        <div class="icon-box tone-blue"><i class="fas fa-file-alt"></i></div>
                        <small>FACULTY VIEW</small>
                    </div>
                    <h4>Instructor Portal</h4>
                    <p>Inspect class rosters, grading tables, submissions, and attendance tools.</p>
                    <a href="index.php">Open Instructor Portal <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="portal-card">
                    <div class="portal-top">
                        <div class="icon-box tone-amber"><i class="fas fa-shield-alt"></i></div>
                        <small>COORDINATOR VIEW</small>
                    </div>
                    <h4>Coordinator Portal</h4>
                    <p>Check submission queues, grade review workflows, and approval actions.</p>
                    <a href="coordinator.php">Open Coordinator Portal <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </section>

        <!-- stats -->
        <section class="stat-grid">
            <?php foreach ($stats as $s): ?>
                <div class="card stat">
                    <div class="icon-box tone-<?= $s["tone"] ?>"><i class="fas <?= $s["icon"] ?>"></i></div>
                    <div>
                        <div class="label"><?= htmlspecialchars($s["label"]) ?></div>
                        <div class="value"><?= htmlspecialchars($s["value"]) ?></div>
                        <div class="note"><?= htmlspecialchars($s["note"]) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- activity + accounts by role
        <section class="two-col">
            <div class="card" id="activity">
                <div class="panel-head card-head">
                    <div>
                        <h3>Recent activity</h3>
                        <p class="sub">Latest actions across the E-Grade System</p>
                    </div>
                    <a href="activity_logs.php">View all</a>
                </div>
                <?php foreach ($activity as $a): ?>
                    <div class="activity-item">
                        <div class="icon-box tone-<?= $a["tone"] ?>"><i class="fas <?= $a["icon"] ?>"></i></div>
                        <div class="text">
                            <div class="title"><?= htmlspecialchars($a["title"]) ?></div>
                            <div class="desc"><?= htmlspecialchars($a["desc"]) ?></div>
                        </div>
                        <div class="time"><?= htmlspecialchars($a["time"]) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="panel-head">
                    <h3>Accounts by role</h3>
                    <p class="sub">Current user distribution</p>
                </div>
                <div class="roles-body">
                    <?php foreach ($roles as $r): ?>
                        <div class="role-row">
                            <div class="line"><span><?= htmlspecialchars($r[0]) ?></span><b><?= number_format($r[1]) ?></b></div>
                            <div class="bar"><span style="width: <?= $roleWidths[$r[0]] ?>%"></span></div>
                        </div>
                    <?php endforeach; ?>
                    <a href="user_management.php" class="manage-btn">Manage user accounts <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </section>-->

        <!-- system health -->
        <section class="card health">
            <div class="health-title">
                <span class="dot"></span>
                <div>
                    <h3>System health</h3>
                    <p class="sub">All services are running normally</p>
                </div>
            </div>
            <div class="health-meta">
                <div><small>Database</small><strong>Operational</strong></div>
                <div><small>Last backup</small><strong>Today, 2:00 AM</strong></div>
                <div><small>Academic term</small><strong>2nd Semester 2025–2026</strong></div>
            </div>
        </section>
        </div><!-- /admin-content -->
    </main>

</div>
</body>
</html>
