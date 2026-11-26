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
         <div class="main-page-student">
            <h1>Subjects</h1>
            <p>2nd Semester ● A.Y. 2025-2026</p>
         </div>   
         <div class="greet-card-container">
            <div class="date">
               <p>Tuesday, October 13</p>
            </div>
            <div class="greet-card">
               <h1 class="greeting">Hello,</h1>
               <div class="greet">
                  <span class="user"><?= htmlspecialchars($name) ?></span>
                  <div class="message">
                  <span class="motiv">Set High Goals, Get High Grades</span>
                  <span class="motiv">Aim High, Achieve Higher</span>
                  </div>
                  <div class="num-sub">
                     <span>4 Subjects</span>
                  </div>
                  <div class="num-unit">
                     <span>12 Units</span>
                  </div>
                  <div class="gwa">
                     <span>GWA 1.50</span>
                  </div>
               </div>
            </div>
         </div>
         <div class="sub-grid">
            <div class="subject-content">
               <div class="sub-top teal">
                  <div class="sub-l">
                     <h1>DCIT 21</h1>
                     <p>Introduction to Computing</p>
                  </div>
                  <div class="sub-r">
                     <p>3 units</p>
                  </div>
               </div>
               <div class="sub-bottom">
                  <div class="sub-l">
                     <p>MWF 7:30-8:30AM</p>
                     <a href="grades.php">
                     <button class="sub-button">
                        VIEW MORE
                     </button>
                     </a>
                  </div>
                  <div class="sub-r">
                     <p>1.50</p>
                  </div>
               </div>
            </div>
            <div class="subject-content">
               <div class="sub-top olive">
                  <div class="sub-l">
                     <h1>DCIT 21</h1>
                     <p>Introduction to Computing</p>
                  </div>
                  <div class="sub-r">
                     <p>3 units</p>
                  </div>
               </div>
               <div class="sub-bottom">
                  <div class="sub-l">
                     <p>MWF 7:30-8:30AM</p>
                     <a href="grades.php">
                     <button class="sub-button">
                        VIEW MORE
                     </button>
                     </a>
                  </div>
                  <div class="sub-r">
                     <p>1.50</p>
                  </div>
               </div>
            </div>
            <div class="subject-content">
               <div class="sub-top green" >
                  <div class="sub-l">
                     <h1>DCIT 21</h1>
                     <p>Introduction to Computing</p>
                  </div>
                  <div class="sub-r">
                     <p>3 units</p>
                  </div>
               </div>
               <div class="sub-bottom">
                  <div class="sub-l">
                     <p>MWF 7:30-8:30AM</p>
                     <a href="grades.php">
                     <button class="sub-button">
                        VIEW MORE
                     </button>
                     </a>
                  </div>
                  <div class="sub-r">
                     <p>1.50</p>
                  </div>
               </div>
            </div>
            <div class="subject-content">
               <div class="sub-top teal">
                  <div class="sub-l">
                     <h1>DCIT 21</h1>
                     <p>Introduction to Computing</p>
                  </div>
                  <div class="sub-r">
                     <p>3 units</p>
                  </div>
               </div>
               <div class="sub-bottom">
                  <div class="sub-l">
                     <p>MWF 7:30-8:30AM</p>
                     <a href="grades.php">
                     <button class="sub-button">
                        VIEW MORE
                     </button>
                     </a>
                  </div>
                  <div class="sub-r">
                     <p>1.50</p>
                  </div>
               </div>
            </div>   
         </div>
      <div class="gr-side-column">
             <!-- ===== COPY FROM HERE: paste directly under the GWA ring card ===== -->
    <section class="gsc-grid" aria-label="Grade summary">
 
    <!-- Enrolled Subjects -->
    <div class="gsc-card gsc-card--green">
      <div class="gsc-top">
        <span class="gsc-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2z"/><path d="M22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7z"/></svg>
        </span>
        <span class="gsc-corner">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>
        </span>
      </div>
      <p class="gsc-value">8</p>
      <h3 class="gsc-label">Enrolled Subjects</h3>
      <p class="gsc-desc">Subjects for this semester</p>
   </div>
 
    <!-- GWA / GPA -->
    <div class="gsc-card gsc-card--indigo">
      <div class="gsc-top">
        <span class="gsc-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 20v-6"/><path d="M12 20V6"/><path d="M17 20v-10"/></svg>
        </span>
        <span class="gsc-corner">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 17 6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg>
        </span>
      </div>
      <p class="gsc-value">1.25</p>
      <h3 class="gsc-label">GWA / GPA</h3>
      <p class="gsc-desc">Current general average</p>
   </div>
 
    <!-- Grades Released -->
    <div class="gsc-card gsc-card--orange">
      <div class="gsc-top">
        <span class="gsc-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1"/><path d="m9 13 2 2 4-4"/></svg>
        </span>
        <span class="gsc-corner">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
        </span>
      </div>
      <p class="gsc-value">6/8</p>
      <h3 class="gsc-label">Grades Released</h3>
      <p class="gsc-desc">Subjects with posted grades</p>
   </div>
 
    <!-- Pending Grades -->
    <div class="gsc-card gsc-card--purple">
      <div class="gsc-top">
        <span class="gsc-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4"/><path d="M16 3v4"/><path d="M3 10h18"/></svg>
        </span>
        <span class="gsc-corner">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        </span>
      </div>
      <p class="gsc-value">2</p>
      <h3 class="gsc-label">Pending Grades</h3>
      <p class="gsc-desc">Subjects awaiting submission</p>
   </div>
  </section> 
         <div class="avg-status">
            <p>Passing Rate</p>
            <div class="ring" data-percent="90">
               <svg viewBox="0 0 120 120">
                <circle class="track"    cx="60" cy="60" r="50"/>
                <circle class="progress" cx="60" cy="60" r="50"/>
               </svg>
               <div class="label">
                  <span class="avg">90%</span>
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