<?php

include "includes/db.php";

$message = "";

if (isset($_POST['register'])) {

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($full_name) || empty($email) || empty($password)) {

        $message = "Please fill all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email.";

    } else {

        // Check if email already exists
        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ?"
        );

        mysqli_stmt_bind_param($check, "s", $email);

        mysqli_stmt_execute($check);

        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {

            $message = "This email is already registered.";

        } else {

            // Hash password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert user into database
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (full_name, email, password, role)
                VALUES (?, ?, ?, 'student')"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $full_name,
                $email,
                $hashed_password
            );

            if (mysqli_stmt_execute($stmt)) {

                $message = "Registration successful! You can now login.";

            } else {

                $message = "Registration failed.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Register - University LMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body class="bg-light">

<div class="container">

    <div class="row justify-content-center mt-5">

        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-body p-4">

                    <h2 class="text-center mb-4">
                        Create Account
                    </h2>

                    <?php if ($message != ""): ?>

                        <div class="alert alert-info">
                            <?php echo $message; ?>
                        </div>

                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label">
                                Full Name
                            </label>

                            <input type="text"
                                   name="full_name"
                                   class="form-control"
                                   required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   required>

                        </div>

                        <button type="submit"
                                name="register"
                                class="btn btn-primary w-100">

                            Register

                        </button>

                    </form>

                    <p class="text-center mt-3">

                        Already have an account?

                        <a href="login.php">
                            Login
                        </a>

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>