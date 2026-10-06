<?php

session_start();

include "../includes/db.php";

// Only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit;
}

$message = "";

// ADD DEPARTMENT
if (isset($_POST['add_department'])) {

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (empty($name)) {

        $message = "Department name is required.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO departments (name, description)
             VALUES (?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $name,
            $description
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Department added successfully.";

        } else {

            $message = "Failed to add department.";
        }
    }
}


// DELETE DEPARTMENT
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM departments WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);

    mysqli_stmt_execute($stmt);

    header("Location: departments.php");
    exit;
}


// GET ALL DEPARTMENTS
$result = mysqli_query(
    $conn,
    "SELECT * FROM departments ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Departments - University LMS</title>

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

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>Department Management</h2>

        <a href="dashboard.php"
           class="btn btn-secondary">

            Back to Dashboard

        </a>

    </div>


    <?php if ($message != ""): ?>

        <div class="alert alert-info">

            <?php echo $message; ?>

        </div>

    <?php endif; ?>


    <!-- ADD DEPARTMENT -->

    <div class="card shadow mb-4">

        <div class="card-body">

            <h4 class="mb-3">
                Add New Department
            </h4>

            <form method="POST">

                <div class="mb-3">

                    <label class="form-label">
                        Department Name
                    </label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           placeholder="e.g. Software Engineering"
                           required>

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Description
                    </label>

                    <textarea name="description"
                              class="form-control"
                              rows="3"
                              placeholder="Department description"></textarea>

                </div>


                <button type="submit"
                        name="add_department"
                        class="btn btn-primary">

                    Add Department

                </button>

            </form>

        </div>

    </div>


    <!-- DEPARTMENT LIST -->

    <div class="card shadow">

        <div class="card-body">

            <h4 class="mb-3">
                All Departments
            </h4>


            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>

                            <th>Department Name</th>

                            <th>Description</th>

                            <th>Created</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                        <tr>

                            <td>
                                <?php echo $row['id']; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['description']); ?>
                            </td>

                            <td>
                                <?php echo $row['created_at']; ?>
                            </td>

                            <td>

                                <a href="edit_department.php?id=<?php echo $row['id']; ?>"
                                   class="btn btn-sm btn-warning">

                                    Edit

                                </a>

                                <a href="departments.php?delete=<?php echo $row['id']; ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this department?');">

                                    Delete

                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>

</html>