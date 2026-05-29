<?php
// Display the account management page for the logged-in user.
// This page loads the current user's details, shows any one-time feedback message, and provides a form for updating account information such as name and default currency.

require_once __DIR__ . '/app/functions.php';

// Restrict access to authenticated users only
requireLogin();

// Load the current user's record and any flash message stored in the session.
$user = getCurrentUser();
$flash = getFlash();

// Stop the page loading if the user record cannot be retrieved.
// This acts as a safeguard against invalid session data or missing account records.
if (!$user) {
    setFlash('danger', 'Unable to load account details.');
    redirect('index.php');
}

// Set page-specific values used by the shared header template.
// This keeps repeated layout and navigation settings centralised.
$pageTitle = 'My Account';
$currentPage = 'account';
$showNavbar = true;
$useBootstrapJs = true;

require_once __DIR__ . '/app/header.php';
?>

<main id="main-content" class="container py-4">
    <!-- Page heading clearly introduces the purpose of the screen -->
    <div class="mb-4">
        <h1 class="fw-bold mb-1">My Account</h1>
        <p class="text-muted mb-0">View and update your account details and default currency.</p>
    </div>

    <!-- Display a one time success or error message after account actions -->
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo e($flash['type']); ?>" role="alert">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3">Update Account Details</h2>

                    <!-- Account update form pre-filled with the current user's details.
                         Pre populating values improves usability by allowing the user
                         to edit existing information rather than re-enter everything. -->
                    <form action="app/update-account.php" method="POST" novalidate data-validate="true">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="first_name"
                                    name="first_name"
                                    value="<?php echo e($user['first_name']); ?>"
                                    required
                                >
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="last_name"
                                    name="last_name"
                                    value="<?php echo e($user['last_name']); ?>"
                                    required
                                >
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="currency" class="form-label">Default Currency</label>
                                <select class="form-select" id="currency" name="currency" required>
                                    <?php foreach (VALID_CURRENCIES as $currency): ?>
                                        <option
                                            value="<?php echo e($currency); ?>"
                                            <?php echo ($user['currency'] === $currency) ? 'selected' : ''; ?>
                                        >
                                            <?php echo e($currency); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Account Summary</h2>

                    <!-- Summary panel provides a quick read-only overview of the
                         currently stored account information alongside the edit form -->
                    <p class="mb-2"><strong>Name:</strong> <?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    <p class="mb-2"><strong>Email:</strong> <?php echo e($user['email']); ?></p>
                    <p class="mb-0"><strong>Default Currency:</strong> <?php echo e($user['currency']); ?></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/app/footer.php'; ?>