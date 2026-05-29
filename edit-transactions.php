<?php
// Display the edit transaction page for the logged-in user.
// This page validates the requested transaction ID, loads the existing transaction data, restores old form input after validation errors,
// and builds a form for updating the selected transaction.

require_once __DIR__ . '/app/functions.php';

// Restrict access to authenticated users only
requireLogin();

// Load the current flash message and the logged-in user's categories.
// Categories are needed to populate the category dropdown in the edit form.
$flash = getFlash();
$categories = getUserCategories($_SESSION['user_id']);

// Read the transaction ID from the query string.
// The page uses this ID to identify which transaction should be edited.
$transactionId = $_GET['id'] ?? '';

// Validate that a transaction ID was provided and that it is numeric.
// This helps prevent invalid or tampered requests from being processed.
if ($transactionId === '' || !is_numeric($transactionId)) {
    setFlash('danger', 'Invalid transaction selected.');
    redirect('transactions.php');
}

// Load the selected transaction and ensure it belongs to the logged-in user.
// This prevents users from accessing or editing another user's data.
$transaction = findTransactionById((int)$transactionId, (int)$_SESSION['user_id']);

if (!$transaction) {
    setFlash('danger', 'Transaction not found.');
    redirect('transactions.php');
}

// Restore previously submitted form values if validation failed during the last update attempt.
// The session value is cleared after reading so it is only used once.
$oldInput = $_SESSION['old_transaction'] ?? [];
unset($_SESSION['old_transaction']);

// Build a single form data array so the form can be pre filled using either the previously submitted values or the original transaction values.
// This keeps the form logic cleaner and improves maintainability.
$formData = [
    'id' => $transaction['id'],
    'date' => $oldInput['date'] ?? $transaction['date'],
    'item_name' => $oldInput['item_name'] ?? $transaction['item_name'],
    'category_id' => $oldInput['category_id'] ?? $transaction['category_id'],
    'amount' => $oldInput['amount'] ?? $transaction['amount'],
    'currency' => $oldInput['currency'] ?? $transaction['currency'],
    'type' => $oldInput['type'] ?? $transaction['type']
];

// Attempt to find a category named "Income" so client-side behaviour can optionally auto select it for income transactions.
$incomeCategoryId = '';

foreach ($categories as $category) {
    if (strtolower(trim($category['name'])) === 'income') {
        $incomeCategoryId = (string)$category['id'];
        break;
    }
}

// Set page-specific values used by the shared header template.
$pageTitle = 'Edit Transaction';
$currentPage = 'transactions';
$showNavbar = true;
$useBootstrapJs = true;

require_once __DIR__ . '/app/header.php';
?>

<main id="main-content" class="container py-4">
    <!-- Page heading clearly identifies the purpose of the screen -->
    <div class="mb-4">
        <h1 class="fw-bold mb-1">Edit Transaction</h1>
        <p class="text-muted mb-0">Update the details of an existing transaction.</p>
    </div>

    <!-- Display a one-time success or error message from the session -->
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo e($flash['type']); ?>" role="alert">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <?php if (empty($categories)): ?>
                <!-- Prevent editing if no categories are available, as the transaction
                     would not be able to maintain a valid category relationship -->
                <div class="alert alert-warning mb-0" role="alert">
                    You need at least one category before editing a transaction.
                    <a href="categories.php" class="alert-link">Go to Categories</a>
                </div>
            <?php else: ?>
                <!-- Transaction update form.
                     A hidden ID field is used so the submitted form updates the correct record.
                     novalidate and data-validate allow validation behaviour to be controlled consistently. -->
                <form action="app/update-transaction.php" method="POST" novalidate data-validate="true">
                    <input type="hidden" name="id" value="<?php echo (int)$formData['id']; ?>">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="date" class="form-label">Date</label>
                            <input
                                type="date"
                                class="form-control"
                                id="date"
                                name="date"
                                value="<?php echo e($formData['date']); ?>"
                                required
                            >
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="item_name" class="form-label">Item Name</label>
                            <input
                                type="text"
                                class="form-control"
                                id="item_name"
                                name="item_name"
                                value="<?php echo e($formData['item_name']); ?>"
                                required
                            >
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="category_id" class="form-label">Category</label>
                            <select
                                class="form-select"
                                id="category_id"
                                name="category_id"
                                data-income-id="<?php echo e($incomeCategoryId); ?>"
                                required
                            >
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option
                                        value="<?php echo (int)$category['id']; ?>"
                                        <?php echo ((string)$formData['category_id'] === (string)$category['id']) ? 'selected' : ''; ?>
                                    >
                                        <?php echo e($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <?php if ($incomeCategoryId === ''): ?>
                                <!-- Explain the optional naming convention that supports
                                     automatic income category selection -->
                                <div class="form-text">
                                    To auto-select a category for income transactions, create a category named "Income".
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="amount" class="form-label">Amount</label>
                            <input
                                type="number"
                                class="form-control"
                                id="amount"
                                name="amount"
                                step="0.01"
                                min="0"
                                value="<?php echo e($formData['amount']); ?>"
                                required
                            >
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select" id="currency" name="currency" required>
                                <?php foreach (VALID_CURRENCIES as $currency): ?>
                                    <option
                                        value="<?php echo e($currency); ?>"
                                        <?php echo ($formData['currency'] === $currency) ? 'selected' : ''; ?>
                                    >
                                        <?php echo e($currency); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="type" class="form-label">Transaction Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Select a type</option>
                                <?php foreach (VALID_TRANSACTION_TYPES as $type): ?>
                                    <option
                                        value="<?php echo e($type); ?>"
                                        <?php echo ($formData['type'] === $type) ? 'selected' : ''; ?>
                                    >
                                        <?php echo e($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Group the main submit action and cancel action together
                         to make the edit flow clear and easy to use -->
                    <div class="d-flex flex-column flex-md-row gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Update Transaction</button>
                        <a href="transactions.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/app/footer.php'; ?>