    <?php
session_start();
if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["user_role"] ?? "", ["instructor", "administrator"], true)) {
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
    </head>
    <link rel="stylesheet" href="argo.css?v=2"/>
    <link rel="stylesheet" href="responsive.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
         <div class="initial">AH</div> 
            <span>Alonel Hugo</span>
        </div>
       </div>
           <ul class="menu">
        <li>
          <a href="index.php">
           <i class="fas fa-book-open fa-outline"></i> 
            <span>My Classes</span>
            </a>
       </li>
        <li>
            <a href="list.php">
            <i class="fas fa-file-alt fa-outline"></i>  
            <span>Student List</span>
            </a>                              
       </li> 
        <li><a href="input-grades.html">
           <i class="fas fa-file-alt fa-book"></i>
           <span>Input Grades</span>
          </a>
        </li>
        <li><a href="logout.php">Log out</a></li>
      
    </ul>
  </div>
        <div class="main-content">
          <!--header-->
         <div class="header-wrapper">
          <div class="header-left">
              <img src="images/cvsulogo.png" alt="logo" class="logo"> 
          </div>
          <div class="header-center">
             <p> Instructor Dashboard</p>
          </div>
          <div class="header-right">
            <div class="profile"><i class="far fa-user profile-icon"></i>
               <div class="profile-text"> 
                <h1>Alonel Hugo</h1>
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
         
         <div class="ins-dashboard-container">
            <div class="date">
               <p>Tuesday, October 13</p>
            </div>
            <div class="dahsboard-card">
               <h1 class="ins">Instructor Dashboard,</h1>
               <div class="dahsboard">
                  <span class="your-classes">Your Classes</span>
                  <div class="message">
                  <span class="motiv">Set High Goals, Get High Grades</span>
                  <span class="motiv">Aim High, Achieve Higher</span>
                  </div>
               </div>
            </div>
             <div class="stats">
                     <div class="C">
                      <span class="stats-num">5</span>
                      <span class="stats-label">Classes</span></div>
                     <div class="A">
                      <span class="stats-num">4</span>
                      <span class="stats-label">approved</span>
                     </div>
                     <div class="R">
                      <span class="stats-num">1</span>
                      <span class="stats-label">Rejected</span>
                     </div>
              </div>
         </div>
         <div class="main-page">
          <div class="main-page-container">
          <div class="class-label-container">
          <h1>CLASSES</h1>
          </div>
          <div class="class-label">
          <span class="all-class">All 5</span>
          <span class="class-approved">Approved 4</span>
          <span class="class-rejected">Rejected 1</span>
          </div>
          <div class="classes-content">
              <div class="card">
                <div class="card-r">
                  <h1>DCIT23_BSCS2B</h1>
                  <p class="sub_des">COMPUTER PROGRAMMING 2</p>
                </div>
                 <div class="card-l">
                  <a href="list.html">
                  <button class="b1">View Student List</button>
                  </a>
                 
                </div>
              </div>
              <div class="card">
                <div class="card-r">
                  <h1>INSY50_BSCS2B</h1>
                  <p class="sub_des">FUNDAMENTALS OF INFORMATION SYSTEMS</p>
                </div>
                <div class="card-l">
                  <a href="list.html">
                  <button class="b1">View Student List</button>
                  </a>
                  
                </div>
              </div>
              <div class="card">
                <div class="card-r">
                  <h1>DCIT23_BSCS2A</h1>
                  <p class="sub_des">COMPUTER PROGRAMMING 2</p>
                </div>
                 <div class="card-l">
                  <a href="list.html">
                  <button class="b1">View Student List</button>
                  </a>
                 
                </div>
              </div>
              <div class="card">
                <div class="card-r">
                  <h1>MATH1_BSCS2B</h1>
                  <p class="sub_des">CALCULUS</p>                 
                </div>
                 <div class="card-l">
                  <a href="list.html">
                  <button class="b1">View Student List</button>
                  </a>
                  
                </div>
              </div>
              <div class="card">    
                <div class="card-r">
                  <h1>DCIT21_BSCS1A</h1>
                  <p class="sub_des">INTRODUCTION TO COMPUTING</p>
                </div>
                <div class="card-l">
                  <a href="list.html">
                  <button class="b1">View Student List</button>
                  </a>
                  
                </div>
              </div>
              </div>
              </div>
          </div>
         <div class="class-status">
                  <ul class="classes">
               <p>MY CLASSES</p>
                <li>
                  <span>DCIT23_BSCS2B</span>
                  <button>edit</button> 
                  <span class="approved">APPROVED</span>
                </li>
                <li>
                  <span>INSY50_BSCS2B</span>
                  <button>edit</button> 
                  <span class="rejected">REJECTED</span>
                </li>
                <li>
                  <span>DCIT23_BSCS2A</span>
                  <button>edit</button> 
                  <span class="approved">APPROVED</span>
                </li>
                <li>
                  <span>MATH1_BSCS2B</span>
                  <button>edit</button> 
                  <span class="approved">APPROVED</span>
                </li>
                <li>
                  <span>DCIT21_BSCS1A</span>
                  <button>edit</button>
                  <span class="approved">APPROVED</span>
                </li>
            </ul>
              </div>
        </div>
         <script src="script.js"></script>
    </body>
</html>