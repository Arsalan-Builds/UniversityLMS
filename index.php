<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>University LMS</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Our Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">
            University LMS
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">Courses</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">About</a>
                </li>

                <li class="nav-item ms-lg-2">
                    <a class="btn btn-light text-primary" href="login.php">
                        Login
                    </a>
                </li>

            </ul>

        </div>
    </div>
</nav>


<!-- ================= HERO SECTION ================= -->

<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-md-7">

                <h1 class="display-4 fw-bold">
                    Welcome to University LMS
                </h1>

                <p class="lead">
                    A complete Learning Management System for
                    students, teachers and university administration.
                </p>

                <a href="register.php" class="btn btn-primary btn-lg">
                    Get Started
                </a>

                <a href="login.php" class="btn btn-outline-primary btn-lg ms-2">
                    Login
                </a>

            </div>

            <div class="col-md-5 text-center">

                <div class="lms-icon">
                    🎓
                </div>

            </div>

        </div>
    </div>
</section>


<!-- ================= FEATURES ================= -->

<section class="py-5">

    <div class="container">

        <div class="text-center mb-5">

            <h2 class="fw-bold">
                LMS Features
            </h2>

            <p class="text-muted">
                Everything students and teachers need in one place.
            </p>

        </div>

        <div class="row g-4">

            <!-- Student -->
            <div class="col-md-4">

                <div class="card h-100 shadow-sm">

                    <div class="card-body text-center">

                        <h3>👨‍🎓</h3>

                        <h4 class="card-title">
                            Student Portal
                        </h4>

                        <p class="card-text">
                            Access courses, lectures, assignments,
                            grades and announcements.
                        </p>

                    </div>

                </div>

            </div>


            <!-- Teacher -->
            <div class="col-md-4">

                <div class="card h-100 shadow-sm">

                    <div class="card-body text-center">

                        <h3>👨‍🏫</h3>

                        <h4 class="card-title">
                            Teacher Portal
                        </h4>

                        <p class="card-text">
                            Manage courses, materials,
                            assignments and student grades.
                        </p>

                    </div>

                </div>

            </div>


            <!-- Admin -->
            <div class="col-md-4">

                <div class="card h-100 shadow-sm">

                    <div class="card-body text-center">

                        <h3>👨‍💼</h3>

                        <h4 class="card-title">
                            Admin Panel
                        </h4>

                        <p class="card-text">
                            Manage students, teachers,
                            courses and the entire LMS.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- ================= FOOTER ================= -->

<footer class="bg-dark text-white text-center py-4">

    <p class="mb-0">
        © 2026 University LMS. All Rights Reserved.
    </p>

</footer>


<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>