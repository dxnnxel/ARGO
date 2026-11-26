<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = (int) $_SESSION['user_id'];

/* ---------- profile ---------- */
$stmt = $conn->prepare('SELECT * FROM student_profiles WHERE user_id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

$name = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
if ($name === '') $name = 'Student';

$parts    = preg_split('/\s+/', $name);
$initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));

/* ---------- enrolled subjects ---------- */
$stmt = $conn->prepare(
    'SELECT s.id, s.code, s.title, s.units, s.schedule, e.grade
       FROM enrollments e
       JOIN subjects s ON s.id = e.subject_id
      WHERE e.user_id = ?
      ORDER BY s.code'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$hasSubjects = count($subjects) > 0;

/* ---------- stats computed from the rows ---------- */
$numSubjects = count($subjects);
$totalUnits  = 0;
$weighted    = 0;
$gradedUnits = 0;
$released    = 0;
$passed      = 0;
foreach ($subjects as $s) {
    $totalUnits += (int) $s['units'];
    if ($s['grade'] !== null) {
        $released++;
        $weighted    += $s['grade'] * $s['units'];
        $gradedUnits += $s['units'];
        if ($s['grade'] <= 3.00) $passed++;   // CvSU scale: 1.00 best, 3.00 passing
    }
}
$pending     = $numSubjects - $released;
$gwa         = $gradedUnits > 0 ? number_format($weighted / $gradedUnits, 2) : '--';
$passingRate = $released > 0 ? round($passed / $released * 100) : 0;

/* ---------- classes available to join (TEST ONLY, remove when LMS is connected) ---------- */
$stmt = $conn->prepare(
    'SELECT id, code, title, units, schedule FROM subjects
      WHERE id NOT IN (SELECT subject_id FROM enrollments WHERE user_id = ?)
      ORDER BY code'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$available = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$colors = ['teal', 'olive', 'green'];
?>
<!DOCTYPE html>
<html>
   <head>
    <title>ARGO</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="argo.css?v=2">
    <link rel="stylesheet" href="responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
   </head>

   <body>
    <!--sidebar-->
    <div class="sidebar">
      <div class="brand">
        <div class="sidebar-logo">
         <img src="images/cvsulogo.png" alt="logo" class="logo">
        </div>
         <div class="sys-title-card">
            <div class="sys-title"> A.R.G.O </div>
            <div class="sys-description">Academic Record Grades Organizer</div>
         </div>
      </div>
     <div class="student_card">
        <div class="student_info">
         <div class="initial"><?= htmlspecialchars($initials) ?></div>
            <span><?= htmlspecialchars($name) ?></span>
        </div>
     </div>
     <ul class="menu">
        <li>
            <a href="student.php">
           <i class="fas fa-book-open fa-outline"></i>
            <span> My Subject</span>
            </a>
       </li>
        <li>
            <a href="fgrades.php">
            <i class="fas fa-file-alt fa-outline"></i>
            <span>Grades</span>
            </a>
       </li>
         <li>
        <a href="logout.php">
            <span>  &larr; Log Out</span>
         </a>
       </li>
    </ul>
    </div>

    <!--main-content-->
     <div class="main-content">
         <div class="header-wrapper">
          <div class="header-left">
              <img src="images/cvsulogo.png" alt="logo" class="logo">
          </div>
          <div class="header-center">
             <p> CvSU E-Grade </p>
          </div>
          <div class="header-right">
            <div class="profile"><i class="far fa-user profile-icon"></i>
               <div class="profile-text">
                  <h1><?= htmlspecialchars($name) ?></h1>
                   <p>STUDENT PROFILE</p>
                </div>
                <div class="bell-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                 </svg>
                <span class="bell-dot"></span>
            </div>
            </div>
          </div>
         </div>

         <div class="main-page-student">
            <h1>Subjects</h1>
            <p>2nd Semester ● A.Y. 2025-2026</p>
         </div>

         <div class="greet-card-container">
            <div class="date">
               <p><?= date('l, F j') ?></p>
            </div>
            <div class="greet-card">
               <h1 class="greeting">Hello,</h1>
               <div class="greet">
                  <span class="user"><?= htmlspecialchars($name) ?></span>
                  <div class="message">
                  <span class="motiv">Set High Goals, Get High Grades</span>
                  <span class="motiv">Aim High, Achieve Higher</span>
                  </div>
                  <div class="num-sub"><span><?= $numSubjects ?> Subjects</span></div>
                  <div class="num-unit"><span><?= $totalUnits ?> Units</span></div>
                  <div class="gwa"><span>GWA <?= $gwa ?></span></div>
               </div>
            </div>
         </div>

         <div class="sub-grid">
          <?php if (!$hasSubjects): ?>
            <div class="empty-state">
               <i class="fas fa-chalkboard-teacher"></i>
               <h2>You are not enrolled in any class yet</h2>
               <p>Enroll in a class in the LMS to see your subjects here.</p>
            </div>
          <?php else: ?>
            <?php foreach ($subjects as $i => $s): ?>
            <div class="subject-content">
               <div class="sub-top <?= $colors[$i % count($colors)] ?>">
                  <div class="sub-l">
                     <h1><?= htmlspecialchars($s['code']) ?></h1>
                     <p><?= htmlspecialchars($s['title']) ?></p>
                  </div>
                  <div class="sub-r">
                     <p><?= (int) $s['units'] ?> units</p>
                  </div>
               </div>
               <div class="sub-bottom">
                  <div class="sub-l">
                     <p><?= htmlspecialchars($s['schedule']) ?></p>
                     <a href="grades.php?subject_id=<?= (int) $s['id'] ?>">
                       <button class="sub-button" type="button">VIEW MORE</button>
                     </a>
                  </div>
                  <div class="sub-r">
                     <p><?= $s['grade'] !== null ? number_format($s['grade'], 2) : '--' ?></p>
                     <!-- TEST ONLY -->
                     <form method="post" action="join_class.php">
                        <input type="hidden" name="action" value="leave">
                        <input type="hidden" name="subject_id" value="<?= (int) $s['id'] ?>">
                        <button class="leave-btn" type="submit">Leave (test)</button>
                     </form>
                  </div>
               </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
         </div>

         <!-- ===== TEST PANEL: delete this block when the LMS is connected ===== -->
         <?php if ($available): ?>
         <div class="test-panel">
            <h3>Test: Join a class</h3>
            <small>Temporary stand-in for the LMS. Remove this panel once it is connected.</small>
            <?php foreach ($available as $a): ?>
            <form method="post" action="join_class.php" class="test-row">
               <span><strong><?= htmlspecialchars($a['code']) ?></strong> &ndash; <?= htmlspecialchars($a['title']) ?> (<?= htmlspecialchars($a['schedule']) ?>)</span>
               <input type="hidden" name="subject_id" value="<?= (int) $a['id'] ?>">
               <button type="submit">Join class</button>
            </form>
            <?php endforeach; ?>
         </div>
         <?php endif; ?>
         <!-- ===== END TEST PANEL ===== -->

      <div class="gr-side-column">
    <section class="gsc-grid" aria-label="Grade summary">
    <div class="gsc-card gsc-card--green">
      <div class="gsc-top">
        <span class="gsc-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2z"/><path d="M22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7z"/></svg>
        </span>
        <span class="gsc-corner">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>
        </span>
      </div>
      <p class="gsc-value"><?= $numSubjects ?></p>
      <h3 class="gsc-label">Enrolled Subjects</h3>
      <p class="gsc-desc">Subjects for this semester</p>
   </div>

    <div class="gsc-card gsc-card--indigo">
      <div class="gsc-top">
        <span class="gsc-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 20v-6"/><path d="M12 20V6"/><path d="M17 20v-10"/></svg>
        </span>
        <span class="gsc-corner">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 17 6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg>
        </span>
      </div>
      <p class="gsc-value"><?= $gwa ?></p>
      <h3 class="gsc-label">GWA / GPA</h3>
      <p class="gsc-desc">Current general average</p>
   </div>

    <div class="gsc-card gsc-card--orange">
      <div class="gsc-top">
        <span class="gsc-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1"/><path d="m9 13 2 2 4-4"/></svg>
        </span>
        <span class="gsc-corner">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
        </span>
      </div>
      <p class="gsc-value"><?= $released ?>/<?= $numSubjects ?></p>
      <h3 class="gsc-label">Grades Released</h3>
      <p class="gsc-desc">Subjects with posted grades</p>
   </div>

    <div class="gsc-card gsc-card--purple">
      <div class="gsc-top">
        <span class="gsc-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4"/><path d="M16 3v4"/><path d="M3 10h18"/></svg>
        </span>
        <span class="gsc-corner">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        </span>
      </div>
      <p class="gsc-value"><?= $pending ?></p>
      <h3 class="gsc-label">Pending Grades</h3>
      <p class="gsc-desc">Subjects awaiting submission</p>
   </div>
  </section>

         <div class="avg-status">
            <p>Passing Rate</p>
            <div class="ring" data-percent="<?= $passingRate ?>">
               <svg viewBox="0 0 120 120">
                <circle class="track"    cx="60" cy="60" r="50"/>
                <circle class="progress" cx="60" cy="60" r="50"/>
               </svg>
               <div class="label">
                  <span class="avg"><?= $passingRate ?>%</span>
                  <span class="caption"></span>
               </div>
           </div>
            <span class="cap">Focus, Grind and Achieve your Goal</span>
         </div>
  </div>
      </div>
      <script src="script.js"></script>
   </body>
</html>
