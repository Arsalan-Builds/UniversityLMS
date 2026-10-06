<?php

session_start();

include "../includes/db.php";

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit;
}

// Get department ID
if (!isset($_GET['id'])) {
    header("Location: departments.php");
    exit;
}

$id = intval($_GET['id']);


// Update department
if (isset($_POST['update_department'])) {

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (!empty($name)) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE departments
             SET name = ?, description = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $name,
            $description,
            $id
        );

        mysqli_stmt_execute($stmt);

        header("Location: departments.php");
        exit;
    }
}


// Get current department
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM departments WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$department = mysqli_fetch_assoc($result);

if (!$department) {
    header("Location: departments.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Department - University LMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a class="navbar-brand"
           href="dashboard.php">

            University LMS - Admin

        </a>

        <a href="../logout.php"
           class="btn btn-light">

            Logout

        </a>

    </div>

</nav>


<div class="container mt-5">

    <div class="card shadow">

        <div class="card-body">

            <h2 class="mb-4">
                Edit Department
            </h2>

            <form method="POST">

                <div class="mb-3">

                    <label class="form-label">
                        Department Name
                    </label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           value="<?php echo htmlspecialchars($department['name']); ?>"
                           required>

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Description
                    </label>

                    <textarea name="description"
                              class="form-control"
                              rows="4"><?php echo htmlspecialchars($department['description']); ?></textarea>

                </div>


                <button type="submit"
                        name="update_department"
                        class="btn btn-success">

                    Update Department

                </button>

                <a href="departments.php"
                   class="btn btn-secondary">

                    Cancel

                </a>

            </form>

        </div>

    </div>

</div>

</body>

</html>