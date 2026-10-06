<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
   CHECK COURSE ID
   Accept both:
   course_id and id
========================= */

if (isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {

    $course_id = intval($_GET['course_id']);

} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {

    $course_id = intval($_GET['id']);

} else {

    header("Location: dashboard.php");
    exit;
}


/* =========================
   GET COURSE
========================= */

$stmt = mysqli_prepare($conn, "

    SELECT
        courses.id,
        courses.course_name,
        courses.course_code

    FROM courses

    WHERE courses.id = ?
    AND courses.teacher_id = ?

");

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $course_id,
    $teacher_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$course = mysqli_fetch_assoc($result);

if (!$course) {

    die(
        "Course not found or you are not assigned to this course."
    );

}


/* =========================
   CREATE UPLOAD DIRECTORY
========================= */

$upload_dir = "../uploads/materials/";

if (!is_dir($upload_dir)) {

    mkdir(
        $upload_dir,
        0777,
        true
    );

}


/* =========================
   UPLOAD MATERIAL
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST['upload_material'])
) {

    $title = trim($_POST['title'] ?? '');

    $description = trim(
        $_POST['description'] ?? ''
    );


    if (empty($title)) {

        $error = "Material title is required.";

    } elseif (!isset($_FILES['material_file'])) {

        $error = "Please select a file.";

    } else {

        $file = $_FILES['material_file'];


        if ($file['error'] !== UPLOAD_ERR_OK) {

            $error = "File upload failed.";

        } else {

            $allowed_extensions = [

                "pdf",
                "doc",
                "docx",
                "ppt",
                "pptx",
                "txt",
                "zip"

            ];


            $original_name = basename(
                $file['name']
            );


            $extension = strtolower(
                pathinfo(
                    $original_name,
                    PATHINFO_EXTENSION
                )
            );


            $max_size = 10 * 1024 * 1024;


            if (
                !in_array(
                    $extension,
                    $allowed_extensions
                )
            ) {

                $error =
                    "Invalid file type. Allowed: PDF, DOC, DOCX, PPT, PPTX, TXT, ZIP.";

            } elseif ($file['size'] > $max_size) {

                $error =
                    "File is too large. Maximum size is 10 MB.";

            } else {


                /*
                 * Generate unique filename
                 */

                $new_filename =
                    "course_" .
                    $course_id .
                    "_" .
                    time() .
                    "_" .
                    uniqid() .
                    "." .
                    $extension;


                $destination =
                    $upload_dir .
                    $new_filename;


                if (
                    move_uploaded_file(
                        $file['tmp_name'],
                        $destination
                    )
                ) {


                    /*
                     * Path saved in database
                     */

                    $database_path =
                        "uploads/materials/" .
                        $new_filename;


                    $stmt = mysqli_prepare(
                        $conn,
                        "

                        INSERT INTO materials
                        (
                            course_id,
                            title,
                            description,
                            file_path
                        )

                        VALUES (?, ?, ?, ?)

                        "
                    );


                    if (!$stmt) {

                        if (file_exists($destination)) {
                            unlink($destination);
                        }

                        $error =
                            "Database error: " .
                            mysqli_error($conn);

                    } else {


                        mysqli_stmt_bind_param(
                            $stmt,
                            "isss",
                            $course_id,
                            $title,
                            $description,
                            $database_path
                        );


                        if (
                            mysqli_stmt_execute($stmt)
                        ) {

                            header(
                                "Location: materials.php?course_id=" .
                                $course_id
                            );

                            exit;

                        } else {

                            if (
                                file_exists(
                                    $destination
                                )
                            ) {

                                unlink(
                                    $destination
                                );

                            }

                            $error =
                                "File uploaded but database save failed.";

                        }

                    }

                } else {

                    $error =
                        "Could not save the uploaded file.";

                }

            }

        }

    }

}


/* =========================
   DELETE MATERIAL
========================= */

if (isset($_GET['delete'])) {

    $material_id = intval(
        $_GET['delete']
    );


    /*
     * First get file path
     * and make sure material
     * belongs to this teacher's course.
     */

    $stmt = mysqli_prepare(
        $conn,
        "

        SELECT materials.file_path

        FROM materials

        INNER JOIN courses
            ON materials.course_id = courses.id

        WHERE materials.id = ?
        AND materials.course_id = ?
        AND courses.teacher_id = ?

        "
    );


    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "iii",
            $material_id,
            $course_id,
            $teacher_id
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result(
            $stmt
        );

        $material = mysqli_fetch_assoc(
            $result
        );


        if ($material) {

            /*
             * Delete physical file
             */

            $file_path =
                "../" .
                $material['file_path'];


            if (
                file_exists($file_path)
            ) {

                unlink($file_path);

            }


            /*
             * Delete database record
             */

            $stmt = mysqli_prepare(
                $conn,
                "

                DELETE FROM materials

                WHERE id = ?

                "
            );


            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $material_id
            );


            mysqli_stmt_execute($stmt);

        }

    }


    header(
        "Location: materials.php?course_id=" .
        $course_id
    );

    exit;
}


/* =========================
   GET MATERIALS
========================= */

$stmt = mysqli_prepare(
    $conn,
    "

    SELECT
        id,
        title,
        description,
        file_path,
        uploaded_at

    FROM materials

    WHERE course_id = ?

    ORDER BY uploaded_at DESC

    "
);


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $course_id
);

mysqli_stmt_execute($stmt);

$materials_result =
    mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Course Materials -
        <?php
        echo htmlspecialchars(
            $course['course_name']
        );
        ?>
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    <style>

        body {

            background: #f4f8fa;

            font-family:
                "Segoe UI",
                Inter,
                Arial,
                sans-serif;

            color: #263238;

        }


        .navbar-custom {

            background: #087f7b;

            box-shadow:
                0 3px 10px
                rgba(0, 0, 0, 0.08);

        }


        .navbar-brand {

            font-weight: 700;

            font-size: 20px;

        }


        .page-container {

            max-width: 1150px;

            margin: 40px auto;

            padding: 0 20px;

        }


        .course-card {

            background: #ffffff;

            border-radius: 12px;

            padding: 25px 30px;

            margin-bottom: 25px;

            border-left:
                5px solid #087f7b;

            box-shadow:
                0 4px 15px
                rgba(0, 0, 0, 0.06);

        }


        .course-card h2 {

            color: #087f7b;

            font-size: 26px;

            font-weight: 700;

            margin-bottom: 8px;

        }


        .course-code {

            color: #666;

            font-size: 15px;

        }


        .upload-card,
        .materials-card {

            background: #ffffff;

            border-radius: 12px;

            overflow: hidden;

            margin-bottom: 25px;

            box-shadow:
                0 4px 15px
                rgba(0, 0, 0, 0.06);

        }


        .card-header-custom {

            background: #087f7b;

            color: white;

            padding: 16px 22px;

            font-size: 18px;

            font-weight: 700;

        }


        .card-body-custom {

            padding: 25px;

        }


        .form-label {

            font-weight: 600;

            color: #37474f;

        }


        .form-control {

            border-radius: 7px;

            padding: 10px 12px;

        }


        .form-control:focus {

            border-color: #087f7b;

            box-shadow:
                0 0 0 0.2rem
                rgba(8, 127, 123, 0.15);

        }


        .upload-btn {

            background: #ff7518;

            border: none;

            color: white;

            padding: 10px 18px;

            border-radius: 7px;

            font-weight: 600;

        }


        .upload-btn:hover {

            background: #e85f00;

            color: white;

        }


        .table {

            margin-bottom: 0;

            vertical-align: middle;

        }


        .table thead th {

            background: #087f7b;

            color: white;

            border: none;

            padding: 14px 12px;

            font-size: 14px;

        }


        .table tbody td {

            padding: 14px 12px;

            font-size: 14px;

        }


        .table tbody tr:hover {

            background: #f7fafb;

        }


        .view-btn {

            background: #087f7b;

            color: white;

            border: none;

            padding: 7px 12px;

            border-radius: 6px;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

            display: inline-block;

        }


        .view-btn:hover {

            background: #066966;

            color: white;

        }


        .delete-btn {

            background: #dc3545;

            color: white;

            border: none;

            padding: 7px 12px;

            border-radius: 6px;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

            display: inline-block;

        }


        .delete-btn:hover {

            background: #bb2d3b;

            color: white;

        }


        .back-btn {

            background: #ff7518;

            color: white;

            border: none;

            padding: 10px 18px;

            border-radius: 7px;

            font-weight: 600;

            text-decoration: none;

            display: inline-block;

            margin-top: 20px;

        }


        .back-btn:hover {

            background: #e85f00;

            color: white;

        }


        .empty-box {

            text-align: center;

            padding: 40px 20px;

            color: #777;

        }


        .empty-box i {

            font-size: 45px;

            color: #087f7b;

            margin-bottom: 12px;

        }


        .alert {

            border-radius: 8px;

        }

    </style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar navbar-dark navbar-custom">

    <div class="container">

        <a
            class="navbar-brand"
            href="dashboard.php"
        >

            <i class="bi bi-mortarboard-fill"></i>

            University LMS

        </a>


        <div>

            <a
                href="dashboard.php"
                class="btn btn-light btn-sm me-2"
            >

                <i class="bi bi-speedometer2"></i>

                Dashboard

            </a>


            <a
                href="../logout.php"
                class="btn btn-danger btn-sm"
            >

                <i class="bi bi-box-arrow-right"></i>

                Logout

            </a>

        </div>

    </div>

</nav>


<div class="page-container">


    <!-- =========================
         COURSE
    ========================= -->

    <div class="course-card">

        <h2>

            <i class="bi bi-book-fill"></i>

            <?php

            echo htmlspecialchars(
                $course['course_name']
            );

            ?>

        </h2>


        <div class="course-code">

            Course Code:

            <strong>

                <?php

                echo htmlspecialchars(
                    $course['course_code']
                );

                ?>

            </strong>

        </div>

    </div>


    <!-- =========================
         ERROR
    ========================= -->

    <?php if (isset($error)): ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <?php

            echo htmlspecialchars(
                $error
            );

            ?>

        </div>

    <?php endif; ?>


    <!-- =========================
         UPLOAD FORM
    ========================= -->

    <div class="upload-card">

        <div class="card-header-custom">

            <i class="bi bi-cloud-arrow-up-fill"></i>

            Upload Course Material

        </div>


        <div class="card-body-custom">

            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <div class="mb-3">

                    <label class="form-label">

                        Material Title

                    </label>


                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        placeholder="Example: Lecture 1 - Introduction"
                        required
                    >

                </div>


                <div class="mb-3">

                    <label class="form-label">

                        Description

                    </label>


                    <textarea
                        name="description"
                        class="form-control"
                        rows="3"
                        placeholder="Optional description"
                    ></textarea>

                </div>


                <div class="mb-3">

                    <label class="form-label">

                        Select File

                    </label>


                    <input
                        type="file"
                        name="material_file"
                        class="form-control"
                        required
                    >


                    <small class="text-muted">

                        Allowed:
                        PDF, DOC, DOCX, PPT, PPTX, TXT, ZIP.
                        Maximum 10 MB.

                    </small>

                </div>


                <button
                    type="submit"
                    name="upload_material"
                    class="upload-btn"
                >

                    <i class="bi bi-upload"></i>

                    Upload Material

                </button>


            </form>

        </div>

    </div>


    <!-- =========================
         MATERIAL LIST
    ========================= -->

    <div class="materials-card">

        <div class="card-header-custom">

            <i class="bi bi-folder-fill"></i>

            Course Materials

        </div>


        <div class="card-body-custom">


            <?php

            if (
                mysqli_num_rows(
                    $materials_result
                ) > 0
            ):

            ?>


                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead>

                            <tr>

                                <th>Title</th>

                                <th>Description</th>

                                <th>Uploaded</th>

                                <th>File</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php

                        while (
                            $material =
                            mysqli_fetch_assoc(
                                $materials_result
                            )
                        ):

                        ?>


                            <tr>


                                <td>

                                    <strong>

                                        <?php

                                        echo htmlspecialchars(
                                            $material['title']
                                        );

                                        ?>

                                    </strong>

                                </td>


                                <td>

                                    <?php

                                    if (
                                        empty(
                                            $material[
                                                'description'
                                            ]
                                        )
                                    ) {

                                        echo "No description";

                                    } else {

                                        echo nl2br(
                                            htmlspecialchars(
                                                $material[
                                                    'description'
                                                ]
                                            )
                                        );

                                    }

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $material[
                                                'uploaded_at'
                                            ]
                                        )
                                    );

                                    ?>

                                </td>


                                <td>

                                    <a
                                        href="../<?php echo htmlspecialchars($material['file_path']); ?>"
                                        target="_blank"
                                        class="view-btn"
                                    >

                                        <i class="bi bi-file-earmark-arrow-down"></i>

                                        View / Download

                                    </a>

                                </td>


                                <td>

                                    <a
                                        href="materials.php?course_id=<?php echo $course_id; ?>&delete=<?php echo $material['id']; ?>"
                                        class="delete-btn"
                                        onclick="return confirm('Delete this material?');"
                                    >

                                        <i class="bi bi-trash"></i>

                                        Delete

                                    </a>

                                </td>


                            </tr>


                        <?php

                        endwhile;

                        ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <div class="empty-box">

                    <i class="bi bi-folder2-open"></i>

                    <h5>No Course Materials</h5>

                    <p class="mb-0">

                        No course materials have been uploaded yet.

                    </p>

                </div>


            <?php endif; ?>


            <!-- BACK TO DASHBOARD -->

            <a
                href="dashboard.php"
                class="back-btn"
            >

                <i class="bi bi-arrow-left"></i>

                Back to Dashboard

            </a>


        </div>

    </div>


</div>


</body>

</html>