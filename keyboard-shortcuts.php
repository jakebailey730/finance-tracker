<?php
// Display the keyboard navigation help page.
// This page explains how users can interact with the application without a mouse, supporting accessibility requirements such as keyboard-only navigation.

require_once __DIR__ . '/app/functions.php';

// Restrict access to authenticated users.
requireLogin();

// Load any one-time feedback message from the session.
$flash = getFlash();

// Set page-specific configuration for the shared header template.
$pageTitle = 'Keyboard Help';
$currentPage = 'keyboard';
$showNavbar = true;
$useBootstrapJs = true;

require_once __DIR__ . '/app/header.php';
?>

<main id="main-content" class="container py-4">
    <!-- Page heading introduces the purpose of the page clearly -->
    <div class="mb-4">
        <h1 class="fw-bold mb-1">Keyboard Navigation Help</h1>
        <p class="text-muted mb-0">
            This page explains how to use the finance tracker without a mouse.
        </p>
    </div>

    <!-- Display a one-time flash message if present -->
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo e($flash['type']); ?>" role="alert">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Basic Keyboard Controls</h2>

                    <!-- Table used to clearly present key actions and their behaviour.
                         This structured format improves readability and accessibility. -->
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">Key</th>
                                    <th scope="col">What it does</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><kbd>Tab</kbd></td>
                                    <td>Move to the next link, button, or form field.</td>
                                </tr>
                                <tr>
                                    <td><kbd>Shift</kbd> + <kbd>Tab</kbd></td>
                                    <td>Move to the previous link, button, or form field.</td>
                                </tr>
                                <tr>
                                    <td><kbd>Enter</kbd></td>
                                    <td>Activate a selected button or link, or submit a form.</td>
                                </tr>
                                <tr>
                                    <td><kbd>Space</kbd></td>
                                    <td>Activate some buttons, checkboxes, and collapsible controls.</td>
                                </tr>
                                <tr>
                                    <td><kbd>Arrow Keys</kbd></td>
                                    <td>Move through some menus, select lists, and browser controls.</td>
                                </tr>
                                <tr>
                                    <td><kbd>Esc</kbd></td>
                                    <td>Close some open menus or dismiss browser popups where supported.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">How to Use This Application</h2>

                    <!-- Step-by-step guidance helps users understand how to navigate
                         key areas of the application using only the keyboard -->
                    <ul class="mb-0">
                        <li class="mb-2">Use <kbd>Tab</kbd> to move through the navigation menu at the top of each page.</li>
                        <li class="mb-2">Press <kbd>Enter</kbd> on a navigation link to open that page.</li>
                        <li class="mb-2">On forms such as Register, Add Transaction, and My Account, use <kbd>Tab</kbd> to move between inputs.</li>
                        <li class="mb-2">Use <kbd>Enter</kbd> on the submit button to save changes.</li>
                        <li class="mb-2">Use the “Skip to main content” link at the top of each logged-in page to move past repeated navigation more quickly.</li>
                        <li class="mb-0">If a validation message appears, use <kbd>Shift</kbd> + <kbd>Tab</kbd> to move back to the form field and correct it.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h4 mb-3">Accessibility Support</h2>

                    <!-- Explain the accessibility features built into the application. -->
                    <p>
                        This application has been built to support keyboard navigation and screen readers.
                        Form inputs use visible labels, page headings follow a clear structure, and navigation
                        remains consistent throughout the application.
                    </p>

                    <p class="mb-0">
                        Focus styles remain visible while tabbing so users can clearly see which
                        interactive element is currently selected.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/app/footer.php'; ?>