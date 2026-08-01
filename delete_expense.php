<?php
require 'db_connect.php';

// $_GET holds values passed in the URL itself (after a ?).
// We'll link to this file like: delete_expense.php?id=5
// So $_GET['id'] will contain "5".
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepared statement again - same safety reason as the INSERT earlier.
    // Even though this value comes from a link WE created (not a form the
    // user typed into), it's still good practice to never trust input blindly.
    $sql = "DELETE FROM expenses WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id); // "i" = integer
    mysqli_stmt_execute($stmt);
}

header("Location: index.php");
exit;
