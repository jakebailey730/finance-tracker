<?php
// Display the transactions page for the logged-in user.
// This page loads the user's recorded transactions and categories then presents the data in a table so records can be viewed, edited or deleted from one place.

require_once __DIR__ . '/app/functions.php';

// Restrict access to authenticated users 
requireLogin();

// Load any one time feedback message along with the logged-in user's transactions and categories. 
// Categories are needed so stored category IDs can be converted into readable category names in the table.
$flash = getFlash();
$transactions = getUserTransactions($_SESSION['user_id']);
$categories = getUserCategories($_SESSION['user_id']);

// Set page specific values used by the shared header template.
// This keeps repeated layout and navigation settings centralised.
$pageTitle = 'Transactions';
$currentPage = 'transactions';
$showNavbar = true;
$useBootstrapJs = true;

require_once __DIR__ . '/app/header.php';
?>

<main id="main-content" class="container py-4">
    <!-- Page heading and primary call-to-action help the user quickly
         understand the purpose of the page and add a new transaction -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="fw-bold mb-1">Transactions</h1>
            <p class="text-muted mb-0">View and manage your recorded income and expenses.</p>
        </div>

        <div>
            <a href="add-transactions.php" class="btn btn-primary">Add Transaction</a>
        </div>
    </div>

    <!-- Display a one-time success or error message after actions such as
         adding, updating or deleting a transaction -->
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo e($flash['type']); ?>" role="alert">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <!-- Show a helpful empty-state message when no transaction data exists yet -->
                <p class="mb-3 text-muted">No transactions have been added yet.</p>
                <a href="add-transactions.php" class="btn btn-primary">Add Your First Transaction</a>
            <?php else: ?>
                <!-- Responsive table used so transaction data remains readable
                     and usable across different screen sizes -->
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Item</th>
                                <th scope="col">Category</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Type</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo e($transaction['date']); ?></td>
                                    <td><?php echo e($transaction['item_name']); ?></td>
                                    <td><?php echo e(getCategoryName((int)$transaction['category_id'], $categories)); ?></td>
                                    <td>
                                        <?php echo e($transaction['currency']); ?>
                                        <?php echo number_format((float)$transaction['amount'], 2); ?>
                                    </td>
                                    <td>
                                        <!-- Colour-coded badges provide quick visual distinction
                                             between income and expense transactions -->
                                        <?php if (($transaction['type'] ?? '') === 'Income'): ?>
                                            <span class="badge bg-success">Income</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Expense</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-center">
                                            <!-- Edit action sends the user to a dedicated edit page
                                                 with the selected transaction ID in the query string -->
                                            <a href="edit-transactions.php?id=<?php echo (int)$transaction['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>

                                            <!-- Delete is performed through a POST form rather than a normal link
                                                 because it changes stored data. A confirmation dialog is used
                                                 to reduce the risk of accidental deletion. -->
                                            <form action="app/delete-transaction.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                                                <input type="hidden" name="id" value="<?php echo (int)$transaction['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/app/footer.php'; ?>