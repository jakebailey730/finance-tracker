<?php
// Display the login page for unauthenticated users.
// This page shows any one-time feedback messages, restores the previously entered email address after a failed login attempt, and submits the
// login form to the authentication handler.

require_once __DIR__ . '/app/functions.php';

// Prevent users who are already logged in from accessing the login page again.
// Redirecting them straight to the dashboard improves user flow and avoids unnecessary access to authentication screens.
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

// Load any flash message and previously entered login data from the session.
// The stored email is used to repopulate the form after a failed login attempt while the session value is cleared immediately so it only appears once.
$flash = getFlash();
$oldLogin = $_SESSION['old_login'] ?? [];
unset($_SESSION['old_login']);

// Set page-specific values used by the shared header template.
// The navigation bar is hidden because it is not needed on the login page.
$pageTitle = 'Login';
$showNavbar = false;
$useBootstrapJs = false;

require_once __DIR__ . '/app/header.php';
?>

<main id="main-content" class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">

            <!-- Page heading introduces the application and makes the purpose
                 of the screen clear to the user -->
            <div class="text-center mb-4">
                <h1 class="fw-bold"><?php echo APP_NAME; ?></h1>
                <p class="text-muted mb-0">Login to manage your finances</p>
            </div>

            <!-- Display a one-time success or error message from the session -->
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo e($flash['type']); ?>" role="alert">
                    <?php echo e($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">Login</h2>

                    <!-- Login form submits to the shared authentication handler.
                         A hidden action field is used so the same processing file
                         can distinguish between login and registration requests. -->
                    <form action="app/auth.php" method="POST" novalidate data-validate="true">
                        <input type="hidden" name="action" value="login">

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                value="<?php echo e($oldLogin['email'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                required
                            >
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>

                        <!-- Provide clear routes for users who need to register
                             or access keyboard accessibility guidance -->
                        <p class="mb-2 text-center">
                            Don’t have an account?
                            <a href="register.php">Register here</a>
                        </p>

                        <p class="mb-0 text-center">
                            <a href="keyboard-shortcuts.php">Keyboard navigation help</a>
                        </p>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require_once __DIR__ . '/app/footer.php'; ?>