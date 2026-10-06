<?php

include "includes/db.php";

$email = "admin@university.com";
$password = "admin123";

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "UPDATE users
        SET password = ?, role = 'admin'
        WHERE email = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $hashed_password,
    $email
);

if (mysqli_stmt_execute($stmt)) {

    if (mysqli_stmt_affected_rows($stmt) > 0) {

        echo "Admin password reset successfully.<br>";
        echo "Email: admin@university.com<br>";
        echo "Password: admin123";

    } else {

        echo "Admin account was not found.";
    }

} else {

    echo "Error: " . mysqli_error($conn);
}

?>