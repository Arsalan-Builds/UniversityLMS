<?php

session_start();

include "../includes/db.php";

/* =====================================================
   STUDENT LOGIN CHECK
===================================================== */

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];


/* =====================================================
   IF COURSE ID IS PROVIDED
   SHOW MATERIALS FOR THAT COURSE
===================================================== */

if (isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {

    $course_id = intval($_GET['course_id']);


    /* -----------------------------------------------
       CHECK STUDENT IS ENROLLED
    ------------------------------------------------ */

    $course_sql = "
        SELECT
            courses.id,
            courses.course_code,
            courses.course_name
        FROM enrollments
        INNER JOIN courses
            ON enrollments.course_id = courses.id
        WHERE enrollments.student_id = ?
        AND courses.id = ?
    ";

    $course_stmt = mysqli_prepare($conn, $course_sql);

    mysqli_stmt_bind_param(
        $course_stmt,
        "ii",
        $student_id,
        $course_id
    );

    mysqli_stmt_execute($course_stmt);

    $course_result = mysqli_stmt_get_result($course_stmt);

    if (mysqli_num_rows($course_result) == 0) {
        die("You are not enrolled in this course.");
    }

    $course = mysqli_fetch_assoc($course_result);


    /* -----------------------------------------------
       GET MATERIALS
    ------------------------------------------------ */

    $materials_sql = "
        SELECT
            id,
            title,
            description,
            file_path,
            uploaded_at
        FROM materials
        WHERE course_id = ?
        ORDER BY uploaded_at DESC
    ";

    $materials_stmt = mysqli_prepare(
        $conn,
        $materials_sql
    );

    mysqli_stmt_bind_param(
        $materials_stmt,
        "i",
        $course_id
    );

    mysqli_stmt_execute($materials_stmt);

    $materials_result = mysqli_stmt_get_result(
        $materials_stmt
    );

    ?>

    <!DOCTYPE html>

    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <meta name="viewport"
              content="width=device-width, initial-scale=1.0">

        <title>Course Materials</title>

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
        >

        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        >

        <style>

            body {
                background: #f4f8fa;
                font-family: "Segoe UI", Inter, Arial, sans-serif;
                color: #122033;
            }

            .main-content {
                padding: 35px;
            }

            .header-card {
                background: white;
                padding: 25px;
                border-radius: 12px;
                box-shadow: 0 3px 12px rgba(18,32,51,.05);
                margin-bottom: 25px;
            }

            .header-card h2 {
                color: #087f7b;
                font-size: 25px;
                font-weight: 700;
                margin-bottom: 6px;
            }

            .course-code {
                color: #71808d;
            }

            .materials-card {
                background: white;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 3px 12px rgba(18,32,51,.05);
            }

            .card-header-custom {
                background: #087f7b;
                color: white;
                padding: 18px 22px;
                font-size: 19px;
                font-weight: 700;
            }

            .table th {
                background: #f4f8fa;
                font-size: 14px;
                font-weight: 700;
            }

            .table td {
                font-size: 14px;
                vertical-align: middle;
            }

            .btn-theme {
                background: #ff7518;
                color: white;
                border: none;
                border-radius: 7px;
                padding: 8px 15px;
                font-weight: 600;
                text-decoration: none;
                display: inline-block;
            }

            .btn-theme:hover {
                background: #e85f00;
                color: white;
            }

            .btn-back {
                background: #087f7b;
                color: white;
                border: none;
                border-radius: 7px;
                padding: 9px 17px;
                font-weight: 600;
                text-decoration: none;
            }

            .btn-back:hover {
                background: #066c69;
                color: white;
            }

        </style>

    </head>

    <body>

    <div class="main-content">

        <div class="header-card">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div>

                    <h2>
                        <i class="bi bi-folder-fill"></i>
                        <?php echo htmlspecialchars($course['course_name']); ?>
                    </h2>

                    <div class="course-code">

                        Course Code:

                        <strong>
                            <?php echo htmlspecialchars($course['course_code']); ?>
                        </strong>

                    </div>

                </div>

                <a
                    href="materials.php"
                    class="btn-back"
                >
                    <i class="bi bi-arrow-left"></i>
                    Back to Materials
                </a>

            </div>

        </div>


        <div class="materials-card">

            <div class="card-header-custom">

                <i class="bi bi-folder2-open"></i>

                Course Materials

            </div>


            <?php if (mysqli_num_rows($materials_result) > 0): ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover mb-0">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Title</th>

                                <th>Description</th>

                                <th>Uploaded</th>

                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php

                        $count = 1;

                        while ($material = mysqli_fetch_assoc($materials_result)):

                        ?>

                            <tr>

                                <td>
                                    <?php echo $count++; ?>
                                </td>

                                <td>

                                    <strong style="color:#087f7b;">

                                        <i class="bi bi-file-earmark-text"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $material['title']
                                        );
                                        ?>

                                    </strong>

                                </td>

                                <td>

                                    <?php

                                    if (!empty($material['description'])) {

                                        echo nl2br(
                                            htmlspecialchars(
                                                $material['description']
                                            )
                                        );

                                    } else {

                                        echo '<span class="text-muted">
                                                No description
                                              </span>';

                                    }

                                    ?>

                                </td>

                                <td>

                                    <?php

                                    echo date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $material['uploaded_at']
                                        )
                                    );

                                    ?>

                                </td>

                                <td>

                                    <?php if (!empty($material['file_path'])): ?>

                                        <a
                                            href="../<?php echo htmlspecialchars($material['file_path']); ?>"
                                            target="_blank"
                                            class="btn-theme"
                                        >

                                            <i class="bi bi-eye"></i>

                                            View / Download

                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No file
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="p-5 text-center">

                    <i
                        class="bi bi-folder-x"
                        style="font-size:40px;color:#087f7b;"
                    ></i>

                    <h5 class="mt-3">
                        No Materials Available
                    </h5>

                    <p class="text-muted mb-0">
                        Your teacher has not uploaded any materials for this course yet.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

    </body>

    </html>

    <?php

    exit;
}


/* =====================================================
   NO COURSE ID
   SHOW ALL ENROLLED COURSES
===================================================== */

$courses_sql = "
    SELECT
        courses.id,
        courses.course_code,
        courses.course_name,
        courses.description
    FROM enrollments
    INNER JOIN courses
        ON enrollments.course_id = courses.id
    WHERE enrollments.student_id = ?
    ORDER BY courses.course_name ASC
";

$courses_stmt = mysqli_prepare(
    $conn,
    $courses_sql
);

mysqli_stmt_bind_param(
    $courses_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($courses_stmt);

$courses_result = mysqli_stmt_get_result(
    $courses_stmt
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Study Materials - University LMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        body {
            background: #f4f8fa;
            font-family: "Segoe UI", Inter, Arial, sans-serif;
            color: #122033;
        }

        .main-content {
            padding: 35px;
        }

        .page-header {
            margin-bottom: 28px;
        }

        .page-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #122033;
            margin-bottom: 5px;
        }

        .page-header p {
            color: #71808d;
            margin: 0;
        }

        .course-card {
            background: white;
            border: 1px solid #e4ebef;
            border-radius: 13px;
            padding: 23px;
            height: 100%;
            box-shadow: 0 3px 12px rgba(18,32,51,.04);
            transition: .2s ease;
        }

        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(18,32,51,.09);
        }

        .course-icon {
            width: 48px;
            height: 48px;
            border-radius: 11px;
            background: #dff3f1;
            color: #087f7b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 16px;
        }

        .course-card h5 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .course-code {
            color: #087f7b;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .course-card p {
            color: #71808d;
            font-size: 14px;
            min-height: 42px;
        }

        .btn-theme {
            background: #ff7518;
            color: white;
            border: none;
            border-radius: 7px;
            padding: 9px 16px;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
        }

        .btn-theme:hover {
            background: #e85f00;
            color: white;
        }

        .btn-back {
            background: #087f7b;
            color: white;
            border: none;
            border-radius: 7px;
            padding: 9px 17px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #066c69;
            color: white;
        }

        .empty-box {
            background: white;
            border-radius: 12px;
            padding: 45px;
            text-align: center;
            box-shadow: 0 3px 12px rgba(18,32,51,.05);
        }

    </style>

</head>

<body>


<div class="main-content">


    <!-- HEADER -->

    <div class="page-header">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <h2>
                    <i class="bi bi-folder-fill"
                       style="color:#087f7b;"></i>

                    Study Materials
                </h2>

                <p>
                    Select a course to view materials uploaded by your teacher.
                </p>

            </div>


            <a
                href="dashboard.php"
                class="btn-back"
            >

                <i class="bi bi-arrow-left"></i>

                Back to Dashboard

            </a>

        </div>

    </div>



    <!-- COURSES -->

    <?php if (mysqli_num_rows($courses_result) > 0): ?>


        <div class="row g-4">


            <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>


                <div class="col-lg-4 col-md-6">


                    <div class="course-card">


                        <div class="course-icon">

                            <i class="bi bi-book-fill"></i>

                        </div>


                        <h5>

                            <?php
                            echo htmlspecialchars(
                                $course['course_name']
                            );
                            ?>

                        </h5>


                        <div class="course-code">

                            Course Code:

                            <?php
                            echo htmlspecialchars(
                                $course['course_code']
                            );
                            ?>

                        </div>


                        <p>

                            <?php

                            if (!empty($course['description'])) {

                                echo htmlspecialchars(
                                    $course['description']
                                );

                            } else {

                                echo "View study materials for this course.";

                            }

                            ?>

                        </p>


                        <a
                            href="materials.php?course_id=<?php echo $course['id']; ?>"
                            class="btn-theme"
                        >

                            <i class="bi bi-folder2-open"></i>

                            View Materials

                        </a>


                    </div>


                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <div class="empty-box">

            <i
                class="bi bi-book"
                style="font-size:45px;color:#087f7b;"
            ></i>

            <h4 class="mt-3">
                No Enrolled Courses
            </h4>

            <p class="text-muted">
                You are not enrolled in any courses yet.
            </p>

        </div>


    <?php endif; ?>


</div>


</body>

</html>