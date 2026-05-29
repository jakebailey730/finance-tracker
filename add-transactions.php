<?php
// Display the add transaction page for authenticated users.
// This page loads the current user's categories, restores old form input after validation errors, and builds a form for creating new transactions.

require_once __DIR__ . '/app/functions.php';

// Restrict access to logged-in users only because this page allows
requireLogin();

// Load shared page data needed for rendering the form and feedback messages.
$user = getCurrentUser();
$flash = getFlash();
$categories = getUserCategories($_SESSION['user_id']);

// Restore previously submitted form data if validation failed on the last request.
// The session value is cleared immediately after reading so it only persists once.
$oldInput = $_SESSION['old_transaction'] ?? [];
unset($_SESSION['old_transaction']);

// Attempt to find a category named "Income" so it can be used by the client-side 
// form behaviour to auto-select the most appropriate category for income entries.
$incomeCategoryId = '';

foreach ($categories as $category) {
    if (strtolower(trim($category['name'])) === 'income') {
        $incomeCategoryId = (string)$category['id'];
        break;
    }
}

// Set page specific values used by the shared header template.
// This keeps layout configuration centralised and avoids repeating header markup.
$pageTitle = 'Add Transaction';
$currentPage = 'transactions';
$showNavbar = true;
$useBootstrapJs = true;

require_once __DIR__ . '/app/header.php';
?>

<main id="main-content" class="container py-4">
    <!-- Page heading introduces the purpose of the screen clearly for usability and accessibility -->
    <div class="mb-4">
        <h1 class="fw-bold mb-1">Add Transaction</h1>
        <p class="text-muted mb-0">Record a new income or expense transaction.</p>
    </div>

    <!-- Display a one-time success or error message stored in the session -->
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo e($flash['type']); ?>" role="alert">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <?php if (empty($categories)): ?>
                <!-- Prevent transaction creation if the user has no categories available.
                     This avoids invalid records being created without a category relationship. -->
                <div class="alert alert-warning mb-0" role="alert">
                    You need to create at least one category before adding a transaction.
                    <a href="categories.php" class="alert-link">Go to Categories</a>
                </div>
            <?php else: ?>
                <!-- Transaction creation form.
                     novalidate is used so validation feedback can be handled in a more controlled way while data-validate provides a hook for custom client-side validation behaviour. -->
                <form action="app/save-transaction.php" method="POST" novalidate data-validate="true">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="date" class="form-label">Date</label>
                            <input
                                type="date"
                                class="form-control"
                                id="date"
                                name="date"
                                value="<?php echo e($oldInput['date'] ?? ''); ?>"
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
                                value="<?php echo e($oldInput['item_name'] ?? ''); ?>"
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
                                        <?php echo (($oldInput['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>
                                    >
                                        <?php echo e($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <?php if ($incomeCategoryId === ''): ?>
                                <!-- Guide the user towards the optional naming convention that enables automatic category selection for income entries. -->
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
                                value="<?php echo e($oldInput['amount'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select" id="currency" name="currency" required>
                                <?php foreach (VALID_CURRENCIES as $currency): ?>
                                    <option
                                        value="<?php echo e($currency); ?>"
                                        <?php echo (($oldInput['currency'] ?? ($user['currency'] ?? DEFAULT_CURRENCY)) === $currency) ? 'selected' : ''; ?>
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
                                        <?php echo (($oldInput['type'] ?? '') === $type) ? 'selected' : ''; ?>
                                    >
                                        <?php echo e($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Primary and secondary actions are grouped together for clear form flow -->
                    <div class="d-flex flex-column flex-md-row gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Save Transaction</button>
                        <a href="transactions.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/app/footer.php'; ?>