<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "includes/db.php";

$message = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {

        $message = "Please fill all fields.";

    } else {

        // Find user by email
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, full_name, email, password, role
             FROM users
             WHERE email = ?"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {

            $user = mysqli_fetch_assoc($result);

            // Check password
            if (password_verify($password, $user['password'])) {

                // Create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect according to role
                if ($user['role'] == "student") {

                    header("Location: student/dashboard.php");
                    exit;

                } elseif ($user['role'] == "teacher") {

                    header("Location: teacher/dashboard.php");
                    exit;

                } elseif ($user['role'] == "admin") {

                    header("Location: admin/dashboard.php");
                    exit;
                }

            } else {

                $message = "Incorrect password.";

            }

        } else {

            $message = "Account not found.";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login - University LMS</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            background: #f4f8fa;

            font-family:
                "Segoe UI",
                Inter,
                Arial,
                sans-serif;

            display: flex;

            align-items: center;

            justify-content: center;

        }


        /* =========================
           LOGIN CARD
        ========================= */

        .login-card {

            width: 100%;

            max-width: 430px;

            background: white;

            border: none;

            border-radius: 14px;

            box-shadow:
                0 8px 30px rgba(0, 0, 0, 0.08);

            overflow: hidden;

        }


        /* =========================
           CARD HEADER
        ========================= */

        .login-header {

            background: #087f7b;

            color: white;

            text-align: center;

            padding: 30px 25px;

        }


        .login-icon {

            width: 65px;

            height: 65px;

            background: white;

            color: #087f7b;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 15px;

            font-size: 30px;

        }


        .login-header h2 {

            font-size: 25px;

            font-weight: 700;

            margin-bottom: 6px;

        }


        .login-header p {

            margin: 0;

            font-size: 14px;

            opacity: 0.9;

        }


        /* =========================
           CARD BODY
        ========================= */

        .login-body {

            padding: 30px;

        }


        .form-label {

            font-weight: 600;

            color: #333;

            margin-bottom: 7px;

        }


        .form-control {

            height: 48px;

            border: 1px solid #d7dfe3;

            border-radius: 8px;

            font-size: 15px;

        }


        .form-control:focus {

            border-color: #087f7b;

            box-shadow:
                0 0 0 0.2rem rgba(8, 127, 123, 0.12);

        }


        /* =========================
           LOGIN BUTTON
        ========================= */

        .login-btn {

            width: 100%;

            height: 48px;

            border: none;

            border-radius: 8px;

            background: #ff7518;

            color: white;

            font-size: 16px;

            font-weight: 600;

            transition: 0.2s ease;

        }


        .login-btn:hover {

            background: #e85f00;

            color: white;

        }


        /* =========================
           ERROR MESSAGE
        ========================= */

        .alert-danger {

            border-radius: 8px;

            font-size: 14px;

            border: none;

        }


        /* =========================
           FOOTER TEXT
        ========================= */

        .login-footer {

            text-align: center;

            margin-top: 22px;

            color: #6c757d;

            font-size: 13px;

        }


        @media (max-width: 500px) {

            .login-card {

                margin: 20px;

            }

            .login-body {

                padding: 25px;

            }

        }

    </style>

</head>


<body>


<!-- =========================
     LOGIN CARD
========================= -->

<div class="login-card">


    <!-- HEADER -->

    <div class="login-header">


        <div class="login-icon">

            <i class="bi bi-mortarboard-fill"></i>

        </div>


        <h2>
            University LMS
        </h2>


        <p>
            Learning Management System
        </p>


    </div>



    <!-- BODY -->

    <div class="login-body">


        <h4
            class="text-center mb-4"
            style="font-weight: 700; color: #333;"
        >
            Sign In
        </h4>



        <!-- ERROR MESSAGE -->

        <?php if ($message != ""): ?>

            <div class="alert alert-danger">

                <i class="bi bi-exclamation-circle me-1"></i>

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>



        <!-- LOGIN FORM -->

        <form method="POST">


            <!-- EMAIL -->

            <div class="mb-3">

                <label class="form-label">

                    Email

                </label>


                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter your email"
                    required
                >

            </div>



            <!-- PASSWORD -->

            <div class="mb-4">

                <label class="form-label">

                    Password

                </label>


                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter your password"
                    required
                >

            </div>



            <!-- LOGIN BUTTON -->

            <button
                type="submit"
                name="login"
                class="login-btn"
            >

                <i class="bi bi-box-arrow-in-right me-1"></i>

                Login

            </button>


        </form>



        <!-- FOOTER -->

        <div class="login-footer">

            <i class="bi bi-shield-check me-1"></i>

            Authorized users only

        </div>


    </div>

</div>


</body>

</html>