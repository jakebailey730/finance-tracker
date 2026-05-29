<?php

// This file acts as a shared footer template that is included across multiple pages.
// Using a reusable component reduces duplication and ensures consistent layout and styling throughout the application.

if (!isset($useBootstrapJs)) {
    // Provide a default value for whether Bootstrap JavaScript should be loaded.
    // This allows individual pages to control whether they require Bootstrap's interactive components, avoiding unnecessary scripts
    // being loaded on pages that do not need them.
    $useBootstrapJs = false;
}
?>

    <footer class="text-center py-4 mt-5">
        <div class="container">
            <!-- Display the current year dynamically so it does not need to be updated manually -->
            <!-- APP_NAME is used as a constant to keep branding consistent across all pages -->
            <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
        </div>
    </footer>

    <?php if ($useBootstrapJs): ?>
        <!-- Load Bootstrap's JavaScript bundle only when required.
             This improves performance by reducing unnecessary script loading
             and keeps the page lightweight where interactive components are not used. -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php endif; ?>

    <!-- Load the main JavaScript file for custom client-side functionality.
         This is included globally so shared behaviours are available across the application. -->
    <script src="js/main.js"></script>
</body>
</html>