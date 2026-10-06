<?php

session_start();
include "../includes/db.php";

/* =========================
   TEACHER LOGIN CHECK
========================= */

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];


/* =========================
   TEACHER INFORMATION
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT full_name, email, teacher_id, department_id
     FROM users
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$teacher = mysqli_fetch_assoc($result);

if (!$teacher) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}


/* =========================
   DEPARTMENT
========================= */

$department_name = "Not Assigned";

if (!empty($teacher['department_id'])) {

    $dept_stmt = mysqli_prepare(
        $conn,
        "SELECT name FROM departments WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $dept_stmt,
        "i",
        $teacher['department_id']
    );

    mysqli_stmt_execute($dept_stmt);

    $dept_result = mysqli_stmt_get_result($dept_stmt);
    $dept = mysqli_fetch_assoc($dept_result);

    if ($dept) {
        $department_name = $dept['name'];
    }
}


/* =========================
   TEACHER COURSES
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        courses.id,
        courses.course_code,
        courses.course_name,
        courses.description,
        courses.credit_hours,
        departments.name AS department_name
     FROM courses
     LEFT JOIN departments
        ON courses.department_id = departments.id
     WHERE courses.teacher_id = ?
     ORDER BY courses.id DESC"
);

mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);

$courses_result = mysqli_stmt_get_result($stmt);


/* =========================
   FIRST COURSE
========================= */

$first_course_id = 0;

$first_course_stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM courses
     WHERE teacher_id = ?
     ORDER BY id ASC
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $first_course_stmt,
    "i",
    $teacher_id
);

mysqli_stmt_execute($first_course_stmt);

$first_course_result = mysqli_stmt_get_result(
    $first_course_stmt
);

$first_course = mysqli_fetch_assoc($first_course_result);

if ($first_course) {
    $first_course_id = (int)$first_course['id'];
}


/* =========================
   FIRST ASSIGNMENT
========================= */

$first_assignment_id = 0;

$first_assignment_stmt = mysqli_prepare(
    $conn,
    "SELECT assignments.id
     FROM assignments
     INNER JOIN courses
        ON assignments.course_id = courses.id
     WHERE courses.teacher_id = ?
     ORDER BY assignments.id ASC
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $first_assignment_stmt,
    "i",
    $teacher_id
);

mysqli_stmt_execute($first_assignment_stmt);

$first_assignment_result = mysqli_stmt_get_result(
    $first_assignment_stmt
);

$first_assignment = mysqli_fetch_assoc(
    $first_assignment_result
);

if ($first_assignment) {
    $first_assignment_id = (int)$first_assignment['id'];
}


/* =========================
   COURSE COUNT
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM courses
     WHERE teacher_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);

$course_count = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)['total'];


/* =========================
   ASSIGNMENT COUNT
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM assignments
     INNER JOIN courses
        ON assignments.course_id = courses.id
     WHERE courses.teacher_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);

$assignment_count = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)['total'];


/* =========================
   STUDENT COUNT
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(DISTINCT enrollments.student_id) AS total
     FROM enrollments
     INNER JOIN courses
        ON enrollments.course_id = courses.id
     WHERE courses.teacher_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);

$student_count = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)['total'];


/* =========================
   INITIAL
========================= */

$initial = strtoupper(
    substr($teacher['full_name'], 0, 1)
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Teacher Dashboard - University LMS</title>

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

        /* SIDEBAR */

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

        /* MAIN */

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

        .teacher-profile {
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

        .welcome-title {
            font-size: 30px;
            font-weight: 750;
            margin-bottom: 5px;
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
        }

        /* WELCOME */

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

        /* STATS */

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
            margin-bottom: 2px;
        }

        .stat-label {
            color: #687887;
            font-size: 14px;
            font-weight: 600;
        }

        /* COURSES */

        .course-card {
            background: white;
            border: 1px solid #e4ebef;
            border-radius: 14px;
            padding: 22px;
            height: 100%;
            box-shadow: 0 3px 12px rgba(18,32,51,.04);
            transition: .2s ease;
        }

        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(18,32,51,.09);
        }

        .course-icon {
            width: 45px;
            height: 45px;
            background: #dff3f1;
            color: #087f7b;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 14px;
        }

        .course-code {
            color: #087f7b;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .course-card h5 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .course-card p {
            color: #71808d;
            font-size: 13px;
            line-height: 1.5;
            min-height: 40px;
            margin-bottom: 17px;
        }

        .course-meta {
            color: #7a8995;
            font-size: 13px;
            margin-bottom: 16px;
        }

        /* BUTTON */

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

        /* PROFILE */

        .profile-card {
            background: white;
            border: 1px solid #e4ebef;
            border-radius: 14px;
            padding: 24px;
            height: 100%;
            box-shadow: 0 3px 12px rgba(18,32,51,.04);
        }

        .profile-big {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: #087f7b;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 14px;
        }

        .profile-card h5 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .profile-card .email {
            color: #7a8995;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .profile-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #edf1f3;
            padding: 10px 0;
            font-size: 14px;
        }

        .profile-row:last-child {
            border-bottom: none;
        }

        .profile-row span:first-child {
            color: #7a8995;
        }

        .profile-row span:last-child {
            font-weight: 600;
            text-align: right;
        }

        /* QUICK ACCESS */

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

        /* EMPTY */

        .empty-course {
            background: white;
            border: 1px dashed #cbd8de;
            border-radius: 14px;
            padding: 35px;
            text-align: center;
            color: #758591;
        }

        .empty-course i {
            font-size: 35px;
            color: #087f7b;
            display: block;
            margin-bottom: 10px;
        }

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

            <div class="brand-text">
                University LMS
            </div>

            <div class="brand-small">
                Teaching Portal
            </div>

        </div>

    </div>


    <div class="nav-menu">

        <a
            href="dashboard.php"
            class="nav-link-custom active"
        >
            <i class="bi bi-grid-1x2-fill"></i>
            Dashboard
        </a>


        <!-- MY COURSES -->

        <?php if ($first_course_id > 0): ?>

            <a
                href="course.php?id=<?php echo $first_course_id; ?>"
                class="nav-link-custom"
            >
                <i class="bi bi-book-fill"></i>
                My Courses
            </a>

        <?php else: ?>

            <a
                href="#courses"
                class="nav-link-custom"
            >
                <i class="bi bi-book-fill"></i>
                My Courses
            </a>

        <?php endif; ?>


        <!-- STUDENTS -->

        <?php if ($first_course_id > 0): ?>

            <a
                href="students.php?course_id=<?php echo $first_course_id; ?>"
                class="nav-link-custom"
            >
                <i class="bi bi-people-fill"></i>
                Students
            </a>

        <?php else: ?>

            <a
                href="#courses"
                class="nav-link-custom"
            >
                <i class="bi bi-people-fill"></i>
                Students
            </a>

        <?php endif; ?>


        <!-- MATERIALS -->

        <?php if ($first_course_id > 0): ?>

            <a
                href="materials.php?course_id=<?php echo $first_course_id; ?>"
                class="nav-link-custom"
            >
                <i class="bi bi-folder-fill"></i>
                Materials
            </a>

        <?php else: ?>

            <a
                href="#courses"
                class="nav-link-custom"
            >
                <i class="bi bi-folder-fill"></i>
                Materials
            </a>

        <?php endif; ?>


        <!-- SUBMISSIONS -->

        <?php if ($first_assignment_id > 0): ?>

            <a
                href="submissions.php?assignment_id=<?php echo $first_assignment_id; ?>"
                class="nav-link-custom"
            >
                <i class="bi bi-file-earmark-check-fill"></i>
                Submissions
            </a>

        <?php else: ?>

            <?php if ($first_course_id > 0): ?>

                <a
                    href="course.php?id=<?php echo $first_course_id; ?>#assignments"
                    class="nav-link-custom"
                >
                    <i class="bi bi-file-earmark-check-fill"></i>
                    Submissions
                </a>

            <?php else: ?>

                <a
                    href="#courses"
                    class="nav-link-custom"
                >
                    <i class="bi bi-file-earmark-check-fill"></i>
                    Submissions
                </a>

            <?php endif; ?>

        <?php endif; ?>


        <a
            href="../logout.php"
            class="nav-link-custom logout-link"
        >
            <i class="bi bi-box-arrow-right"></i>
            Logout
        </a>

    </div>

</div>


<!-- =========================
     MAIN
========================= -->

<div class="main-content">

    <div class="topbar">

        <div class="teacher-profile">

            <div class="profile-avatar">
                <?php echo htmlspecialchars($initial); ?>
            </div>

            <div class="profile-info">

                <strong>
                    <?php echo htmlspecialchars($teacher['full_name']); ?>
                </strong>

                <span>
                    Teacher
                </span>

            </div>

        </div>

    </div>


    <div class="content">

        <h1 class="welcome-title">

            Welcome back,
            <?php echo htmlspecialchars($teacher['full_name']); ?>!

        </h1>

        <p class="welcome-text">

            Manage your courses, students, assignments and learning materials.

        </p>


        <div class="welcome-box">

            <h4>

                <i class="bi bi-stars me-2"></i>

                Teaching Dashboard

            </h4>

            <p>

                Everything you need to manage your teaching activities is available here.

            </p>

        </div>


        <!-- STATS -->

        <h3 class="section-title">
            Teaching Overview
        </h3>


        <div class="row g-4 mb-5">

            <div class="col-lg-4 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon teal-icon">
                        <i class="bi bi-book-fill"></i>
                    </div>

                    <div class="stat-number">
                        <?php echo $course_count; ?>
                    </div>

                    <div class="stat-label">
                        My Courses
                    </div>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon orange-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div class="stat-number">
                        <?php echo $student_count; ?>
                    </div>

                    <div class="stat-label">
                        Students
                    </div>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon teal-icon">
                        <i class="bi bi-journal-text"></i>
                    </div>

                    <div class="stat-number">
                        <?php echo $assignment_count; ?>
                    </div>

                    <div class="stat-label">
                        Assignments
                    </div>

                </div>

            </div>

        </div>


        <!-- COURSES -->

        <h3 class="section-title" id="courses">
            My Courses
        </h3>


        <div class="row g-4 mb-5">

            <?php if (mysqli_num_rows($courses_result) > 0): ?>

                <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>

                    <div class="col-lg-4 col-md-6">

                        <div class="course-card">

                            <div class="course-icon">
                                <i class="bi bi-book-fill"></i>
                            </div>

                            <div class="course-code">

                                <?php echo htmlspecialchars(
                                    $course['course_code']
                                ); ?>

                            </div>

                            <h5>

                                <?php echo htmlspecialchars(
                                    $course['course_name']
                                ); ?>

                            </h5>

                            <p>

                                <?php

                                echo htmlspecialchars(
                                    $course['description']
                                    ?: "Course information and learning resources."
                                );

                                ?>

                            </p>

                            <div class="course-meta">

                                <i class="bi bi-clock me-1"></i>

                                <?php echo htmlspecialchars(
                                    $course['credit_hours']
                                ); ?>

                                Credit Hours

                            </div>

                            <a
                                href="course.php?id=<?php echo $course['id']; ?>"
                                class="btn-theme"
                            >
                                Manage Course
                            </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="col-12">

                    <div class="empty-course">

                        <i class="bi bi-book"></i>

                        <strong>
                            No courses assigned yet.
                        </strong>

                        <div class="mt-1">
                            Courses assigned to you will appear here.
                        </div>

                    </div>

                </div>

            <?php endif; ?>

        </div>


        <!-- PROFILE + QUICK ACCESS -->

        <div class="row g-4">

            <div class="col-lg-6">

                <div class="profile-card">

                    <div class="profile-big">
                        <?php echo htmlspecialchars($initial); ?>
                    </div>

                    <h5>

                        <?php echo htmlspecialchars(
                            $teacher['full_name']
                        ); ?>

                    </h5>

                    <div class="email">

                        <?php echo htmlspecialchars(
                            $teacher['email']
                        ); ?>

                    </div>


                    <div class="profile-row">

                        <span>
                            Teacher ID
                        </span>

                        <span>

                            <?php echo htmlspecialchars(
                                $teacher['teacher_id']
                            ); ?>

                        </span>

                    </div>


                    <div class="profile-row">

                        <span>
                            Department
                        </span>

                        <span>

                            <?php echo htmlspecialchars(
                                $department_name
                            ); ?>

                        </span>

                    </div>


                    <div class="profile-row">

                        <span>
                            Courses
                        </span>

                        <span>
                            <?php echo $course_count; ?>
                        </span>

                    </div>

                </div>

            </div>


            <div class="col-lg-6">

                <div class="quick-box">

                    <h5 class="fw-bold mb-2">
                        Quick Access
                    </h5>


                    <?php if ($first_course_id > 0): ?>

                        <a
                            href="course.php?id=<?php echo $first_course_id; ?>"
                            class="quick-link"
                        >
                            <i class="bi bi-book-fill"></i>
                            My Courses
                        </a>


                        <a
                            href="students.php?course_id=<?php echo $first_course_id; ?>"
                            class="quick-link"
                        >
                            <i class="bi bi-people-fill"></i>
                            My Students
                        </a>


                        <a
                            href="materials.php?course_id=<?php echo $first_course_id; ?>"
                            class="quick-link"
                        >
                            <i class="bi bi-folder-fill"></i>
                            Course Materials
                        </a>


                    <?php else: ?>

                        <a href="#courses" class="quick-link">
                            <i class="bi bi-book-fill"></i>
                            My Courses
                        </a>

                        <a href="#courses" class="quick-link">
                            <i class="bi bi-people-fill"></i>
                            My Students
                        </a>

                        <a href="#courses" class="quick-link">
                            <i class="bi bi-folder-fill"></i>
                            Course Materials
                        </a>

                    <?php endif; ?>


                    <?php if ($first_assignment_id > 0): ?>

                        <a
                            href="submissions.php?assignment_id=<?php echo $first_assignment_id; ?>"
                            class="quick-link"
                        >
                            <i class="bi bi-file-earmark-check-fill"></i>
                            Student Submissions
                        </a>

                    <?php elseif ($first_course_id > 0): ?>

                        <a
                            href="course.php?id=<?php echo $first_course_id; ?>#assignments"
                            class="quick-link"
                        >
                            <i class="bi bi-file-earmark-check-fill"></i>
                            Student Submissions
                        </a>

                    <?php else: ?>

                        <a href="#courses" class="quick-link">
                            <i class="bi bi-file-earmark-check-fill"></i>
                            Student Submissions
                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <div class="footer">

            University LMS &copy;
            <?php echo date("Y"); ?>

        </div>

    </div>

</div>

</body>
</html>