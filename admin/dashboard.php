<?php

session_start();
include "../includes/db.php";

/* =========================
   ADMIN LOGIN CHECK
========================= */

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* =========================
   DASHBOARD COUNTS
========================= */

$student_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE role = 'student'"
);
$student_count = mysqli_fetch_assoc($student_result)['total'];

$teacher_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE role = 'teacher'"
);
$teacher_count = mysqli_fetch_assoc($teacher_result)['total'];

$department_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM departments"
);
$department_count = mysqli_fetch_assoc($department_result)['total'];

$course_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM courses"
);
$course_count = mysqli_fetch_assoc($course_result)['total'];

$announcement_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM announcements"
);
$announcement_count = mysqli_fetch_assoc($announcement_result)['total'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - University LMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

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
            font-family: "Segoe UI", Inter, Arial, sans-serif;
            background: #f4f8fa;
            color: #122033;
            font-size: 15px;
        }

        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 245px;
            height: 100vh;
            background: #087f7b;
            color: white;
            padding: 22px 15px;
            z-index: 1000;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 5px 12px 28px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            background: white;
            color: #087f7b;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
            font-weight: 800;
        }

        .brand-text {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
        }

        .brand-small {
            font-size: 12px;
            opacity: .75;
            margin-top: 3px;
        }

        .nav-menu {
            margin-top: 5px;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 14px;
            color: rgba(255,255,255,.88);
            text-decoration: none;
            padding: 13px 15px;
            margin: 5px 0;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            transition: .2s ease;
        }

        .nav-link-custom i {
            font-size: 19px;
            width: 22px;
        }

        .nav-link-custom:hover {
            background: rgba(255,255,255,.12);
            color: white;
            transform: translateX(2px);
        }

        .nav-link-custom.active {
            background: rgba(255,255,255,.18);
            color: white;
            box-shadow: inset 4px 0 0 #ff7518;
        }

        .logout-link {
            position: absolute;
            left: 15px;
            right: 15px;
            bottom: 20px;
        }

        /* =========================
           MAIN
        ========================= */

        .main-content {
            margin-left: 245px;
            min-height: 100vh;
        }

        .topbar {
            height: 75px;
            background: white;
            border-bottom: 1px solid #e3ebef;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 35px;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #d9f1ef;
            color: #087f7b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 16px;
        }

        .profile-info strong {
            display: block;
            font-size: 14px;
        }

        .profile-info span {
            display: block;
            color: #7b8995;
            font-size: 12px;
        }

        .content {
            padding: 32px 35px 45px;
        }

        /* =========================
           HEADINGS
        ========================= */

        .welcome-title {
            font-size: 30px;
            font-weight: 750;
            margin-bottom: 5px;
            color: #122033;
        }

        .welcome-text {
            color: #657585;
            font-size: 15px;
            margin-bottom: 28px;
        }

        .section-title {
            font-size: 21px;
            font-weight: 700;
            margin-bottom: 17px;
            color: #122033;
        }

        /* =========================
           WELCOME BOX
        ========================= */

        .welcome-box {
            background: #e4f4f3;
            border-left: 5px solid #087f7b;
            border-radius: 12px;
            padding: 20px 23px;
            margin-bottom: 30px;
        }

        .welcome-box h4 {
            margin: 0 0 5px;
            font-size: 20px;
            font-weight: 700;
        }

        .welcome-box p {
            margin: 0;
            color: #536875;
            font-size: 14px;
        }

        /* =========================
           STAT CARDS
        ========================= */

        .stat-card {
            background: white;
            border: 1px solid #e4ebef;
            border-radius: 14px;
            padding: 20px;
            height: 100%;
            transition: .2s ease;
            box-shadow: 0 3px 12px rgba(18,32,51,.04);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(18,32,51,.09);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 16px;
        }

        .teal-icon {
            background: #dff3f1;
            color: #087f7b;
        }

        .orange-icon {
            background: #fff0e6;
            color: #f26b21;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 750;
            color: #122033;
            margin-bottom: 2px;
        }

        .stat-label {
            color: #687887;
            font-size: 14px;
            font-weight: 600;
        }

        /* =========================
           MANAGEMENT CARDS
        ========================= */

        .management-card {
            background: white;
            border: 1px solid #e4ebef;
            border-radius: 14px;
            padding: 22px;
            height: 100%;
            transition: .2s ease;
            box-shadow: 0 3px 12px rgba(18,32,51,.04);
        }

        .management-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(18,32,51,.09);
        }

        .management-icon {
            width: 45px;
            height: 45px;
            background: #dff3f1;
            color: #087f7b;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .management-card h5 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 7px;
        }

        .management-card p {
            color: #71808d;
            font-size: 13px;
            margin-bottom: 17px;
            line-height: 1.5;
        }

        /* =========================
           BUTTONS
        ========================= */

        .btn-theme {
            background: #ff7518;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 9px 17px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            transition: .2s ease;
        }

        .btn-theme:hover {
            background: #e85f00;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 5px 12px rgba(255,117,24,.22);
        }

        .btn-outline-theme {
            background: white;
            color: #087f7b;
            border: 1px solid #087f7b;
            border-radius: 8px;
            padding: 9px 17px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            transition: .2s ease;
        }

        .btn-outline-theme:hover {
            background: #087f7b;
            color: white;
        }

        /* =========================
           QUICK ACTIONS
        ========================= */

        .quick-box {
            background: white;
            border: 1px solid #e4ebef;
            border-radius: 14px;
            padding: 23px;
            box-shadow: 0 3px 12px rgba(18,32,51,.04);
        }

        .quick-link {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 13px 0;
            border-bottom: 1px solid #edf1f3;
            text-decoration: none;
            color: #26384a;
            font-weight: 600;
            transition: .2s ease;
        }

        .quick-link:last-child {
            border-bottom: none;
        }

        .quick-link:hover {
            color: #087f7b;
            padding-left: 5px;
        }

        .quick-link i {
            color: #087f7b;
            font-size: 18px;
        }

        /* =========================
           FOOTER
        ========================= */

        .footer {
            text-align: center;
            color: #8997a3;
            font-size: 13px;
            padding-top: 30px;
        }

        @media (max-width: 900px) {

            .sidebar {
                width: 210px;
            }

            .main-content {
                margin-left: 210px;
            }

            .content {
                padding: 25px 20px;
            }
        }

        @media (max-width: 700px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .logout-link {
                position: static;
                margin-top: 10px;
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                padding: 0 18px;
            }

            .content {
                padding: 22px 16px;
            }
        }

    </style>

</head>

<body>

<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">

    <div class="brand">

        <div class="brand-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>

        <div>
            <div class="brand-text">University LMS</div>
            <div class="brand-small">Learning Management</div>
        </div>

    </div>

    <div class="nav-menu">

        <a href="dashboard.php"
           class="nav-link-custom active">
            <i class="bi bi-grid-1x2-fill"></i>
            Dashboard
        </a>

        <a href="students.php"
           class="nav-link-custom">
            <i class="bi bi-people-fill"></i>
            Students
        </a>

        <a href="teachers.php"
           class="nav-link-custom">
            <i class="bi bi-person-workspace"></i>
            Teachers
        </a>

        <a href="departments.php"
           class="nav-link-custom">
            <i class="bi bi-building"></i>
            Departments
        </a>

        <a href="courses.php"
           class="nav-link-custom">
            <i class="bi bi-book-fill"></i>
            Courses
        </a>

        <a href="announcements.php"
           class="nav-link-custom">
            <i class="bi bi-megaphone-fill"></i>
            Announcements
        </a>

        <a href="../logout.php"
           class="nav-link-custom logout-link">
            <i class="bi bi-box-arrow-right"></i>
            Logout
        </a>

    </div>

</div>


<!-- =========================
     MAIN CONTENT
========================= -->

<div class="main-content">

    <div class="topbar">

        <div class="admin-profile">

            <div class="profile-avatar">
                A
            </div>

            <div class="profile-info">
                <strong>Administrator</strong>
                <span>System Admin</span>
            </div>

        </div>

    </div>


    <div class="content">

        <h1 class="welcome-title">
            Welcome back, Admin!
        </h1>

        <p class="welcome-text">
            Manage your university learning management system from one place.
        </p>


        <!-- WELCOME -->

        <div class="welcome-box">

            <h4>
                <i class="bi bi-stars me-2"></i>
                University LMS Administration
            </h4>

            <p>
                Manage students, teachers, courses, departments and announcements easily.
            </p>

        </div>


        <!-- STATS -->

        <h3 class="section-title">
            System Overview
        </h3>

        <div class="row g-4 mb-5">

            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon teal-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div class="stat-number">
                        <?php echo $student_count; ?>
                    </div>

                    <div class="stat-label">
                        Total Students
                    </div>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon orange-icon">
                        <i class="bi bi-person-workspace"></i>
                    </div>

                    <div class="stat-number">
                        <?php echo $teacher_count; ?>
                    </div>

                    <div class="stat-label">
                        Total Teachers
                    </div>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon teal-icon">
                        <i class="bi bi-building"></i>
                    </div>

                    <div class="stat-number">
                        <?php echo $department_count; ?>
                    </div>

                    <div class="stat-label">
                        Departments
                    </div>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon orange-icon">
                        <i class="bi bi-book-fill"></i>
                    </div>

                    <div class="stat-number">
                        <?php echo $course_count; ?>
                    </div>

                    <div class="stat-label">
                        Courses
                    </div>

                </div>

            </div>

        </div>


        <!-- MANAGEMENT -->

        <h3 class="section-title">
            Management
        </h3>

        <div class="row g-4">

            <div class="col-lg-4 col-md-6">

                <div class="management-card">

                    <div class="management-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <h5>Manage Students</h5>

                    <p>
                        Add, edit, delete and enroll students into courses.
                    </p>

                    <a href="students.php"
                       class="btn-theme">
                        Manage Students
                    </a>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="management-card">

                    <div class="management-icon">
                        <i class="bi bi-person-workspace"></i>
                    </div>

                    <h5>Manage Teachers</h5>

                    <p>
                        Manage teacher accounts and department information.
                    </p>

                    <a href="teachers.php"
                       class="btn-theme">
                        Manage Teachers
                    </a>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="management-card">

                    <div class="management-icon">
                        <i class="bi bi-building"></i>
                    </div>

                    <h5>Departments</h5>

                    <p>
                        Create and manage university departments.
                    </p>

                    <a href="departments.php"
                       class="btn-theme">
                        Manage Departments
                    </a>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="management-card">

                    <div class="management-icon">
                        <i class="bi bi-book-fill"></i>
                    </div>

                    <h5>Courses</h5>

                    <p>
                        Manage courses and assign teachers to them.
                    </p>

                    <a href="courses.php"
                       class="btn-theme">
                        Manage Courses
                    </a>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="management-card">

                    <div class="management-icon">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>

                    <h5>Announcements</h5>

                    <p>
                        Publish important announcements for LMS users.
                    </p>

                    <a href="announcements.php"
                       class="btn-theme">
                        Announcements
                    </a>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="management-card">

                    <div class="management-icon">
                        <i class="bi bi-bar-chart-fill"></i>
                    </div>

                    <h5>System Status</h5>

                    <p>
                        Current LMS has <?php echo $announcement_count; ?> active announcement records.
                    </p>

                    <a href="announcements.php"
                       class="btn-outline-theme">
                        View Announcements
                    </a>

                </div>

            </div>

        </div>


        <div class="footer">
            University LMS &copy; <?php echo date("Y"); ?>
        </div>

    </div>

</div>

</body>
</html>