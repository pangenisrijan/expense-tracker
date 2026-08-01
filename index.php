<?php
// index.php - the main dashboard: shows all expenses and a running total.

require 'db_connect.php';

// === JOIN explained ===
// Your expenses table only stores category_id (a number, e.g. 3).
// It does NOT store the category's actual name ("Rent") - that lives in
// the categories table. A JOIN lets us combine rows from both tables
// in a single query, matching them where expenses.category_id = categories.id.
// Without a JOIN, you'd have to run a separate query per row - JOIN does it
// in one trip to the database, which is both simpler and much faster.

$sql = "SELECT expenses.id, expenses.amount, expenses.description, expenses.expense_date,
               categories.name AS category_name
        FROM expenses
        JOIN categories ON expenses.category_id = categories.id
        ORDER BY expenses.expense_date DESC";

$result = mysqli_query($conn, $sql);
$count = mysqli_num_rows($result);

// Calculate the total. We loop through once here just to sum amounts;
// we'll loop through result again below to print rows, so we reset
// the pointer back to the start with mysqli_data_seek.
$total = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $total += $row['amount'];
}
mysqli_data_seek($result, 0); // rewind so the display loop below starts from row 0 again
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
        <p>No expenses yet. <a href="add_expense.php">Add your first one.</a></p>
    <?php endif; ?>

</body>
</html>
