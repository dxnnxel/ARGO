<?php
session_start();
require_once 'db.php';          // your existing mysqli connection ($conn)

/* ---------- settings ---------- */
$ENFORCE_ROLE = false;           // only users with role 'administrator' may open this page
const PASSING = 75;            
const WEIGHTS = ['attendance' => 0.10, 'quiz' => 0.20, 'activity' => 0.30, 'exam' => 0.40];
const CATEGORY_ORDER = ['quiz', 'activity', 'exam'];
const CATEGORY_LABEL = ['quiz' => 'QUIZ', 'activity' => 'ACTIVITY', 'exam' => 'EXAM'];

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = (int) $_SESSION['user_id'];

function run(mysqli $c, string $sql, string $types = '', array $p = []): array {
    $st = $c->prepare($sql);
    if ($types !== '') $st->bind_param($types, ...$p);
    $st->execute();
    $res = $st->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}
function h($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

$me = run($conn, 'SELECT u.role, COALESCE(ep.first_name, sp.first_name) AS first_name,
                         COALESCE(ep.last_name, sp.last_name) AS last_name
                    FROM users u
                    LEFT JOIN employee_profiles ep ON ep.user_id = u.id
                    LEFT JOIN student_profiles sp ON sp.user_id = u.id
                   WHERE u.id = ?', 'i', [$userId])[0] ?? null;
if (!$me || ($ENFORCE_ROLE && !in_array($me['role'], ['instructor', 'administrator'], true))) { http_response_code(403); exit('Instructors only.'); }
$instrName = trim(($me['first_name'] ?? '') . ' ' . ($me['last_name'] ?? '')) ?: 'Instructor';
$parts = preg_split('/\s+/', $instrName);
$initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));

/* ---------- which subject ---------- */
$subjectId = (int) ($_GET['subject_id'] ?? $_POST['subject_id'] ?? 1);
$subject = run($conn, 'SELECT * FROM subjects WHERE id = ?', 'i', [$subjectId])[0] ?? null;
if (!$subject) { http_response_code(404); exit('Subject not found'); }

$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));

/* ---------- actions (add / remove activities, save scores) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) { http_response_code(400); exit('Bad request'); }
    $action = $_POST['action'] ?? '';
    $msg = '';

    if ($action === 'add_activity') {
        $term = $_POST['term'] ?? '';
        $cat  = $_POST['category'] ?? '';
        $count = max(1, min(20, (int) ($_POST['count'] ?? 1)));
        $max  = (float) ($_POST['max_score'] ?? 100);
        if (in_array($term, ['midterm', 'finals'], true) && in_array($cat, ['quiz', 'activity', 'exam'], true) && $max > 0) {
            for ($i = 0; $i < $count; $i++) {
                run($conn, 'INSERT INTO activities (subject_id, term, category, max_score) VALUES (?,?,?,?)',
                    'issd', [$subjectId, $term, $cat, $max]);
            }
        } else { $msg = 'Invalid activity details.'; }

    } elseif ($action === 'remove_activity') {
        run($conn, 'DELETE FROM activities WHERE id = ? AND subject_id = ?', 'ii', [(int) ($_POST['activity_id'] ?? 0), $subjectId]);

    } elseif ($action === 'save') {
        $maxById = [];
        foreach (run($conn, 'SELECT id, max_score FROM activities WHERE subject_id = ?', 'i', [$subjectId]) as $a)
            $maxById[(int) $a['id']] = (float) $a['max_score'];
        $enrolled = [];
        foreach (run($conn, 'SELECT user_id FROM enrollments WHERE subject_id = ?', 'i', [$subjectId]) as $r)
            $enrolled[(int) $r['user_id']] = true;

        foreach (($_POST['score'] ?? []) as $aid => $byUser) {
            if (!isset($maxById[(int) $aid]) || !is_array($byUser)) continue;
            foreach ($byUser as $uid => $val) {
                if (!isset($enrolled[(int) $uid])) continue;
                $val = trim((string) $val);
                if ($val === '') continue;
                if (is_numeric($val) && $val >= 0 && $val <= $maxById[(int) $aid]) {
                    // INSERT IGNORE: a score that was already received is never overwritten
                    run($conn, 'INSERT IGNORE INTO activity_scores (activity_id, user_id, score) VALUES (?,?,?)',
                        'iid', [(int) $aid, (int) $uid, (float) $val]);
                } else { $msg = 'A score was ignored: it must be between 0 and the activity total.'; }
            }
        }
        foreach (($_POST['att'] ?? []) as $uid => $val) {
            if (!isset($enrolled[(int) $uid])) continue;
            $val = trim((string) $val);
            if ($val === '') $att = null;
            elseif (is_numeric($val) && $val >= 0 && $val <= 100) $att = (float) $val;
            else { $msg = 'Attendance must be between 0 and 100.'; continue; }
            run($conn, 'UPDATE enrollments SET attendance = ? WHERE user_id = ? AND subject_id = ?', 'dii', [$att, (int) $uid, $subjectId]);
        }
    }
    if ($msg !== '') $_SESSION['flash'] = $msg;
    header('Location: list.php?subject_id=' . $subjectId);
    exit;
}
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

/* ---------- load the table data ---------- */
$activities = run($conn, 'SELECT id, term, category, max_score FROM activities WHERE subject_id = ? ORDER BY id', 'i', [$subjectId]);

// Everyone who joined this subject (enrollments row) shows here automatically.
$students = run($conn, 'SELECT u.id AS user_id, u.email, e.attendance,
                               COALESCE(sp.student_id, CAST(u.id AS CHAR)) AS student_no,
                               sp.first_name, sp.last_name
                          FROM enrollments e
                          JOIN users u ON u.id = e.user_id
                          LEFT JOIN student_profiles sp ON sp.user_id = u.id
                         WHERE e.subject_id = ?
                         ORDER BY sp.last_name, sp.first_name, u.id', 'i', [$subjectId]);

$scores = [];
foreach (run($conn, 'SELECT s.activity_id, s.user_id, s.score FROM activity_scores s
                       JOIN activities a ON a.id = s.activity_id WHERE a.subject_id = ?', 'i', [$subjectId]) as $r)
    $scores[(int) $r['user_id']][(int) $r['activity_id']] = (float) $r['score'];

/* ---------- grading ---------- */
function termColumns(array $acts, string $term): array {
    $cols = [];
    foreach (CATEGORY_ORDER as $cat) {
        $n = 0;
        foreach ($acts as $a) {
            if ($a['term'] === $term && $a['category'] === $cat) {
                $n++;
                $cols[] = ['id' => (int) $a['id'], 'category' => $cat, 'number' => $n, 'max' => (float) $a['max_score']];
            }
        }
    }
    return $cols;
}
function termGrade(array $cols, array $sc, $attendance): ?float {
    $parts = [];
    foreach (CATEGORY_ORDER as $cat) {
        $tot = 0; $n = 0;
        foreach ($cols as $c) {
            if ($c['category'] !== $cat || !isset($sc[$c['id']])) continue;
            $tot += $sc[$c['id']] / $c['max'] * 100; $n++;
        }
        if ($n) $parts[] = [$tot / $n, WEIGHTS[$cat]];
    }
    if ($attendance !== null && $attendance !== '') $parts[] = [(float) $attendance, WEIGHTS['attendance']];
    if (!$parts) return null;
    $w = 0; $s = 0;
    foreach ($parts as $p) { $s += $p[0] * $p[1]; $w += $p[1]; }
    return $s / $w;
}
function fmt(?float $v): string { return $v === null ? '' : number_format($v, 2); }
function scoreCells(array $cols, array $sc, int $uid): void {
    foreach ($cols as $c) {
        if (isset($sc[$c['id']])) {
            // score already received: shown as plain text, no input, cannot be changed
            echo '<td>' . rtrim(rtrim(number_format($sc[$c['id']], 2, '.', ''), '0'), '.') . '</td>';
        } else {
            echo '<td><input class="score-input" data-lock="1" form="gradeForm" type="number" min="0" max="' . $c['max'] . '" step="any"'
               . ' name="score[' . $c['id'] . '][' . $uid . ']" value="" title="Max ' . $c['max'] . '"></td>';
        }
    }
}

$mid = termColumns($activities, 'midterm');
$fin = termColumns($activities, 'finals');

$rows = []; $passed = 0; $failed = 0;
foreach ($students as $s) {
    $sc = $scores[(int) $s['user_id']] ?? [];
    $midG = termGrade($mid, $sc, $s['attendance']);
    $finG = termGrade($fin, $sc, $s['attendance']);
    $overall = ($midG !== null && $finG !== null) ? ($midG + $finG) / 2 : null;
    $remark = 'Pending';
    if ($overall !== null) $remark = $overall >= PASSING ? 'Passed' : 'Failed';
    if ($remark === 'Passed') $passed++;
    if ($remark === 'Failed') $failed++;
    $name = trim(($s['last_name'] ?? '') . ', ' . ($s['first_name'] ?? ''), ', ');
    $rows[] = ['s' => $s, 'name' => $name !== '' ? $name : $s['email'], 'sc' => $sc,
               'mid' => $midG, 'overall' => $overall, 'remark' => $remark];
}
$total = count($students);
$pct = $total ? round($passed / $total * 100) : 0;
$reviewed = $passed + $failed;
$counts = ['midterm' => ['quiz' => 0, 'activity' => 0, 'exam' => 0], 'finals' => ['quiz' => 0, 'activity' => 0, 'exam' => 0]];
foreach ($activities as $a) { $counts[$a['term']][$a['category']]++; }
$scoreCount = [];
foreach ($scores as $byAct) { foreach ($byAct as $aid => $v) { $scoreCount[$aid] = ($scoreCount[$aid] ?? 0) + 1; } }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>ARGO</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="argo.css?v=2">
        <link rel="stylesheet" href="responsive.css?v=2">
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
         <div class="initial"><?= h($initials) ?></div>
            <span><?= h($instrName) ?></span>
        </div>
      </div>
      <ul class="menu">
        <li><a href="index.php"><i class="fas fa-book-open fa-outline"></i><span>My Classes</span></a></li>
        <li><a href="list.php"><i class="fas fa-file-alt fa-outline"></i><span>Student List</span></a></li>
        <li><a href="input-grades.html"><i class="fas fa-file-alt fa-book"></i><span>Input Grades</span></a></li>
        <li><a href="logout.php">Log out</a></li>
      </ul>
    </div>

    <div class="main-content">
      <!--header-->
      <div class="header-wrapper">
        <div class="header-left"><img src="images/cvsulogo.png" alt="logo" class="logo"></div>
        <div class="header-center">
           <p>Student Grade Portal</p>
           <p class="text-2">Class Performance Overview &amp; Grading Breakdown</p>
        </div>
        <div class="header-right">
          <div class="profile"><i class="far fa-user profile-icon"></i>
            <div class="profile-text">
              <h1><?= h($instrName) ?></h1>
              <p>INSTRUCTOR</p>
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

      <div class="info-card">
        <div class="info-item"><span class="info-label">SUBJECT:</span><span class="info-value"><?= h($subject['code']) ?></span></div>
        <div class="info-item"><span class="info-label">INSTRUCTOR:</span><span class="info-value"><?= h(strtoupper($instrName)) ?></span></div>
        <div class="info-item"><span class="info-label">PROGRAM:</span><span class="info-value">BSCS</span></div>
        <div class="info-item"><span class="info-label">SECTION:</span><span class="info-value">2B</span></div>
        <div class="info-item"><span class="info-label">SEMESTER:</span><span class="info-value"><?= h(strtoupper($subject['semester'])) ?></span></div>
      </div>

      <!-- inputs in the table point to this form with form="gradeForm" -->
      <form id="gradeForm" method="post" action="list.php?subject_id=<?= $subjectId ?>">
        <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
      </form>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th rowspan="2" class="align-left">STUDENT ID</th>
              <th rowspan="2" class="align-left">STUDENT NAME</th>
              <th rowspan="2">ATTENDANCE</th>
              <th colspan="<?= count($mid) + 1 ?>" class="group-header">MIDTERMS</th>
              <th colspan="<?= count($fin) + 1 ?>" class="group-header">FINALS</th>
              <th rowspan="2" class="align-right">REMARKS</th>
            </tr>
            <tr>
              <?php foreach ($mid as $c): ?><th class="sub-header"><?= CATEGORY_LABEL[$c['category']] . ' ' . $c['number'] ?></th><?php endforeach; ?>
              <th class="sub-header border-right">INITIAL<br>GRADE</th>
              <?php foreach ($fin as $c): ?><th class="sub-header"><?= CATEGORY_LABEL[$c['category']] . ' ' . $c['number'] ?></th><?php endforeach; ?>
              <th class="sub-header">FINAL<br>GRADE</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="<?= 6 + count($mid) + count($fin) ?>" style="text-align:center;padding:24px">No students have joined this class yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($rows as $r): $s = $r['s']; $uid = (int) $s['user_id']; ?>
            <tr>
              <td><?= h($s['student_no']) ?></td>
              <td><?= h($r['name']) ?></td>
              <td><input class="score-input" form="gradeForm" type="number" min="0" max="100" step="any"
                         name="att[<?= $uid ?>]" value="<?= $s['attendance'] === null ? '' : (float) $s['attendance'] ?>"></td>
              <?php scoreCells($mid, $r['sc'], $uid); ?>
              <td><?= fmt($r['mid']) ?></td>
              <?php scoreCells($fin, $r['sc'], $uid); ?>
              <td><?= fmt($r['overall']) ?></td>
              <td class="remark-<?= strtolower($r['remark']) ?>"><?= $r['remark'] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <aside class="subsum-panel">
        <div class="subsum-header">
          <i class="far fa-file-lines subsum-header-icon"></i>
          <h2 class="subsum-title">STUDENT STATUS SUMMARY</h2>
        </div>
        <ul class="subsum-stats">
          <li class="subsum-stat"><span class="subsum-stat-label">Total Student:</span><span class="subsum-stat-value"><?= $total ?></span></li>
          <li class="subsum-stat"><span class="subsum-stat-label">Total Student Passed:</span><span class="subsum-stat-value"><?= $passed ?></span></li>
          <li class="subsum-stat"><span class="subsum-stat-label">Total Student Failed:</span><span class="subsum-stat-value"><?= $failed ?></span></li>
          <li class="subsum-stat"><span class="subsum-stat-label">Reviewed</span><span class="subsum-stat-value"><?= $reviewed ?></span></li>
          <li class="subsum-stat"><span class="subsum-stat-label">Student Percentage Passed:</span><span class="subsum-stat-value"><?= $pct ?>%</span></li>
        </ul>

        <div class="subsum-actions">
          <a href="input-grades.html" style="text-decoration: none;">
            <button type="button" class="subsum-btn subsum-btn-approve"><i class="fas fa-pen"></i> INPUT GRADES</button>
          </a>
          <button type="button" id="btnAdd" class="subsum-btn subsum-btn-approve"><i class="fas fa-plus"></i> ADD ACTIVITIES</button>
          <button type="button" id="btnRemove" class="subsum-btn subsum-btn-return"><i class="far fa-remove"></i> REMOVE ACTIVITIES</button>
          <a href="" style="text-decoration: none;">
            <button type="button" class="subsum-btn subsum-btn-back">&larr; Submit</button>
          </a>
        </div>

        <div class="subsum-note">
          <h3 class="subsum-note-title">Summited Records</h3>
          <p class="subsum-note-text">Summited entries are checked and wil approve by Coordinator before releasing to student portals automatically.</p>
        </div>
      </aside>
    </div>

    <!-- add activities -->
    <dialog id="addDialog" class="argo-dialog">
      <form method="post" action="list.php?subject_id=<?= $subjectId ?>" id="addForm">
        <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
        <input type="hidden" name="action" value="add_activity">
        <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
        <div class="dlg-head">
          <div class="dlg-icon"><i class="fas fa-plus"></i></div>
          <div><h3>Add activities</h3><p><?= h($subject['code']) ?> &middot; new columns in the grade table</p></div>
          <button type="button" class="dlg-x" data-close aria-label="Close">&times;</button>
        </div>
        <div class="dlg-body">
          <div class="dlg-field">
            <span class="dlg-label">Term</span>
            <div class="pills">
              <label class="pill"><input type="radio" name="term" value="midterm" checked><span>Midterms</span></label>
              <label class="pill"><input type="radio" name="term" value="finals"><span>Finals</span></label>
            </div>
          </div>
          <div class="dlg-field">
            <span class="dlg-label">Type</span>
            <div class="pills">
              <label class="pill"><input type="radio" name="category" value="quiz"><span>Quiz</span></label>
              <label class="pill"><input type="radio" name="category" value="activity" checked><span>Activity</span></label>
              <label class="pill"><input type="radio" name="category" value="exam"><span>Exam</span></label>
            </div>
          </div>
          <div class="row2">
            <div class="dlg-field">
              <label class="dlg-label" for="addCount">How many</label>
              <div class="stepper">
                <button type="button" data-step="-1" aria-label="Fewer">&minus;</button>
                <input class="num" id="addCount" type="number" name="count" min="1" max="20" value="1" required>
                <button type="button" data-step="1" aria-label="More">+</button>
              </div>
            </div>
            <div class="dlg-field">
              <label class="dlg-label" for="addMax">Total points each</label>
              <input class="num" id="addMax" type="number" name="max_score" min="1" step="any" value="100" required>
            </div>
          </div>
          <div class="preview" id="addPreview"></div>
        </div>
        <div class="dlg-foot">
          <button type="button" class="btn" data-close>Cancel</button>
          <button type="submit" class="btn btn-primary">Add</button>
        </div>
      </form>
    </dialog>

    <!-- remove activity -->
    <dialog id="removeDialog" class="argo-dialog">
      <form method="post" action="list.php?subject_id=<?= $subjectId ?>" onsubmit="return confirm('Remove this item and all scores entered for it?')">
        <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
        <input type="hidden" name="action" value="remove_activity">
        <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
        <div class="dlg-head" style="background:linear-gradient(135deg,#7a1f16,#c0392b)">
          <div class="dlg-icon"><i class="fas fa-trash"></i></div>
          <div><h3>Remove activity</h3><p>Pick the item to delete</p></div>
          <button type="button" class="dlg-x" data-close aria-label="Close">&times;</button>
        </div>
        <div class="dlg-body">
          <?php $first = true; foreach (['midterm' => 'Midterms', 'finals' => 'Finals'] as $t => $tl):
                  $cols = termColumns($activities, $t); if (!$cols) continue; ?>
            <p class="group-title"><?= $tl ?></p>
            <?php foreach ($cols as $c): $n = $scoreCount[$c['id']] ?? 0; ?>
              <label class="item">
                <input type="radio" name="activity_id" value="<?= $c['id'] ?>" <?= $first ? 'checked' : '' ?>>
                <span><?= ucfirst(strtolower(CATEGORY_LABEL[$c['category']])) . ' ' . $c['number'] ?></span>
                <small><?= $n ?> score<?= $n === 1 ? '' : 's' ?> &middot; <?= rtrim(rtrim(number_format($c['max'], 2, '.', ''), '0'), '.') ?> pts</small>
              </label>
            <?php $first = false; endforeach; ?>
          <?php endforeach; ?>
          <div class="warn">Scores already entered for the item are deleted with it. Items after it are renumbered automatically.</div>
        </div>
        <div class="dlg-foot">
          <button type="button" class="btn" data-close>Cancel</button>
          <button type="submit" class="btn btn-danger">Remove</button>
        </div>
      </form>
    </dialog>

<script>
(function () {
  var add = document.getElementById('addDialog'), rm = document.getElementById('removeDialog');
  var counts = <?= json_encode($counts) ?>;
  var labels = { quiz: 'Quiz', activity: 'Activity', exam: 'Exam' };
  var termNames = { midterm: 'Midterms', finals: 'Finals' };
  var f = document.getElementById('addForm'), cnt = document.getElementById('addCount');

  function preview() {
    var n = Math.max(1, Math.min(20, parseInt(cnt.value, 10) || 1));
    var term = f.term.value, cat = f.category.value, start = counts[term][cat];
    var names = [];
    for (var i = 1; i <= n; i++) names.push(labels[cat] + ' ' + (start + i));
    var txt = n > 3 ? names[0] + ', ' + names[1] + ' … ' + names[n - 1] : names.join(', ');
    document.getElementById('addPreview').textContent = 'Will add to ' + termNames[term] + ': ' + txt;
  }
  f.addEventListener('change', preview);
  cnt.addEventListener('input', preview);
  f.querySelectorAll('[data-step]').forEach(function (b) {
    b.onclick = function () {
      cnt.value = Math.max(1, Math.min(20, (parseInt(cnt.value, 10) || 1) + parseInt(b.getAttribute('data-step'), 10)));
      preview();
    };
  });
  preview();

  document.getElementById('btnAdd').onclick = function () { add.showModal(); };
  document.getElementById('btnRemove').onclick = function () {
    if (!rm.querySelector('input[name="activity_id"]')) { alert('There are no activities to remove.'); return; }
    rm.showModal();
  };
  document.querySelectorAll('[data-close]').forEach(function (b) { b.onclick = function () { b.closest('dialog').close(); }; });
  [add, rm].forEach(function (d) { d.addEventListener('click', function (e) { if (e.target === d) d.close(); }); });

  // saving: any edited score/attendance cell submits the whole table
  document.querySelectorAll('.score-input').forEach(function (i) {
    i.addEventListener('change', function () {
      if (i.hasAttribute('data-lock') && i.value !== '' &&
          !confirm('Save this score? Once saved it cannot be changed.')) { i.value = ''; return; }
      document.getElementById('gradeForm').submit();
    });
  });
  <?php if ($flash !== ''): ?>alert(<?= json_encode($flash) ?>);<?php endif; ?>
})();
</script>
    </body>
</html>
