<?php
require 'db_connect.php';

// This variable will hold a message to show the user (success or error)
$message = "";

// PHP runs this whole file every time the page loads — whether it's a fresh
// visit, or a form submission. So we need to ASK: "was this a form submit?"
// $_SERVER["REQUEST_METHOD"] tells us how the page was requested.
// A normal visit = "GET". A form submission = "POST" (because our <form> below
// will say method="POST").
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // $_POST is a built-in PHP array that holds every field submitted by the form,
    // keyed by each field's "name" attribute (you'll see those in the HTML below).
    // trim() removes accidental leading/trailing spaces the user typed.
    $amount      = trim($_POST['amount']);
    $category_id = trim($_POST['category_id']);
    $description = trim($_POST['description']);
    $expense_date = trim($_POST['expense_date']);

    // Basic validation — never trust that the user filled things in correctly.
    if ($amount == "" || $category_id == "" || $expense_date == "") {
        $message = "Please fill in amount, category, and date.";
    } else {
        // === PREPARED STATEMENT ===
        // We do NOT build the SQL by joining strings directly (e.g.
        // "INSERT INTO expenses VALUES ('$amount', ...)") because if a user
        // typed SQL code into a field instead of a normal value, that could
        // corrupt or attack your database. This is called "SQL injection."
        //
        // Instead, we write the query with placeholders (?) and let MySQL
        // safely insert the real values afterward. This is the standard,
        // safe way to do it — always use this pattern.

        $sql = "INSERT INTO expenses (user_id, category_id, amount, description, expense_date)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        $user_id = 1; // our placeholder Guest User for now

        // "iidss" tells MySQL the TYPE of each value in order:
        // i = integer, d = decimal, s = string
        // user_id(i), category_id(i), amount(d), description(s), expense_date(s)
        mysqli_stmt_bind_param($stmt, "iidss", $user_id, $category_id, $amount, $description, $expense_date);

        if (mysqli_stmt_execute($stmt)) {
            // Success! Redirect back to index.php so the user sees the updated list.
            // We use header("Location: ...") + exit to do this — this pattern
            // (Post → Redirect → Get) prevents the form from being accidentally
            // re-submitted if the user refreshes the page.
            header("Location: index.php");
            exit;
        } else {
            $message = "Something went wrong: " . mysqli_error($conn);
        }
    }
}

// Fetch categories to fill the dropdown menu
$cat_result = mysqli_query($conn, "SELECT id, name FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Expense</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <h1>Add an Expense</h1>

    <?php if ($message != ""): ?>
        <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- method="POST" means the data will be sent invisibly in the request body,
         not shown in the URL (unlike method="GET"). Good for forms that
         change data. action="add_expense.php" means: send it back to THIS
         same file to be processed (which is the if-block above). -->
    <form method="POST" action="add_expense.php">

        <label for="amount">Amount</label><br>
        <input type="number" step="0.01" name="amount" id="amount" required><br><br>

        <label for="category_id">Category</label><br>
        <select name="category_id" id="category_id" required>
            <?php while ($cat = mysqli_fetch_assoc($cat_result)): ?>
                <option value="<?php echo $cat['id']; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="description">Description</label><br>
        <input type="text" name="description" id="description" placeholder="e.g. Lunch with friends"><br><br>

        <label for="expense_date">Date</label><br>
        <input type="date" name="expense_date" id="expense_date" required><br><br>

        <button type="submit">Save Expense</button>
    </form>

    <p><a href="index.php">← Back to dashboard</a></p>

</body>
</html>
