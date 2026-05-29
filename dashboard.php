<?php
// Display the main dashboard for the logged-in user.
// This page loads the user's transaction data, calculates summary totals, and presents quick actions for the main areas of the application.

require_once __DIR__ . '/app/functions.php';

// Restrict access to authenticated users only
requireLogin();

// Load the current user's details and any one-time feedback message.
$user = getCurrentUser();
$flash = getFlash();

// Load all transactions for the logged in user and calculate the summary values shown on the dashboard cards.
$transactions = getUserTransactions($_SESSION['user_id']);
$totals = calculateTotals($transactions);

// Set page specific values used by the shared header template.
// This keeps page structure and navigation settings centralised.
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
$showNavbar = true;
$useBootstrapJs = true;

require_once __DIR__ . '/app/header.php';
?>

<main id="main-content" class="container py-4">
    <!-- Page heading welcomes the user and confirms they are on the dashboard -->
    <div class="mb-4">
        <h1 class="fw-bold">Dashboard</h1>
        <p class="text-muted mb-0">
            Welcome back, <?php echo e($user['first_name'] ?? 'User'); ?>.
        </p>
    </div>

    <!-- Display a one-time status message after actions such as login or updates -->
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo e($flash['type']); ?>" role="alert">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <!-- Summary cards provide the user with an immediate overview of their finances.
         Separate cards are used to make the balance, income and expense values easy to scan. -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5 card-title">Total Balance</h2>
                    <p class="display-6 mb-0">
                        <?php echo e($user['currency'] ?? DEFAULT_CURRENCY); ?>
                        <?php echo number_format($totals['balance'], 2); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-body">
                    <h2 class="h5 card-title">Income</h2>
                    <p class="display-6 mb-0 text-success">
                        <?php echo e($user['currency'] ?? DEFAULT_CURRENCY); ?>
                        <?php echo number_format($totals['income'], 2); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100 border-danger">
                <div class="card-body">
                    <h2 class="h5 card-title">Expenses</h2>
                    <p class="display-6 mb-0 text-danger">
                        <?php echo e($user['currency'] ?? DEFAULT_CURRENCY); ?>
                        <?php echo number_format($totals['expense'], 2); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick action links are grouped together to help the user reach
         the most common tasks quickly from the dashboard. -->
    <section class="mb-4" aria-labelledby="quick-actions-heading">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 id="quick-actions-heading" class="h4 mb-3">Quick Actions</h2>

                <div class="d-grid gap-2 d-md-flex">
                    <a href="add-transactions.php" class="btn btn-primary">Add Transaction</a>
                    <a href="transactions.php" class="btn btn-outline-primary">View Transactions</a>
                    <a href="reports.php" class="btn btn-outline-primary">View Reports</a>
                    <a href="categories.php" class="btn btn-outline-primary">Manage Categories</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Account summary gives simple contextual feedback depending on whether
         the user has started adding data yet. This improves usability by guiding
         new users towards the next logical action. -->
    <section aria-labelledby="recent-summary-heading">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 id="recent-summary-heading" class="h4 mb-3">Account Summary</h2>

                <?php if (empty($transactions)): ?>
                    <p class="mb-0 text-muted">
                        No transactions have been added yet. Use the Add Transaction button to get started.
                    </p>
                <?php else: ?>
                    <p class="mb-2">
                        You currently have <strong><?php echo count($transactions); ?></strong> transaction(s) stored.
                    </p>
                    <p class="mb-0 text-muted">
                        Use the navigation above to manage categories, analyse reports, or update your account settings.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/app/footer.php'; ?>