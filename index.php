<?php
// index.php - the main dashboard: shows all expenses and a running total.
// Now supports an optional category filter.

require 'db_connect.php';

// STEP 1: Was a filter selected?
// This form will use method="GET", so filter choices show up in $_GET,
// not $_POST. GET is the right choice here (rather than POST) because a
// filter is just "asking to view something" - not changing any data -
// and GET means the choice shows up in the URL, so the page is bookmarkable
// and shareable, e.g. index.php?category_id=2
$selected_category = isset($_GET['category_id']) ? $_GET['category_id'] : "";

// STEP 2: Build the query dynamically based on whether a filter was chosen.
// Base query is the same JOIN as before...
$sql = "SELECT expenses.id, expenses.amount, expenses.description, expenses.expense_date,
               categories.name AS category_name
        FROM expenses
        JOIN categories ON expenses.category_id = categories.id";

// ...but if a category was picked, we add a WHERE clause and use a
// prepared statement again (same SQL-injection-safety reason as before -
// even though this comes from a dropdown we built, never trust input blindly).
if ($selected_category != "") {
    $sql .= " WHERE expenses.category_id = ? ORDER BY expenses.expense_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $selected_category);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql .= " ORDER BY expenses.expense_date DESC";
    $result = mysqli_query($conn, $sql);
}

$count = mysqli_num_rows($result);

$total = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $total += $row['amount'];
}
mysqli_data_seek($result, 0);

// For the filter dropdown itself, we always need the FULL category list
// (not the filtered one) - otherwise you couldn't switch back to a
// category you're not currently viewing.
$cat_result = mysqli_query($conn, "SELECT id, name FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Tracker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <h1>Expense Tracker</h1>

    <p><a href="add_expense.php">+ Add a new expense</a></p>

    <!-- method="GET" (default, but written explicitly) - filter choices go
         into the URL, e.g. index.php?category_id=3 -->
    <form method="GET" action="index.php">
        <label for="category_id">Filter by category:</label>
        <select name="category_id" id="category_id" onchange="this.form.submit()">
            <option value="">All categories</option>
            <?php
            // Rewind category result each time this runs (safe since it's a fresh query)
            while ($cat = mysqli_fetch_assoc($cat_result)):
            ?>
                <option value="<?php echo $cat['id']; ?>"
                    <?php if ($cat['id'] == $selected_category) echo "selected"; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <!-- onchange="this.form.submit()" above means: the moment you pick a
             different option, JavaScript submits the form automatically -
             no separate "Apply" button needed. -->
    </form>
    <br>

    <?php if ($count > 0): ?>
        <p><strong>Total spent: NPR <?php echo number_format($total, 2); ?></strong> across <?php echo $count; ?> expense(s)</p>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['expense_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo number_format($row['amount'], 2); ?></td>
                        <td>
                            <a href="edit_expense.php?id=<?php echo $row['id']; ?>">Edit</a>
                            &nbsp;|&nbsp;
                            <a href="delete_expense.php?id=<?php echo $row['id']; ?>"
                               onclick="return confirm('Delete this expense?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No expenses match this filter. <a href="index.php">Clear filter</a> or <a href="add_expense.php">add a new expense</a>.</p>
    <?php endif; ?>

</body>
</html>
