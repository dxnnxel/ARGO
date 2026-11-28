       <?php
session_start();
if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["user_role"] ?? "", ["coordinator", "administrator"], true)) {
    header("Location: login.php");
    exit;
}
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
          <link rel="stylesheet" href="argo.css?v=2">
          <link rel="stylesheet" href="modal.css">
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
                <div class="initial">JO</div> 
                <span>Joe Marlou Opella</span>
               </div>
            </div>
            <ul class="menu">
             <li>
                <a href="coordinator.php">
                <i class="fas fa-book-open fa-outline"></i> 
                <span> Submission Queue</span>
                </a>
             </li>
             <li>
                <a href="approval.html">
                <i class="fas fa-file-alt fa-outline"></i>  
                <span>Grade Approval</span>
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
                  <h1>Joe Marlou Opella</h1>
                   <p>COORDINATOR</p>
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
         <div class="info-item">
            <span class="info-label">INSTRUCTOR:</span>
            <span class="info-value">MR. HUGO</span>
         </div>
         <div class="info-item">
            <span class="info-label">PROGRAM:</span>
            <span class="info-value">BSCS</span>
         </div>
         <div class="info-item">
            <span class="info-label">SECTION:</span>
            <span class="info-value">2B</span>
         </div>
         <div class="info-item">
            <span class="info-label">SEMESTER:</span>
            <span class="info-value">2ND SEMESTER</span>
         </div>
         <div class="info-item">
            <span class="info-label">DATE SUBMITTED:</span>
            <span class="info-value">MAY 31, 2026</span>
         </div>
        </div>
      <aside class="subsum-panel">
 
    <!-- Title -->
    <div class="subsum-header">
      <i class="far fa-file-lines subsum-header-icon"></i>
      <h2 class="subsum-title">Submission Summary</h2>
    </div>
 
    <!-- Stats -->
    <ul class="subsum-stats">
      <li class="subsum-stat">
        <span class="subsum-stat-label">Total Students</span>
        <span class="subsum-stat-value">38</span>
      </li>
      <li class="subsum-stat">
        <span class="subsum-stat-label">Average Grade</span>
        <span class="subsum-stat-value">1.75</span>
      </li>
      <li class="subsum-stat">
        <span class="subsum-stat-label">Pending Reviews</span>
        <span class="subsum-stat-value">32</span>
      </li>
      <li class="subsum-stat">
        <span class="subsum-stat-label">Reviewed</span>
        <span class="subsum-stat-value">5</span>
      </li>
      <li class="subsum-stat">
        <span class="subsum-stat-label">Returned</span>
        <span class="subsum-stat-value">1</span>
      </li>
    </ul>
 
    <!-- Actions -->
    <div class="subsum-actions">
      <button type="button" class="subsum-btn subsum-btn-approve">
        <i class="fas fa-check"></i>
        Approve Grades
      </button>
 
      <button type="button" class="subsum-btn subsum-btn-return">
        <i class="far fa-clone"></i>
        Return to Instructor
      </button>
    <a href="coordinator.php" style="text-decoration: none;">
      <button type="button" class="subsum-btn subsum-btn-back">
             &larr; Back to Queue
      </button>
       </a>
    </div>
 
    <!-- Info box -->
    <div class="subsum-note">
      <h3 class="subsum-note-title">Finalized Records</h3>
      <p class="subsum-note-text">
        Approved entries are locked and released to student portals automatically.
      </p>
    </div>
  </aside>
<main class="revpage-main">
     <div class="gradetbl-wrap">
   <!-- 1) TOOLBAR: place between the info card and the table -->
  <div class="table-toolbar">
    <div class="table-toolbar__left">
      <div class="search-bar" role="search">
        <svg class="search-bar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" aria-hidden="true">
          <circle cx="11" cy="11" r="7"></circle>
          <line x1="16.5" y1="16.5" x2="21" y2="21"></line>
        </svg>
        <input id="studentSearch" class="search-bar__input" type="text"
               placeholder="Search by student ID or name" aria-label="Search students" autocomplete="off">
        <button id="searchClear" class="search-bar__clear" type="button" aria-label="Clear search">&times;</button>
      </div>
      <span id="resultCount" class="result-count" aria-live="polite"></span>
    </div>
 
    <div class="table-toolbar__right">
      <select id="statusFilter" class="status-filter" aria-label="Filter by review status">
        <option value="all">All statuses</option>
        <option value="pending">Pending</option>
        <option value="reviewed">Reviewed</option>
        <option value="returned">Returned</option>
      </select>
    </div>
  </div>
 
  <!-- 2) TABLE: add the Action column header and a button cell in every row.
          Replace this sample with your existing table; keep the data-status attribute. -->
  <div class="table-scroll">
  <table id="gradeTable">
    <thead>
      <tr>
        <th>Student ID</th>
        <th>Student Name</th>
        <th>Final Grade</th>
        <th>Instructor Remarks</th>
        <th>Review Status</th>
        <th class="col-action">Action</th>   <!-- new column -->
      </tr>
    </thead>
    <tbody>
      <tr data-status="pending">
        <td>240100111</td><td>Turgo, Abegail</td><td>1.50</td><td>Very Satisfactory</td>
        <td><span class="status status-pending">Pending</span></td>
        <td class="col-action"><button class="btn-review" type="button">Review</button></td>
      </tr>
      <tr data-status="reviewed">
        <td>240100114</td><td>Gavila, Zacharia</td><td>1.50</td><td>Very Satisfactory</td>
        <td><span class="status status-reviewed">Reviewed</span></td>
        <td class="col-action"><button class="btn-review btn-review--secondary" type="button">View</button></td>
      </tr>
      <tr data-status="returned">
        <td>240188118</td><td>Galoso, Rencel</td><td>1.75</td><td>Outstanding</td>
        <td><span class="status status-returned">Returned</span></td>
        <td class="col-action"><button class="btn-review btn-review--secondary" type="button">View</button></td>
      </tr>
      <tr data-status="pending">
        <td>240100112</td><td>Flores, Grant</td><td>1.50</td><td>Very Satisfactory</td>
        <td><span class="status status-pending">Pending</span></td>
        <td class="col-action"><button class="btn-review" type="button">Review</button></td>
      </tr>
      <tr data-status="pending">
        <td>240100113</td><td>Fuentebella, Aldrin</td><td>1.50</td><td>Very Satisfactory</td>
        <td><span class="status status-pending">Pending</span></td>
        <td class="col-action"><button class="btn-review" type="button">Review</button></td>
      </tr>
      <tr data-status="pending">
        <td>240100114</td><td>Fuentebella, Aldritch</td><td>1.50</td><td>Very Satisfactory</td>
        <td><span class="status status-pending">Pending</span></td>
        <td class="col-action"><button class="btn-review" type="button">Review</button></td>
      </tr>
       <tr data-status="pending">
        <td>240101438</td><td>Kabagani, Sallie</td><td>1.50</td><td>Very Satisfactory</td>
        <td><span class="status status-pending">Pending</span></td>
        <td class="col-action"><button class="btn-review" type="button">Review</button></td>
      </tr>
      <tr data-status="pending">
        <td>240100116</td><td>Santos, Rome  </td><td>1.50</td><td>Very Satisfactory</td>
        <td><span class="status status-pending">Pending</span></td>
        <td class="col-action"><button class="btn-review" type="button">Review</button></td>
      </tr>
      <tr data-status="pending">
        <td>240100118</td><td>Veron, Jheloumae</td>
        <td>1.50</td>
        <td>Very Satisfactory</td>
        <td><span class="status status-pending">Pending</span></td>
        <td class="col-action"><button class="btn-review" type="button">Review</button></td>
      </tr>
    </tbody>
  </table>
  </div>
  <div id="noResults" class="no-results">No students match your search.</div>
  </div>
  <div class="gm-overlay" id="gmOverlay">
  <div class="gm-modal" role="dialog" aria-modal="true" aria-labelledby="gmTitle">
    <div class="gm-header">
      <div>
        <h2 id="gmTitle">Grade Report Breakdown</h2>
        <div class="gm-course">DCIT 21 – Introduction to Computing</div>
        <div class="gm-student" id="gmStudent"></div>
      </div>
      <button class="gm-close" id="gmClose" aria-label="Close">&times;</button>
    </div>
 
    <div class="gm-tabs">
      <button class="gm-tab gm-active" data-term="midterm">Midterm</button>
      <button class="gm-tab" data-term="finals">Finals</button>
    </div>
 
    <div class="gm-body">
      <table class="gm-table">
        <thead>
          <tr>
            <th>Activity</th>
            <th>No.</th>
            <th>Score</th>
            <th>Total points</th>
            <th>Percentage</th>
          </tr>
        </thead>
        <tbody id="gmBody"></tbody>
      </table>
    </div>
 
    <div class="gm-footer">
      <div class="gm-overall">
        <span class="gm-label">OVERALL GRADE</span>
        <span class="gm-grade" id="gmGrade"></span>
        <span class="gm-remark" id="gmRemark"></span>
      </div>
      <div class="gm-actions" id="gmActions">
        <button class="gm-return" id="gmReturn">RETURN</button>
        <button class="gm-approve" id="gmApprove">APPROVE</button>
      </div>
    </div>
  </div>
</div>
    </main>
  </div>
   <script src="script.js"></script>
         </body>
    </html>