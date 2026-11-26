<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {   // use the key from step 3
    header('Location: login.php');
    exit;
}

$stmt = $conn->prepare('SELECT * FROM student_profiles WHERE user_id = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

$name = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
if ($name === '') $name = 'Student';
?>
    <!DOCTYPE html>
    <html>
        <head>
          <title>
            ARGO
          </title>
          <link rel="stylesheet" href="">
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <link rel="stylesheet" href="argo.css">
    <link rel="stylesheet" href="responsive.css">
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
         </head>
         <body>
            <!--sidebar-->
            <div class="sidebar">
            <!--profile-->
            <div class="brand">
                <div class="sidebar-logo">
                <img calss="logo" src="images/cvsulogo.png" alt="logo" class="logo"> 
                </div> 
                <div class="sys-title-card">
                <div class="sys-title"> A.R.G.O </div>
                <div class="sys-description">Academic Record Grades Organizer</div>
                </div> 
            </div>
            <div class="student_card">
               <div class="student_info">
                <div class="initial"><?php
                  $parts = preg_split('/\s+/', trim($name));
                  $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
                  ?>
                  <?= htmlspecialchars($initials) ?></div> 
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
          <!--header-->
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
     <div class="card-container">
    <div class="card-Consultation">
    <!-- Header -->
    <div class="gr-header">
      <div>
        <h1 class="gr-title">Grade Report Breakdown</h1>
        
      </div>
      <button class="gr-back-btn" aria-label="Back">←</button>
    </div>
 
    <!-- Tabs -->
    <div class="gr-tabs">
      <button class="gr-tab gr-tab--active" data-tab="midterm">MIDTERM</button>
      <button class="gr-tab" data-tab="finals">FINALS</button>
    </div>
 
    <!-- Table -->
    <div class="gr-table-wrap">
  <table class="gr-table">
    <thead>
      <tr>
        <th class="gr-th gr-th--left">Activity</th>
        <th class="gr-th">No.</th>
        <th class="gr-th">Score</th>
        <th class="gr-th">Total Points</th>
        <th class="gr-th">Percentage</th>
      </tr>
    </thead>
    <tbody>
 
      <!-- Category: Activities -->
      <tr class="gr-cat"><td class="gr-cat-td" colspan="5">Activities</td></tr>
      <tr class="gr-row">
        <td class="gr-td gr-td--name">Activity 1</td>
        <td class="gr-td gr-td--no">1</td>
        <td class="gr-td"><span class="gr-score">10</span></td>
        <td class="gr-td gr-total">10</td>
        <td class="gr-td gr-percent gr-percent--full">100%</td>
      </tr>
      <tr class="gr-row">
        <td class="gr-td gr-td--name">Activity 2</td>
        <td class="gr-td gr-td--no">2</td>
        <td class="gr-td"><span class="gr-score">10</span></td>
        <td class="gr-td gr-total">10</td>
        <td class="gr-td gr-percent gr-percent--full">100%</td>
      </tr>
      <tr class="gr-row">
        <td class="gr-td gr-td--name">Activity 3</td>
        <td class="gr-td gr-td--no">3</td>
        <td class="gr-td"><span class="gr-score">9</span></td>
        <td class="gr-td gr-total">10</td>
        <td class="gr-td gr-percent">90%</td>
      </tr>
 
      <!-- Category: Quizzes -->
      <tr class="gr-cat"><td class="gr-cat-td" colspan="5">Quizzes</td></tr>
      <tr class="gr-row">
        <td class="gr-td gr-td--name">Quiz 1</td>
        <td class="gr-td gr-td--no">1</td>
        <td class="gr-td"><span class="gr-score">19</span></td>
        <td class="gr-td gr-total">20</td>
        <td class="gr-td gr-percent">95%</td>
      </tr>
      <tr class="gr-row">
        <td class="gr-td gr-td--name">Quiz 2</td>
        <td class="gr-td gr-td--no">2</td>
        <td class="gr-td"><span class="gr-score">20</span></td>
        <td class="gr-td gr-total">20</td>
        <td class="gr-td gr-percent gr-percent--full">100%</td>
      </tr>
 
      <!-- Category: Long Tests -->
      <tr class="gr-cat"><td class="gr-cat-td" colspan="5">Long Tests</td></tr>
      <tr class="gr-row">
        <td class="gr-td gr-td--name">Long Test 1</td>
        <td class="gr-td gr-td--no">1</td>
        <td class="gr-td"><span class="gr-score">50</span></td>
        <td class="gr-td gr-total">50</td>
        <td class="gr-td gr-percent gr-percent--full">100%</td>
      </tr>
 
      <!-- Category: Practical -->
      <tr class="gr-cat"><td class="gr-cat-td" colspan="5">Practical</td></tr>
      <tr class="gr-row">
        <td class="gr-td gr-td--name">Practical 1<span class="gr-dot"></span></td>
        <td class="gr-td gr-td--no">1</td>
        <td class="gr-td"><span class="gr-score">1</span></td>
        <td class="gr-td gr-total">1</td>
        <td class="gr-td gr-percent gr-percent--full">100%</td>
      </tr>

        <!-- Category: Practical -->
      <tr class="gr-cat"><td class="gr-cat-td" colspan="5">Exam</td></tr>
      <tr class="gr-row">
        <td class="gr-td gr-td--name">Exam 1<span class="gr-dot"></span></td>
        <td class="gr-td gr-td--no">1</td>
        <td class="gr-td"><span class="gr-score">70</span></td>
        <td class="gr-td gr-total">100</td>
        <td class="gr-td gr-percent gr-percent--full">100%</td>
      </tr>
 
    </tbody>
  </table>
</div>  
 
    <!-- Footer -->
    <div class="gr-footer">
      <div class="gr-overall">
        <span class="gr-overall-label">Overall Grade</span>
        <span class="gr-grade-pill">1.00</span>
        <span class="gr-badge">Excellent</span>
      </div>
      <button class="gr-download-btn">↓ Download</button>
    </div>  
  </div>
</div>      
  </div>
   <script src="script.js"></script>
         </body>
    </html>