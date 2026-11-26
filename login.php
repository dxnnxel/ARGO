<?php

session_start();

require_once "db.php";
require_once "auth_functions.php";

$error = "";


/* LOGGED IN */

if (isset($_SESSION["user_id"])) {

    $userId =
        (int) $_SESSION["user_id"];


    $stmt =
        $conn->prepare("
            SELECT
                role,
                account_status,
                email_verified
            FROM users
            WHERE id = ?
            LIMIT 1
        ");


    if ($stmt) {

        $stmt->bind_param(
            "i",
            $userId
        );


        $stmt->execute();


        $user =
            $stmt
                ->get_result()
                ->fetch_assoc();


        $stmt->close();


        if (
            $user &&
            $user["account_status"] === "active" &&
            (int) $user["email_verified"] === 1
        ) {

            if (
                $user["role"] ===
                "administrator"
            ) {

                header(
                    "Location: admin_dashboard.php"
                );

            } else {

                header(
                    "Location: student.php"
                );
            }


            exit;
        }
    }


    unset(
        $_SESSION["user_id"],
        $_SESSION["user_role"],
        $_SESSION["role"],
        $_SESSION["user_email"]
    );
}

    
/* LOGIN */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {

    $email =
        strtolower(
            trim(
                $_POST["email"]
                ?? ""
            )
        );


    $password =
        $_POST["password"]
        ?? "";
    $loginAs = strtolower(trim($_POST["login_as"] ?? "student"));

    $allowedRoles = ["student", "instructor", "coordinator"];
    if (!in_array($loginAs, $allowedRoles, true)) {
        $loginAs = "student";
    }
   

    if (
        $email === "" ||
        $password === ""
    ) {

        $error =
            "Please enter your email and password.";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            "Please enter a valid email address.";

    } else {

        $stmt =
            $conn->prepare("
                SELECT
                    id,
                    email,
                    password_hash,
                    role,
                    account_status,
                    email_verified
                FROM users
                WHERE LOWER(email) = ?
                LIMIT 1
            ");

        if (!$stmt) {

            $error =
                "Unable to process your login right now.";

        } else {

            $stmt->bind_param(
                "s",
                $email
            );

            $stmt->execute();

            $user =
                $stmt
                    ->get_result()
                    ->fetch_assoc();


            $stmt->close();


            if (
                !$user ||
                !password_verify(
                    $password,
                    $user["password_hash"]
                )
            ) {

                $error =
                    "Invalid email or password.";
            
            }elseif ($user["role"] !== $loginAs) {

                $error =
                      "No " . ucfirst($loginAs) . " account found for this email.";

            } elseif (
                $user["role"] === "student" &&
                !isCvsuEmail(
                    $user["email"]
                )
            ) {

                $error =
                    "Student accounts must use a valid CvSU email address.";

            } elseif (
                $user["account_status"]
                === "suspended"
            ) {

                $error =
                    "This account is currently suspended.";

            } elseif (
                $user["account_status"]
                === "archived"
            ) {

                $error =
                    "This account is currently archived.";

            } elseif (
                $user["account_status"]
                === "pending"
            ) {

                $error =
                    "This account is still pending verification.";

            } elseif (
                $user["account_status"]
                === "deactivated"
            ) {

                $_SESSION[
                    "reactivation_user_id"
                ] =
                    (int) $user["id"];


                $_SESSION[
                    "reactivation_email"
                ] =
                    $user["email"];


                header(
                    "Location: reactivate_account.php"
                );

                exit;

            } elseif (
                $user["account_status"]
                !== "active"
            ) {

                $error =
                    "This account is not currently active.";

            } elseif (
                (int) $user[
                    "email_verified"
                ] !== 1
            ) {

                $error =
                    "This account has not been verified.";

            } else {

                try {

                    $code =
                        createAuthCode(
                            $conn,
                            (int) $user["id"],
                            "login"
                        );


                    /*
                        Email sending can fail locally
                        before PHPMailer is configured.

                        We still generate the OTP
                        for local development testing.
                    */

                    sendAuthCode(
                        $user["email"],
                        $code,
                        "login"
                    );


                    $_SESSION[
                        "pending_auth_user_id"
                    ] =
                        (int) $user["id"];


                    $_SESSION[
                        "pending_auth_purpose"
                    ] =
                        "login";


                    $_SESSION[
                        "pending_auth_email"
                    ] =
                        $user["email"];


                    $_SESSION[
                        "pending_auth_role"
                    ] =
                        $user["role"];


                    /*
                        DEVELOPMENT ONLY

                        auth.php displays this OTP
                        while PHPMailer is not yet configured.
                    */

                    $_SESSION[
                        "dev_auth_code"
                    ] =
                        $code;


                    header(
                        "Location: auth.php"
                    );

                    exit;

                } catch (Throwable $exception) {

                    $error =
                        "Unable to generate your verification code.";
                }
            }
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CvSU E-Grade</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="login.css">
</head>

<body>
<div class="page">
    <!-- TOP BAR -->
    <header class="topbar">
        <div class="topbar-inner">
            <a href="login.php" class="brand">
              <img calss="logo" src="images/cvsulogo.png" alt="logo" class="logo"> 
                <span class="brand-text">
                    <span class="brand-name">CvSU E-Grade</span>
                    <span class="brand-sub">Carmona Campus</span>
                </span>
            </a>
            <div class="portal-tag">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4.5 6v5.5c0 4.6 3.1 8.2 7.5 9.5 4.4-1.3 7.5-4.9 7.5-9.5V6L12 3Z"/><path d="m8.8 12 2.4 2.4 4-4.4"/></svg>
                Official Academic Portal
            </div>
        </div>
    </header>

    <main class="main">
        <!-- LEFT -->
        <section class="hero">
            <div class="hero-inner">
                <span class="hero-pill">
                    <i></i>
                    CvSU Carmona E-Grade System
                </span>
                <h1 class="hero-title">
                    <span>Your grades.</span>
                    <span>Your progress.</span>
                    <span class="last">Your future.</span>
                </h1>
                <span class="hero-underline"></span>
                <p class="hero-text">
                    One secure space for students, instructors, and coordinators
                    to access grades, manage records, and keep academic progress moving.
                </p>
                <div class="hero-features">
                    <div class="feature">
                        <span class="feature-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
                        </span>
                        <span>
                            <strong>Accurate records</strong>
                            <small>Real-time academic information</small>
                        </span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                        </span>
                        <span>
                            <strong>Secure access</strong>
                            <small>Protected university credentials</small>
                        </span>
                    </div>
                </div>
            </div>
        </section>
        <!-- RIGHT -->
        <section class="login-side">
            <div class="login-wrap">
                <div class="login-card">
                    <div class="card-head">
                        <span class="card-head-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.6"/><path d="M5 20c.8-3.6 3.6-5.6 7-5.6s6.2 2 7 5.6"/></svg>
                        </span>

                        <div>
                            <span class="card-eyebrow">Welcome back</span>
                            <h2>Sign in to your account</h2>
                        </div>

                    </div>

                    <?php if ($error !== ""): ?>

                        <div class="alert alert-error" role="alert">
                            <span class="alert-icon">!</span>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>

                    <?php endif; ?>

                    <form method="POST" action="login.php" class="auth-form">
                        <!-- visual only: backend decides the portal from the account's role -->
                        <input type="hidden" name="login_as" id="loginAs" value="student">

                        <div class="form-group">

                            <span class="field-label">I am a</span>

                            <div class="role-tabs" role="tablist">
                                <button type="button" class="role-tab active" data-role="student" role="tab" aria-selected="true">Student</button>
                                <button type="button" class="role-tab" data-role="instructor" role="tab" aria-selected="false">Instructor</button>
                                <button type="button" class="role-tab" data-role="coordinator" role="tab" aria-selected="false">Coordinator</button>
                            </div>

                        </div>

                        <div class="form-group">
                            <label for="email">University email</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5.5" width="18" height="13" rx="2"/><path d="m4 7.5 8 6 8-6"/></svg>
                                </span> 
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="name@cvsu.edu.ph"
                                    value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                                    autocomplete="email"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                                </span>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                    required
                                >
                                <button type="button" class="password-toggle" id="passwordToggle" aria-label="Show password">
                                    <svg class="eye-on" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.8"/></svg>
                                    <svg class="eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12S6 5.5 12 5.5c1.7 0 3.2.5 4.5 1.2M21.5 12S18 18.5 12 18.5c-1.7 0-3.2-.5-4.5-1.2"/><path d="M9.9 9.9a2.8 2.8 0 0 0 4.2 3.7"/><path d="m3.5 3.5 17 17"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-options">
                            <label class="remember">
                                <input type="checkbox" name="remember" value="1">
                                <span>Remember me</span>
                            </label>

                            <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
                        </div>

                        <button type="submit" class="auth-button">
                            <span id="submitLabel">Sign in as Student</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                    </form>

                    <div class="card-help">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.6 9.5a2.5 2.5 0 1 1 3.4 2.3c-.7.3-1 .8-1 1.5"/><path d="M12 16.8h.01"/></svg>

                        <span>
                            No account yet?
                            <?php
                            $registerLinks = [
                              "student"     => "register.php",
                              "instructor"  => "register_instructor.php",
                              "coordinator" => "register_coordinator.php",
                            ];
                            ?>
                            <a href="<?= htmlspecialchars($registerLinks[$loginAs] ?? 'register.php') ?>"
                                id="registerLink"
                                data-student="register.php"
                                data-instructor="register_instructor.php"
                                data-coordinator="register_coordinator.php">Create Account</a>
                        </span>
                    </div>
                </div>

                <p class="privacy-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4.5 6v5.5c0 4.6 3.1 8.2 7.5 9.5 4.4-1.3 7.5-4.9 7.5-9.5V6L12 3Z"/><path d="m8.8 12 2.4 2.4 4-4.4"/></svg>
                    Your information is protected by CvSU data privacy policies.
                </p>
            </div>
        </section>
    </main>
    <footer class="footer">
        <div class="footer-inner">
            <span>&copy; <?= date("Y") ?> Cavite State University &ndash; Carmona Campus</span>
            <span>E-Grade System v2.0</span>
        </div>
    </footer>
</div>


<script src="script.js"></script>

</body>

</html>
