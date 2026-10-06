```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "../includes/db.php";

// Only students can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "student") {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

$message = "";
$message_type = "success";


// Get assignment ID
if (!isset($_GET['id'])) {
    die("Assignment ID is missing.");
}

$assignment_id = intval($_GET['id']);


// Get assignment
$sql = "
    SELECT
        assignments.id,
        assignments.title,
        assignments.description,
        assignments.due_date,
        courses.course_name,
        courses.course_code
    FROM assignments

    INNER JOIN courses
        ON assignments.course_id = courses.id

    INNER JOIN enrollments
        ON courses.id = enrollments.course_id

    WHERE assignments.id = ?
    AND enrollments.student_id = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $assignment_id,
    $student_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


if (mysqli_num_rows($result) == 0) {

    die("Assignment not found or you are not enrolled in this course.");

}

$assignment = mysqli_fetch_assoc($result);


// Check existing submission
$check_sql = "
    SELECT id, file_path, submitted_at
    FROM submissions
    WHERE assignment_id = ?
    AND student_id = ?
";

$check_stmt = mysqli_prepare($conn, $check_sql);

mysqli_stmt_bind_param(
    $check_stmt,
    "ii",
    $assignment_id,
    $student_id
);

mysqli_stmt_execute($check_stmt);

$check_result = mysqli_stmt_get_result($check_stmt);

$existing_submission = mysqli_fetch_assoc($check_result);


// ===============================
// HANDLE FILE SUBMISSION
// ===============================

if (isset($_POST['submit_assignment'])) {

    if (!isset($_FILES['assignment_file'])) {

        $message = "Please select a file.";
        $message_type = "danger";

    } else {

        $file = $_FILES['assignment_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {

            $message = "There was an error uploading the file.";
            $message_type = "danger";

        } else {

            $original_name = basename($file['name']);

            $extension = strtolower(
                pathinfo(
                    $original_name,
                    PATHINFO_EXTENSION
                )
            );


            // Allowed file types
            $allowed_extensions = [
                "pdf",
                "doc",
                "docx",
                "txt",
                "zip"
            ];


            if (!in_array($extension, $allowed_extensions)) {

                $message =
                    "Only PDF, DOC, DOCX, TXT and ZIP files are allowed.";

                $message_type = "danger";

            } else {

                // Create uploads folder
                $upload_folder = "../uploads/submissions/";

                if (!is_dir($upload_folder)) {

                    mkdir(
                        $upload_folder,
                        0777,
                        true
                    );
                }


                // Create unique filename
                $new_filename =
                    "student_" .
                    $student_id .
                    "_assignment_" .
                    $assignment_id .
                    "_" .
                    time() .
                    "." .
                    $extension;


                $target_file =
                    $upload_folder .
                    $new_filename;


                if (move_uploaded_file(
                    $file['tmp_name'],
                    $target_file
                )) {

                    $relative_path =
                        "uploads/submissions/" .
                        $new_filename;


                    // If submission already exists, update it
                    if ($existing_submission) {

                        $update_sql = "
                            UPDATE submissions
                            SET file_path = ?,
                                submitted_at = CURRENT_TIMESTAMP
                            WHERE id = ?
                        ";

                        $update_stmt =
                            mysqli_prepare(
                                $conn,
                                $update_sql
                            );

                        mysqli_stmt_bind_param(
                            $update_stmt,
                            "si",
                            $relative_path,
                            $existing_submission['id']
                        );

                        mysqli_stmt_execute(
                            $update_stmt
                        );

                        $message =
                            "Assignment resubmitted successfully.";

                    } else {

                        // New submission
                        $insert_sql = "
                            INSERT INTO submissions
                            (
                                assignment_id,
                                student_id,
                                file_path
                            )
                            VALUES (?, ?, ?)
                        ";

                        $insert_stmt =
                            mysqli_prepare(
                                $conn,
                                $insert_sql
                            );

                        mysqli_stmt_bind_param(
                            $insert_stmt,
                            "iis",
                            $assignment_id,
                            $student_id,
                            $relative_path
                        );

                        if (
                            mysqli_stmt_execute(
                                $insert_stmt
                            )
                        ) {

                            $message =
                                "Assignment submitted successfully.";

                        } else {

                            $message =
                                "Could not save submission.";

                            $message_type = "danger";
                        }
                    }


                    // Refresh existing submission
                    $check_stmt = mysqli_prepare(
                        $conn,
                        $check_sql
                    );

                    mysqli_stmt_bind_param(
                        $check_stmt,
                        "ii",
                        $assignment_id,
                        $student_id
                    );

                    mysqli_stmt_execute(
                        $check_stmt
                    );

                    $check_result =
                        mysqli_stmt_get_result(
                            $check_stmt
                        );

                    $existing_submission =
                        mysqli_fetch_assoc(
                            $check_result
                        );


                } else {

                    $message =
                        "Could not upload the file.";

                    $message_type = "danger";
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Submit Assignment - University LMS
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body>


<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a
            class="navbar-brand"
            href="dashboard.php"
        >
            University LMS
        </a>

        <a
            href="../logout.php"
            class="btn btn-danger"
        >
            Logout
        </a>

    </div>

</nav>


<div class="container mt-5">


    <a
        href="assignments.php"
        class="btn btn-secondary mb-4"
    >
        ← Back to Assignments
    </a>


    <h2>
        Submit Assignment
    </h2>


    <?php if (!empty($message)): ?>

        <div class="alert alert-<?php echo $message_type; ?>">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <div class="card shadow-sm mt-4">

        <div class="card-body">


            <h3>

                <?php
                echo htmlspecialchars(
                    $assignment['title']
                );
                ?>

            </h3>


            <h6 class="text-primary">

                <?php
                echo htmlspecialchars(
                    $assignment['course_code']
                );
                ?>

                -

                <?php
                echo htmlspecialchars(
                    $assignment['course_name']
                );
                ?>

            </h6>


            <hr>


            <p>

                <strong>
                    Description:
                </strong>

            </p>

            <p>

                <?php

                echo nl2br(
                    htmlspecialchars(
                        $assignment['description']
                    )
                );

                ?>

            </p>


            <p class="text-danger">

                <strong>
                    Due Date:
                </strong>

                <?php

                if (!empty($assignment['due_date'])) {

                    echo date(
                        "d M Y, h:i A",
                        strtotime(
                            $assignment['due_date']
                        )
                    );

                } else {

                    echo "No due date";

                }

                ?>

            </p>


            <?php if ($existing_submission): ?>

                <div class="alert alert-success">

                    You have already submitted this assignment.

                    <br>

                    You can upload a new file below to replace
                    your previous submission.

                </div>

            <?php endif; ?>


            <form
                method="POST"
                enctype="multipart/form-data"
                class="mt-4"
            >


                <div class="mb-3">

                    <label class="form-label">

                        Select Assignment File

                    </label>


                    <input
                        type="file"
                        name="assignment_file"
                        class="form-control"
                        required
                    >


                    <small class="text-muted">

                        Allowed:
                        PDF, DOC, DOCX, TXT, ZIP

                    </small>

                </div>


                <button
                    type="submit"
                    name="submit_assignment"
                    class="btn btn-primary"
                >

                    Submit Assignment

                </button>


            </form>


        </div>

    </div>


</div>


</body>

</html>
```
