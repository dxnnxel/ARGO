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
    <link rel="stylesheet" href="responsive.css?v=2">
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
            <span>Submission Queue</span>
            </a>
       </li>
        <li>
            <a href="approval.html">
            <i class="fas fa-file-alt fa-outline"></i>  
            <span> Grade Approval</span>
            </a>
       </li> 
        <li>  
            <a href="logout.php">Log out</a>
        </li>
     
    </ul>
  </div>
        <div class="main-content">
          <!--header-->
         <div class="header-wrapper">
          <div class="header-left">
              <img src="images/cvsulogo.png" alt="logo" class="logo"> 
          </div>
          <div class="header-center">
             <p> Coordinator Dashboard</p>
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
          <main class="sg-page">
    <h1 class="sg-page-title">SUBMITTED GRADES</h1>
 
    <div class="sg-layout">
 
      <!-- Table card -->
      <section class="sg-table-card">
        <div class="sg-table-wrap">
          <table class="sg-grades-table">
            <thead>
              <tr>
                <th>SUBMISSION ID</th>
                <th>INSTRUCTOR</th>
                <th>PROGRAM</th>
                <th>DATE</th>
                <th>SECTION</th>
                <th class="sg-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>100003</td>
                <td class="sg-bold">Mr. Galoso</td>
                <td>BSCS</td>
                <td class="sg-bold">5/28/2026</td>
                <td>BSCS-2B</td>
                <td class="sg-center"><a href="approval.html" class="sg-btn-review">REVIEW</a></td>
              </tr>
              <tr>
                <td>100009</td>
                <td class="sg-bold">Mr. Flores</td>
                <td>BSCS</td>
                <td class="sg-bold">5/31/2026</td>
                <td>BSCS-1B</td>
                <td class="sg-center"><a href="approval.html" class="sg-btn-review">REVIEW</a></td>
              </tr>
              <tr>
                <td>100021</td>
                <td class="sg-bold">Ms. Veron</td>
                <td>BSCS</td>
                <td class="sg-bold">5/31/2026</td>
                <td>BSCS-2C</td>
                <td class="sg-center"><a href="approval.html" class="sg-btn-review">REVIEW</a></td>
              </tr>
              <tr class="sg-selected">
                <td><span class="sg-id-with-dot">100001</span></td>
                <td class="sg-bold">Mr. Hugo</td>
                <td>BSCS</td>
                <td>5/31/2026</td>
                <td>BSCS-2B</td>
                <td class="sg-center"><a href="approval.html" class="sg-link-view">VIEW</a></td>
              </tr>
              <tr>
                <td>100001</td>
                <td class="sg-bold">Mr. Hugo</td>
                <td>BSCS</td>
                <td class="sg-bold">5/31/2026</td>
                <td>BSCS-3B</td>
                <td class="sg-center"><a href="approval.html" class="sg-btn-review">REVIEW</a></td>
              </tr>
              <tr>
                <td>100004</td>
                <td class="sg-bold">Mr. Fowler</td>
                <td>BSCS</td>
                <td class="sg-bold">5/31/2026</td>
                <td>BSCS-3A</td>
                <td class="sg-center"><a href="approval.html" class="sg-btn-review">REVIEW</a></td>
              </tr>
              <tr>
                <td>100005</td>
                <td class="sg-bold">Mr. Right</td>
                <td>BSCS</td>
                <td class="sg-bold">5/31/2026</td>
                <td>BSCS-1B</td>
                <td class="sg-center"><a href="approval.html" class="sg-btn-review">REVIEW</a></td>
              </tr>
              <tr>
                <td>100006</td>
                <td class="sg-bold">Ms. Santos</td>
                <td>BSCS</td>
                <td class="sg-bold">5/27/2026</td>
                <td>BSCS-2A</td>
                <td class="sg-center"><a href="approval.html" class="sg-btn-review">REVIEW</a></td>
              </tr>
            </tbody>
          </table>
        </div>
 
        <div class="sg-table-footer">
          <button type="button" class="sg-btn-outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="8" y1="13" x2="16" y2="13"/>
              <line x1="8" y1="17" x2="16" y2="17"/>
            </svg>
            REVIEW SELECTED SUBMISSION
          </button>
        </div>
      </section>
    </div>
  </main>
  
 <aside class="subsum-panel">
 
    <!-- Title -->
    <div class="subsum-header">
      <i class="far fa-file-lines subsum-header-icon"></i>
      <h2 class="subsum-title">APPROVAL QUEUE STATISTICS</h2>
    </div>
 
    <!-- Stats -->
    <ul class="subsum-stats">
      <li class="subsum-stat">
        <span class="subsum-stat-label">Total Pending approval:</span>
        <span class="subsum-stat-value" id="sumTotal">18</span>
      </li>
      <li class="subsum-stat">
        <span class="subsum-stat-label">Total Approval Done:</span>
        <span class="subsum-stat-value" id="sumPassed">5</span>
      </li>
    </ul>
 
    <!-- Actions 
    <div class="subsum-actions">
      <a href="input-grades.html" style="text-decoration: none;">
      <button type="button" class="subsum-btn subsum-btn-approve">
        <i class="fas fa-pen"></i>
        INPUT GRADES
      </button>
      </a>
      <a href="" style="text-decoration: none;">
      <button type="button" class="subsum-btn subsum-btn-approve">
        <i class="fas fa-plus"></i>
        ADD ACTIVITIES
      </button>
      </a>
 
      <button type="button" class="subsum-btn subsum-btn-return">
        <i class="far fa-remove"></i>
          REMOVE ACTIVITIES
      </button>
       <a href="" style="text-decoration: none;">
      <button type="button" class="subsum-btn subsum-btn-back">  
        &larr; Submit
      </button>
      </a>
    </div>-->
    <!-- Info box -->
    <div class="subsum-note">
      <h3 class="subsum-note-title">Approved Records</h3>
      <p class="subsum-note-text">
       Approved Recods will be seen in the student account portal for transparency of their grades and perfomances.
    </div>
  </aside>
         </div>
   <script src="script.js"></script>
         </body>
    </html>