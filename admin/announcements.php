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

$admin_id = $_SESSION['user_id'];


/* =========================
   ADD ANNOUNCEMENT
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['add_announcement'])
) {

    $title = trim($_POST['title']);
    $message = trim($_POST['message']);

    if (empty($title) || empty($message)) {

        $error = "Title and message are required.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO announcements
            (title, message, created_by)
            VALUES (?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $title,
            $message,
            $admin_id
        );

        if (mysqli_stmt_execute($stmt)) {

            header("Location: announcements.php");
            exit;

        } else {

            $error = "Failed to create announcement.";

        }
    }
}


/* =========================
   DELETE ANNOUNCEMENT
========================= */

if (isset($_GET['delete'])) {

    $announcement_id = intval($_GET['delete']);

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM announcements
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $announcement_id
    );

    mysqli_stmt_execute($stmt);

    header("Location: announcements.php");
    exit;
}


/* =========================
   GET ANNOUNCEMENTS
========================= */

$result = mysqli_query(
    $conn,
    "
    SELECT
        announcements.id,
        announcements.title,
        announcements.message,
        announcements.created_at,
        users.full_name AS created_by_name

    FROM announcements

    LEFT JOIN users
        ON announcements.created_by = users.id

    ORDER BY announcements.created_at DESC
    "
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Announcements - University LMS</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <a
            class="navbar-brand"
            href="dashboard.php"
        >
            University LMS
        </a>


        <div>

            <a
                href="dashboard.php"
                class="btn btn-light btn-sm"
            >
                Dashboard
            </a>


            <a
                href="../logout.php"
                class="btn btn-danger btn-sm"
            >
                Logout
            </a>

        </div>

    </div>

</nav>


<!-- =========================
     MAIN CONTENT
========================= -->

<div class="container py-5">


    <h2 class="mb-4">
        Manage Announcements
    </h2>


    <!-- ERROR -->

    <?php if (isset($error)): ?>

        <div class="alert alert-danger">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <!-- =========================
         CREATE ANNOUNCEMENT
    ========================= -->

    <div class="card shadow-sm mb-5">

        <div class="card-header bg-primary text-white">

            <h4 class="mb-0">
                Create Announcement
            </h4>

        </div>


        <div class="card-body">

            <form method="POST">


                <div class="mb-3">

                    <label class="form-label">
                        Announcement Title
                    </label>


                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        placeholder="Example: Mid-Term Exam Schedule"
                        required
                    >

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Message
                    </label>


                    <textarea
                        name="message"
                        class="form-control"
                        rows="5"
                        placeholder="Write announcement here..."
                        required
                    ></textarea>

                </div>


                <button
                    type="submit"
                    name="add_announcement"
                    class="btn btn-success"
                >
                    Publish Announcement
                </button>


            </form>

        </div>

    </div>


    <!-- =========================
         ANNOUNCEMENT LIST
    ========================= -->

    <div class="card shadow-sm">

        <div class="card-header bg-dark text-white">

            <h4 class="mb-0">
                All Announcements
            </h4>

        </div>


        <div class="card-body">


            <?php if (mysqli_num_rows($result) > 0): ?>


                <?php while (
                    $announcement =
                    mysqli_fetch_assoc($result)
                ): ?>


                    <div class="border rounded p-3 mb-3 bg-white">


                        <div
                            class="d-flex justify-content-between align-items-start"
                        >


                            <div>

                                <h5>

                                    <?php

                                    echo htmlspecialchars(
                                        $announcement['title']
                                    );

                                    ?>

                                </h5>


                                <small class="text-muted">

                                    Posted by:

                                    <?php

                                    echo htmlspecialchars(
                                        $announcement['created_by_name']
                                        ?? 'Admin'
                                    );

                                    ?>

                                    |

                                    <?php

                                    echo date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $announcement['created_at']
                                        )
                                    );

                                    ?>

                                </small>

                            </div>


                            <!-- =========================
                                 ACTION BUTTONS
                            ========================= -->

                            <div>

                                <!-- EDIT BUTTON -->

                                <a
                                    href="edit_announcement.php?id=<?php echo $announcement['id']; ?>"
                                    class="btn btn-warning btn-sm"
                                >
                                    Edit
                                </a>


                                <!-- DELETE BUTTON -->

                                <a
                                    href="announcements.php?delete=<?php echo $announcement['id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this announcement?');"
                                >
                                    Delete
                                </a>

                            </div>


                        </div>


                        <hr>


                        <p class="mb-0">

                            <?php

                            echo nl2br(
                                htmlspecialchars(
                                    $announcement['message']
                                )
                            );

                            ?>

                        </p>


                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <div class="alert alert-info mb-0">

                    No announcements have been created yet.

                </div>


            <?php endif; ?>


        </div>

    </div>


</div>


</body>

</html>

