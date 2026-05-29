<?php

// Shared header template used across all pages.
// This centralises common layout elements to avoid duplication and ensure consistency throughout the application.

if (!isset($pageTitle)) {
    // Provide a default page title if one is not set by the page.
    $pageTitle = APP_NAME;
}

if (!isset($currentPage)) {
    // Track the current page so the correct navigation link can be highlighted.
    // This improves user experience by clearly indicating where the user is.
    $currentPage = '';
}

if (!isset($showNavbar)) {
    // Allow individual pages to control whether the navigation bar is displayed.
    // This is useful for pages such as login/register where navigation is not required.
    $showNavbar = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Define character encoding to ensure correct text rendering -->
    <meta charset="UTF-8">

    <!-- Ensure the layout is responsive on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Dynamically set the page title and escape output to prevent XSS -->
    <title><?php echo e($pageTitle); ?> | <?php echo APP_NAME; ?></title>

    <!-- Include Bootstrap CSS for responsive layout and pre-built UI components -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom stylesheets for application specific design -->
    <link href="css/style.css" rel="stylesheet">

    <!-- Additional responsive styling for smaller screen sizes -->
    <link href="css/responsive.css" rel="stylesheet">

    <!-- Accessibility focused styles -->
    <link href="css/accessibility.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Skip link allows keyboard and screen reader users to bypass navigation -->
    <!-- This improves accessibility by enabling faster navigation to main content -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <?php if ($showNavbar): ?>
        <!-- Main navigation bar displayed on authenticated pages -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <!-- Application name used as a consistent brand and link to dashboard -->
                <a class="navbar-brand fw-bold" href="dashboard.php"><?php echo APP_NAME; ?></a>

                <!-- Responsive toggle button for collapsing navigation on smaller screens -->
                <!-- ARIA attributes are used to improve accessibility for screen readers -->
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#mainNav"
                    aria-controls="mainNav"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navigation links container -->
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav ms-auto">
                        <!-- Each navigation link conditionally applies an 'active' class based on the current page to improve user orientation -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage === 'transactions') ? 'active' : ''; ?>" href="transactions.php">Transactions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage === 'categories') ? 'active' : ''; ?>" href="categories.php">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage === 'reports') ? 'active' : ''; ?>" href="reports.php">Reports</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage === 'account') ? 'active' : ''; ?>" href="my-account.php">My Account</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage === 'keyboard') ? 'active' : ''; ?>" href="keyboard-shortcuts.php">Keyboard Help</a>
                        </li>
                        <li class="nav-item">
                            <!-- Logout link triggers session destruction to securely end the user session -->
                            <a class="nav-link" href="app/logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>