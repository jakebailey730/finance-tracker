<?php
// Display the registration page for new users.
// This page shows any one-time feedback messages, restores previously entered registration values after validation errors, and submits the
// registration form to the shared authentication handler.

require_once __DIR__ . '/app/functions.php';

// Prevent users who are already logged in from accessing the registration page.
// Redirecting authenticated users to the dashboard
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

// Load any flash message and previously entered registration data from the session.
// Old form values are used to repopulate the form after a failed submission while the session value is cleared immediately so it is only shown once.
$flash = getFlash();
$oldRegister = $_SESSION['old_register'] ?? [];
unset($_SESSION['old_register']);

// Set page-specific values used by the shared header template.
// The navigation bar is hidden because it is not needed during registration.
$pageTitle = 'Register';
$showNavbar = false;
$useBootstrapJs = false;

require_once __DIR__ . '/app/header.php';
?>

<main id="main-content" class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">

            <!-- Page heading introduces the application and clearly identifies
                 that this screen is for account creation -->
            <div class="text-center mb-4">
                <h1 class="fw-bold"><?php echo APP_NAME; ?></h1>
                <p class="text-muted mb-0">Create your account</p>
            </div>

            <!-- Display a one-time success or error message from the session -->
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo e($flash['type']); ?>" role="alert">
                    <?php echo e($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">Register</h2>

                    <!-- Registration form submits to the shared authentication handler.
                         A hidden action field is used so the same processing script
                         can distinguish between registration and login requests. -->
                    <form action="app/auth.php" method="POST" novalidate data-validate="true">
                        <input type="hidden" name="action" value="register">

                        <div class="mb-3">
                            <label for="first_name" class="form-label">First name</label>
                            <input
                                type="text"
                                class="form-control"
                                id="first_name"
                                name="first_name"
                                value="<?php echo e($oldRegister['first_name'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last name</label>
                            <input
                                type="text"
                                class="form-control"
                                id="last_name"
                                name="last_name"
                                value="<?php echo e($oldRegister['last_name'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                value="<?php echo e($oldRegister['email'] ?? ''); ?>"
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
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>

                        <!-- Provide a clear route back to the login page for existing users -->
                        <p class="mb-0 text-center">
                            Already have an account?
                            <a href="index.php">Login here</a>
                        </p>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require_once __DIR__ . '/app/footer.php'; ?>