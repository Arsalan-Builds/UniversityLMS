```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "../includes/db.php";

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit;
}

// Check announcement ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: announcements.php");
    exit;
}

$id = intval($_GET['id']);

$error = "";


// ======================================
// GET ANNOUNCEMENT
// ======================================

$sql = "
    SELECT *
    FROM announcements
    WHERE id = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$announcement = mysqli_fetch_assoc($result);

if (!$announcement) {
    die("Announcement not found.");
}


// ======================================
// UPDATE ANNOUNCEMENT
// ======================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST['title']);
    $message = trim($_POST['message']);


    if (empty($title) || empty($message)) {

        $error = "Please fill all required fields.";

    } else {

        $update_sql = "
            UPDATE announcements
            SET
                title = ?,
                message = ?
            WHERE id = ?
        ";

        $update_stmt = mysqli_prepare(
            $conn,
            $update_sql
        );

        mysqli_stmt_bind_param(
            $update_stmt,
            "ssi",
            $title,
            $message,
            $id
        );


        if (mysqli_stmt_execute($update_stmt)) {

            header("Location: announcements.php");
            exit;

        } else {

            $error = "Failed to update announcement: " .
                     mysqli_error($conn);
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

    <title>Edit Announcement - University LMS</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<div class="container mt-5">


    <div class="card shadow">


        <!-- HEADER -->

        <div class="card-header bg-primary text-white">

            <h3 class="mb-0">
                Edit Announcement
            </h3>

        </div>


        <!-- BODY -->

        <div class="card-body">


            <!-- ERROR MESSAGE -->

            <?php if (!empty($error)): ?>

                <div class="alert alert-danger">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form method="POST">


                <!-- TITLE -->

                <div class="mb-3">

                    <label class="form-label">
                        Announcement Title *
                    </label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        value="<?php
                            echo htmlspecialchars(
                                $announcement['title']
                            );
                        ?>"
                        required
                    >

                </div>


                <!-- MESSAGE -->

                <div class="mb-3">

                    <label class="form-label">
                        Announcement Message *
                    </label>

                    <textarea
                        name="message"
                        class="form-control"
                        rows="7"
                        required
                    ><?php

                    echo htmlspecialchars(
                        $announcement['message']
                    );

                    ?></textarea>

                </div>


                <!-- BUTTONS -->

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Update Announcement
                </button>


                <a
                    href="announcements.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>


            </form>

        </div>

    </div>

</div>


</body>

</html>
```
