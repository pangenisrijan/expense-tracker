<?php
require 'db_connect.php';

$message = "";

// STEP 1: Figure out WHICH expense we're editing.
// We'll link to this page like: edit_expense.php?id=3
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id = $_GET['id'];

// STEP 2: Handle the form SUBMISSION (this runs only when the user
// clicks "Update Expense" - same REQUEST_METHOD check as add_expense.php).
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount       = trim($_POST['amount']);
    $category_id  = trim($_POST['category_id']);
    $description  = trim($_POST['description']);
    $expense_date = trim($_POST['expense_date']);

    if ($amount == "" || $category_id == "" || $expense_date == "") {
        $message = "Please fill in amount, category, and date.";
    } else {
        // UPDATE works like INSERT but changes an EXISTING row instead of
        // creating a new one. The WHERE clause is critical - without it,
        // this would update EVERY row in the table instead of just one.
        $sql = "UPDATE expenses
                SET amount = ?, category_id = ?, description = ?, expense_date = ?
                WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "dissi", $amount, $category_id, $description, $expense_date, $id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php");
            exit;
        } else {
            $message = "Something went wrong: " . mysqli_error($conn);
        }
    }
}

// STEP 3: Load the CURRENT values of this expense, so the form shows
// what's already saved (instead of a blank form like add_expense.php has).
$sql = "SELECT * FROM expenses WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$expense = mysqli_fetch_assoc($result);

if (!$expense) {
    // No expense with that id exists (e.g. someone typed a random id in the URL)
    header("Location: index.php");
    exit;
}

$cat_result = mysqli_query($conn, "SELECT id, name FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Expense</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <h1>Edit Expense</h1>

    <?php if ($message != ""): ?>
        <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" action="edit_expense.php?id=<?php echo $id; ?>">

        <label for="amount">Amount</label><br>
        <input type="number" step="0.01" name="amount" id="amount"
               value="<?php echo htmlspecialchars($expense['amount']); ?>" required><br><br>

        <label for="category_id">Category</label><br>
        <select name="category_id" id="category_id" required>
            <?php while ($cat = mysqli_fetch_assoc($cat_result)): ?>
                <!-- "selected" pre-picks whichever category this expense currently has -->
                <option value="<?php echo $cat['id']; ?>"
                    <?php if ($cat['id'] == $expense['category_id']) echo "selected"; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="description">Description</label><br>
        <input type="text" name="description" id="description"
               value="<?php echo htmlspecialchars($expense['description']); ?>"><br><br>

        <label for="expense_date">Date</label><br>
        <input type="date" name="expense_date" id="expense_date"
               value="<?php echo htmlspecialchars($expense['expense_date']); ?>" required><br><br>

        <button type="submit">Update Expense</button>
    </form>

    <p><a href="index.php">← Back to dashboard</a></p>

</body>
</html>
