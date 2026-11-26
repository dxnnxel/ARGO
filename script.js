/* ARGO shared grade logic - used by input-grades.html and list.html */
window.ARGO = (function () {
  var KEY = 'argo_grades';

  var FIELDS = ['attendance', 'midquiz', 'midactivities', 'midexam', 'fquiz', 'factivities', 'fexam'];

  // Highest score allowed per field (attendance is out of 100, everything else out of 10)
  var MAX = {
    attendance: 100,
    midquiz: 10, midactivities: 10, midexam: 10,
    fquiz: 10, factivities: 10, fexam: 10
  };

  // Remarks use the final grade: 3.00 or better = Passed
  var PASSING_GRADE = 3.00;

  // ---- Grade scale (shared by midterm and final) ----
  function toGradePoint(pct) {
    if (pct >= 97) return 1.00;
    if (pct >= 95) return 1.25;
    if (pct >= 92) return 1.50;
    if (pct >= 89) return 1.75;
    if (pct >= 85) return 2.00;
    if (pct >= 82) return 2.25;
    if (pct >= 77) return 2.50;
    if (pct >= 75) return 2.75;
    if (pct >= 70) return 3.00;
    return 5.00;
  }

  function num(v) {
    if (v === '' || v === null || v === undefined) return null;
    var n = parseFloat(v);
    return isNaN(n) ? null : n;
  }

  // Computes one term (prefix = 'mid' or 'f') and returns the grade point.
  // Returns null if any score for that term is still blank.
  function computeTerm(rec, prefix) {
    var att = num(rec.attendance);
    var q   = num(rec[prefix + 'quiz']);
    var a   = num(rec[prefix + 'activities']);
    var e   = num(rec[prefix + 'exam']);
    if (att === null || q === null || a === null || e === null) return null;

    var attendance = (att / 100) * 20;
    var quiz       = (q / 10) * 25;
    var activities = (a / 10) * 30;
    var exam       = (e / 10) * 25;
    return toGradePoint(attendance + quiz + activities + exam);
  }

  function compute(rec) {
    rec = rec || {};
    var mid = computeTerm(rec, 'mid');
    var fin = computeTerm(rec, 'f');
    var remarks = fin === null ? 'Pending' : (fin <= PASSING_GRADE ? 'Passed' : 'Failed');
    return { midGrade: mid, fGrade: fin, remarks: remarks };
  }

  function loadAll() {
    try { return JSON.parse(localStorage.getItem(KEY)) || {}; }
    catch (e) { return {}; }
  }

  function saveAll(data) {
    try { localStorage.setItem(KEY, JSON.stringify(data)); return true; }
    catch (e) { return false; }
  }

  return {
    FIELDS: FIELDS,
    MAX: MAX,
    PASSING_GRADE: PASSING_GRADE,
    toGradePoint: toGradePoint,
    compute: compute,
    loadAll: loadAll,
    saveAll: saveAll
  };
})();
 // Tab switching
  document.querySelectorAll('.gr-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.gr-tab').forEach(t => t.classList.remove('gr-tab--active'));
      tab.classList.add('gr-tab--active');
    });
  });
 
  // Update percentage when score changes
  document.querySelectorAll('.gr-row').forEach(row => {
    const select = row.querySelector('.gr-select');
    const total = parseInt(row.querySelector('.gr-total').textContent, 10);
    const percent = row.querySelector('.gr-percent');
    select.addEventListener('change', () => {
      percent.textContent = Math.round((parseInt(select.value, 10) / total) * 100) + '%';
    });
  });
  
    //ring percentages
  // One listener handles typing in ANY row (no onclick/oninput needed in the HTML)
document.addEventListener('input', e => {
    const row = e.target.closest('tr[data-student]');
    if (row && e.target.dataset.field) updateRow(row);
});
  const ring = document.querySelector('.ring');
if  (ring){
  const bar = ring.querySelector('.progress');
  const circumference = 2 * Math.PI * 50;          // ≈ 314
  const percent = ring.dataset.percent;
  // animate to the target on load
  requestAnimationFrame(() => {
    bar.style.strokeDashoffset = circumference * (1 - percent / 100);
})
};
//search bar 
function initStudentFilters() {
  const search = document.getElementById('studentSearch');
  const clearBtn = document.getElementById('searchClear');
  const statusFilter = document.getElementById('statusFilter');
  const rows = document.querySelectorAll('#gradeTable tbody tr');
  const count = document.getElementById('resultCount');
  const noResults = document.getElementById('noResults');

  // Not on this page, so skip the whole feature
  if (!search || !clearBtn || !statusFilter || !count || !noResults) return;

  function applyFilters() {
    const q = search.value.trim().toLowerCase();
    const status = statusFilter.value;
    let shown = 0;

    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      const match = (!q || text.includes(q)) &&
                    (status === 'all' || row.dataset.status === status);
      row.style.display = match ? '' : 'none';
      if (match) shown++;
    });

    count.textContent = `Showing ${shown} of ${rows.length} students`;
    noResults.style.display = shown === 0 ? 'block' : 'none';
  }

  search.addEventListener('input', applyFilters);
  statusFilter.addEventListener('change', applyFilters);
  clearBtn.addEventListener('click', () => {
    search.value = '';
    applyFilters();
    search.focus();
  });

  // Review / View button: replace with your own navigation
  document.querySelectorAll('.btn-review').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.closest('tr').children[0].textContent;
      console.log('Open review for student', id);
    });
  });

  applyFilters();
}
initStudentFilters();

/*modal*/
(function () {
  const overlay = document.getElementById("gmOverlay");
  if (!overlay) return; 
  const bodyEl  = document.getElementById("gmBody");
  const approve = document.getElementById("gmApprove");
  const returnBtn = document.getElementById("gmReturn");
  const actions = document.getElementById("gmActions");
  let currentRow = null;
  let term = "midterm";
 
  /* ---- Breakdown data (replace with your real scores / fetch per student) ----
     Each group is one section. Items are [score, total]; numbering is automatic.
     Midterm / Finals exam is always a single item. */
  const data = {
    midterm: [
      { title: "Activities",    type: "Activity",     items: [[10, 10], [10, 10], [9, 10]] },
      { title: "Quizzes",       type: "Quiz",         items: [[19, 20], [20, 20], [18, 20]] },
      { title: "Long Tests",    type: "Long Test",    items: [[50, 50], [49, 50], [48, 50]] },
      { title: "Practicals",    type: "Practical",    items: [[30, 30], [30, 30], [28, 30]] },
      { title: "Midterm Exam",  type: "Midterm Exam", items: [[48, 50]] }
    ],
    finals: [
      { title: "Activities",    type: "Activity",     items: [[10, 10], [9, 10], [10, 10]] },
      { title: "Quizzes",       type: "Quiz",         items: [[20, 20], [19, 20], [18, 20]] },
      { title: "Long Tests",    type: "Long Test",    items: [[50, 50], [50, 50], [47, 50]] },
      { title: "Practicals",    type: "Practical",    items: [[30, 30], [29, 30], [30, 30]] },
      { title: "Finals Exam",   type: "Finals Exam",  items: [[47, 50]] }
    ]
  };
 
  function render() {
    bodyEl.innerHTML = data[term].map(g => {
      const head = `<tr class="gm-section"><td colspan="5">${g.title}</td></tr>`;
      const rows = g.items.map(([score, total], i) => {
        const n = i + 1;
        const pct = Math.round(score / total * 100);
        const label = g.items.length > 1 ? `${g.type} ${n}` : g.type;
        return `
          <tr>
            <td>${label}</td>
            <td class="gm-no">${n}</td>
            <td><span class="gm-score">${score}</span></td>
            <td>${total}</td>
            <td class="gm-pct${pct === 100 ? " gm-perfect" : ""}">${pct}%</td>
          </tr>`;
      }).join("");
      return head + rows;
    }).join("");
  }
 
  function setTab() {
    overlay.querySelectorAll(".gm-tab").forEach(t =>
      t.classList.toggle("gm-active", t.dataset.term === term));
  }
 
  function open(row, btn) {
    const c = row.cells;
    currentRow = row;
    term = "midterm";
    document.getElementById("gmStudent").textContent =
      c[1].textContent.trim() + " (" + c[0].textContent.trim() + ")";
    document.getElementById("gmGrade").textContent  = c[2].textContent.trim();
    document.getElementById("gmRemark").textContent = c[3].textContent.trim();
    actions.hidden = btn.textContent.trim().toLowerCase() !== "review"; // View = read-only
    setTab();
    render();
    overlay.classList.add("gm-open");
  }
 
  function close() {
    overlay.classList.remove("gm-open");
    currentRow = null;
  }
 
  /* Click on any Review / View button (works for rows added later too) */
  document.addEventListener("click", e => {
    const btn = e.target.closest("td button");
    if (!btn || overlay.contains(btn)) return;
    const label = btn.textContent.trim().toLowerCase();
    if (label !== "review" && label !== "view") return;
    const row = btn.closest("tr");
    if (row) open(row, btn);
  });
 
  overlay.querySelectorAll(".gm-tab").forEach(t =>
    t.addEventListener("click", () => { term = t.dataset.term; setTab(); render(); }));
 
  document.getElementById("gmClose").addEventListener("click", close);
  overlay.addEventListener("click", e => { if (e.target === overlay) close(); });
  document.addEventListener("keydown", e => { if (e.key === "Escape") close(); });
 
  /* Approve / Return: update the row's status and turn its button into "View" */
  function finalize(status) {
    if (!currentRow) return;
    currentRow.cells[4].textContent = status;
    const btn = currentRow.cells[5].querySelector("button");
    if (btn) {
      btn.textContent = "View";
 
      // Copy the look of a View button that already exists in the table
      const ref = Array.from(document.querySelectorAll("td button")).find(b =>
        b !== btn && !overlay.contains(b) && b.textContent.trim().toLowerCase() === "view");
 
      if (ref) {
        btn.className = ref.className;
        const inline = ref.getAttribute("style");
        if (inline) btn.setAttribute("style", inline); else btn.removeAttribute("style");
      } else {
        btn.className = "gm-view-fallback";
      }
    }
    // TODO: send the approval / return to your backend here
    close();
  }
 
  approve.addEventListener("click", () => finalize("Reviewed"));
  returnBtn.addEventListener("click", () => finalize("Returned"));
})();

 /* Show / hide password */
    const passwordInput  = document.getElementById("password");
    const passwordToggle = document.getElementById("passwordToggle");

    if (passwordInput && passwordToggle){

  
     passwordToggle.addEventListener("click", function () {

        const showing = passwordInput.type === "text";

        passwordInput.type = showing ? "password" : "text";

        passwordToggle.classList.toggle("is-showing", !showing);

        passwordToggle.setAttribute(
            "aria-label",
            showing ? "Show password" : "Hide password"
        );
      });
    }
    /* Role tabs (visual only) */
    const roleTabs    = document.querySelectorAll(".role-tab");
    const submitLabel = document.getElementById("submitLabel");
    const loginAs     = document.getElementById("loginAs");

    if(roleTabs.length && submitLabel && loginAs){


      roleTabs.forEach(function (tab) {

        tab.addEventListener("click", function () {

            roleTabs.forEach(function (t) {
                t.classList.remove("active");
                t.setAttribute("aria-selected", "false");
            });

            tab.classList.add("active");
            tab.setAttribute("aria-selected", "true");

            loginAs.value = tab.dataset.role;

            submitLabel.textContent = "Sign in as " + tab.textContent.trim();
            
            if (registerLink) {
            registerLink.href = registerLink.dataset[tab.dataset.role] || "register.php";
          }
        });
     });
    }