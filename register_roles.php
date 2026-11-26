<?php
session_start();

/* ---------- ROLE CONFIG ---------- */
$roles = [
    "instructor" => [
        "label"    => "Instructor",
        "title"    => "Register as a CvSU instructor",
        "subtitle" => "Create your CVSWHO instructor account using your official CvSU email.",
        "email"    => "CvSU Instructor Email",
        "notice_title" => "CvSU Instructor Verification",
        "notice"   => "Your official CvSU email will be verified before your account is activated.",
    ],
    "coordinator" => [
        "label"    => "Coordinator",
        "title"    => "Register as a CvSU coordinator",
        "subtitle" => "Create your CVSWHO coordinator account using your official CvSU email.",
        "email"    => "CvSU Coordinator Email",
        "notice_title" => "Coordinator Approval Required",
        "notice"   => "Coordinator accounts are reviewed and approved by an administrator before they are activated.",
    ],
];

$ranks = [
    "Instructor",
    "Assistant Professor",
    "Associate Professor",
    "Professor",
];

/* ---------- HELPERS ---------- */
function old(string $key): string
{
    return htmlspecialchars($_POST[$key] ?? "", ENT_QUOTES, "UTF-8");
}

/* ---------- CURRENT ROLE (whitelisted) ---------- */
$role = $_POST["role"] ?? "instructor";
if (!array_key_exists($role, $roles)) {
    $role = "instructor";
}

/* ---------- FORM HANDLING ---------- */
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name  = trim($_POST["first_name"] ?? "");
    $last_name   = trim($_POST["last_name"] ?? "");
    $middle_name = trim($_POST["middle_name"] ?? "");
    $email       = strtolower(trim($_POST["email"] ?? ""));
    $password    = $_POST["password"] ?? "";
    $confirm     = $_POST["confirm_password"] ?? "";
    $employee_id = trim($_POST["employee_id"] ?? "");
    $department  = trim($_POST["department"] ?? "");

    if ($first_name === "" || $last_name === "") {
        $errors[] = "First name and last name are required.";
    }

    if (!preg_match('/^[a-z]+\.[a-z]+@cvsu\.edu\.ph$/', $email)) {
        $errors[] = "Use your official CvSU email (firstname.lastname@cvsu.edu.ph).";
    }

    if ($employee_id === "") {
        $errors[] = "Employee ID is required.";
    }

    if ($department === "") {
        $errors[] = "College / Department is required.";
    }

    /* role-specific fields */
    $rank = null;
    $designation = null;

    if ($role === "instructor") {
        $rank = $_POST["academic_rank"] ?? "";
        if (!in_array($rank, $ranks, true)) {
            $errors[] = "Please select your academic rank.";
        }
    } else {
        $designation = trim($_POST["designation"] ?? "");
        if ($designation === "") {
            $errors[] = "Please enter your coordinator designation.";
        }
    }

    /* password rules */
    if (
        strlen($password) < 8 ||
        !preg_match("/[A-Z]/", $password) ||
        !preg_match("/[a-z]/", $password) ||
        !preg_match("/[0-9]/", $password)
    ) {
        $errors[] = "Password must be at least 8 characters with uppercase, lowercase, and a number.";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (!isset($_POST["agree_terms"])) {
        $errors[] = "You must agree to the Terms and Conditions.";
    }

    /* ---------- SAVE ACCOUNT ---------- */
    if (!$errors) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Instructors: verify email first. Coordinators: admin approval first.
        $status = $role === "coordinator" ? "pending_approval" : "pending_verification";

        // TODO: connect your database and insert, e.g. with PDO:
        //
        // $stmt = $pdo->prepare(
        //   "INSERT INTO users
        //    (role, first_name, middle_name, last_name, email, password_hash,
        //     employee_id, department, academic_rank, designation, status)
        //    VALUES (?,?,?,?,?,?,?,?,?,?,?)"
        // );
        // $stmt->execute([$role, $first_name, $middle_name, $last_name, $email,
        //                 $password_hash, $employee_id, $department, $rank,
        //                 $designation, $status]);
        //
        // Check for a duplicate email first, then redirect:
        // header("Location: login.php?registered=1"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor &amp; Coordinator Register</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="register.css?v=2">
</head>
<body>
<div class="page-wrapper">
    <div class="auth-container">

        <div class="auth-brand">
            <a href="index.php" class="brand">
                <div class="brand-mark">AR</div>
                <div class="brand-text">
                    <span class="brand-name">A.R.G.O</span>
                    <span class="brand-subtitle">Student Profile Management</span>
                </div>
            </a>
        </div>

        <div class="auth-card">
            <div class="auth-header">
                <span class="section-label">CREATE YOUR ACCOUNT</span>
                <h1 id="formTitle"><?= htmlspecialchars($roles[$role]["title"]) ?></h1>
                <p id="formSubtitle"><?= htmlspecialchars($roles[$role]["subtitle"]) ?></p>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-error" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" novalidate="false">

                <!-- ROLE SWITCH -->
                <div class="role-section">
                    <span class="role-label">I am registering as</span>

                    <div class="role-switch" role="radiogroup" aria-label="Account type">
                        <?php foreach ($roles as $value => $info): ?>
                            <input
                                type="radio"
                                id="role-<?= $value ?>"
                                name="role"
                                value="<?= $value ?>"
                                <?= $role === $value ? "checked" : "" ?>
                            >
                            <label for="role-<?= $value ?>">
                                <?= htmlspecialchars($info["label"]) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            placeholder="First name"
                            value="<?= old("first_name") ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            placeholder="Last name"
                            value="<?= old("last_name") ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input
                        type="text"
                        id="middle_name"
                        name="middle_name"
                        placeholder="Middle name"
                        value="<?= old("middle_name") ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="email" id="emailLabel"><?= htmlspecialchars($roles[$role]["email"]) ?></label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="firstname.lastname@cvsu.edu.ph"
                        pattern="[A-Za-z]+\.[A-Za-z]+@cvsu\.edu\.ph"
                        value="<?= old("email") ?>"
                        required
                    >
                    <span class="input-help">
                        Example: firstname.lastname@cvsu.edu.ph
                    </span>
                </div>

                <!-- FACULTY DETAILS (both roles) -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="employee_id">Employee ID</label>
                        <input
                            type="text"
                            id="employee_id"
                            name="employee_id"
                            placeholder="Employee ID"
                            value="<?= old("employee_id") ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="department">College / Department</label>
                        <input
                            type="text"
                            id="department"
                            name="department"
                            placeholder="e.g. College of Engineering"
                            value="<?= old("department") ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Create a password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            id="passwordToggle"
                            aria-label="Show password"
                        >
                            <svg class="eye-icon eye-open" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-icon eye-closed" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 3l18 18"></path>
                                <path d="M10.6 6.2A9.8 9.8 0 0 1 12 6c6 0 9.5 6 9.5 6a16.4 16.4 0 0 1-3.1 3.8"></path>
                                <path d="M6.1 6.1C3.8 7.7 2.5 12 2.5 12s3.5 6 9.5 6a9.8 9.8 0 0 0 4.1-.9"></path>
                                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"></path>
                            </svg>
                        </button>
                    </div>

                    <span class="input-help">
                        At least 8 characters with uppercase, lowercase, and a number.
                    </span>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>

                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Confirm your password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            id="confirmPasswordToggle"
                            aria-label="Show confirm password"
                        >
                            <svg class="eye-icon eye-open" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-icon eye-closed" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 3l18 18"></path>
                                <path d="M10.6 6.2A9.8 9.8 0 0 1 12 6c6 0 9.5 6 9.5 6a16.4 16.4 0 0 1-3.1 3.8"></path>
                                <path d="M6.1 6.1C3.8 7.7 2.5 12 2.5 12s3.5 6 9.5 6a9.8 9.8 0 0 0 4.1-.9"></path>
                                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="terms-agreement">
                    <label class="terms-checkbox">
                        <input
                            type="checkbox"
                            name="agree_terms"
                            value="1"
                            <?= isset($_POST["agree_terms"]) ? "checked" : "" ?>
                            required
                        >
                        <span class="custom-checkbox"></span>
                        <span class="terms-text">
                            I have read and agree to the
                            <a href="terms.html" target="_blank" rel="noopener noreferrer">Terms and Conditions</a>
                            of CVSWHO.
                        </span>
                    </label>
                </div>

                <button type="submit" class="auth-button">
                    Create Account
                </button>
            </form>

            <div class="auth-divider">
                <span>OR</span>
            </div>

            <div class="auth-bottom">
                <p>
                    Already have an account?
                    <a href="login.php">Login</a>
                </p>

                <a href="login.php" class="back-link">← Back to home</a>
            </div>
        </div>
        <div class="auth-security">
            <span class="security-icon">✓</span>
            <div>
                <strong id="noticeTitle"><?= htmlspecialchars($roles[$role]["notice_title"]) ?></strong>
                <p id="noticeText"><?= htmlspecialchars($roles[$role]["notice"]) ?></p>
            </div>
        </div>

    </div>
</div>

<script>
    /* ---------- ROLE SWITCH ---------- */
    const roleContent = <?= json_encode($roles, JSON_HEX_TAG | JSON_HEX_AMP) ?>;

    function applyRole(role) {
        const content = roleContent[role];
        if (!content) return;

        // Show only the fields for the selected role.
        // Disabled inputs are not submitted and not validated.
        document.querySelectorAll(".role-fields").forEach(function (box) {
            const active = box.dataset.role === role;
            box.hidden = !active;

            box.querySelectorAll("input, select").forEach(function (field) {
                field.disabled = !active;
                field.required = active;
            });
        });

        document.getElementById("formTitle").textContent = content.title;
        document.getElementById("formSubtitle").textContent = content.subtitle;
        document.getElementById("emailLabel").textContent = content.email;
        document.getElementById("noticeTitle").textContent = content.notice_title;
        document.getElementById("noticeText").textContent = content.notice;
    }

    document.querySelectorAll('input[name="role"]').forEach(function (radio) {
        radio.addEventListener("change", function () {
            applyRole(radio.value);
        });
    });

    applyRole(document.querySelector('input[name="role"]:checked').value);


    /* ---------- PASSWORD TOGGLE ---------- */
    function setupPasswordToggle(inputId, buttonId) {
        const passwordInput = document.getElementById(inputId);
        const toggleButton = document.getElementById(buttonId);

        if (!passwordInput || !toggleButton) {
            return;
        }

        toggleButton.addEventListener("click", function () {
            const isHidden = passwordInput.type === "password";

            passwordInput.type = isHidden ? "text" : "password";
            toggleButton.classList.toggle("is-visible", isHidden);
            toggleButton.setAttribute(
                "aria-label",
                isHidden ? "Hide password" : "Show password"
            );
        });
    }

    setupPasswordToggle("password", "passwordToggle");
    setupPasswordToggle("confirm_password", "confirmPasswordToggle");
</script>

</body>
</html>
