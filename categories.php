<?php
// Display the categories management page for authenticated users.
// This page allows users to create, edit and delete their own transaction categories while also showing one-time feedback messages.

require_once __DIR__ . '/app/functions.php';

// Restrict access to logged-in users
requireLogin();

// Load the current flash message and the logged-in user's categories.
// Previously submitted category input is also restored if validation failed.
$flash = getFlash();
$categories = getUserCategories($_SESSION['user_id']);
$oldCategory = $_SESSION['old_category'] ?? [];
unset($_SESSION['old_category']);

// Set page-specific values for the shared header template.
// This keeps layout and navigation configuration centralised.
$pageTitle = 'Categories';
$currentPage = 'categories';
$showNavbar = true;
$useBootstrapJs = true;

require_once __DIR__ . '/app/header.php';
?>

<main id="main-content" class="container py-4">
    <!-- Page heading clearly introduces the purpose of this screen -->
    <div class="mb-4">
        <h1 class="fw-bold mb-1">Categories</h1>
        <p class="text-muted mb-0">Create and manage your transaction categories.</p>
    </div>

    <!-- Display a one-time success or error message after form actions -->
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo e($flash['type']); ?>" role="alert">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h4 mb-3">Add Category</h2>

                    <!-- Form for creating a new category.
                         The previous value is repopulated after validation failure
                         to improve usability and reduce repeated input. -->
                    <form action="app/save-category.php" method="POST" novalidate data-validate="true">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input
                                type="text"
                                class="form-control"
                                id="name"
                                name="name"
                                value="<?php echo e($oldCategory['name'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h4 mb-3">Your Categories</h2>

                    <?php if (empty($categories)): ?>
                        <!-- Show helpful fallback text when no categories exist yet -->
                        <p class="mb-0 text-muted">No categories have been created yet.</p>
                    <?php else: ?>
                        <!-- Responsive table used so category management remains usable
                             across smaller and larger screen sizes -->
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">Category Name</th>
                                        <th scope="col" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo e($category['name']); ?></td>
                                            <td class="text-center">
                                                <div class="d-flex flex-column flex-md-row gap-2 justify-content-center">
                                                    <!-- Collapse component is used for inline editing
                                                         so the user can update a category without leaving the page -->
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#edit-category-<?php echo (int)$category['id']; ?>"
                                                        aria-expanded="false"
                                                        aria-controls="edit-category-<?php echo (int)$category['id']; ?>"
                                                    >
                                                        Edit
                                                    </button>

                                                    <!-- Delete action is submitted through POST to avoid deleting
                                                         data through a simple URL request. A confirmation dialog is
                                                         used as an extra safeguard against accidental deletion. -->
                                                    <form
                                                        action="app/delete-category.php"
                                                        method="POST"
                                                        onsubmit="return confirm('Are you sure you want to delete this category?');"
                                                    >
                                                        <input type="hidden" name="id" value="<?php echo (int)$category['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- Expandable inline edit form for updating the selected category -->
                                                <div class="collapse mt-3" id="edit-category-<?php echo (int)$category['id']; ?>">
                                                    <div class="card card-body bg-light">
                                                        <form action="app/update-category.php" method="POST" novalidate data-validate="true">
                                                            <input type="hidden" name="id" value="<?php echo (int)$category['id']; ?>">

                                                            <div class="mb-3 text-start">
                                                                <label for="edit-name-<?php echo (int)$category['id']; ?>" class="form-label">
                                                                    Edit Category Name
                                                                </label>
                                                                <input
                                                                    type="text"
                                                                    class="form-control"
                                                                    id="edit-name-<?php echo (int)$category['id']; ?>"
                                                                    name="name"
                                                                    value="<?php echo e($category['name']); ?>"
                                                                    required
                                                                >
                                                            </div>

                                                            <button type="submit" class="btn btn-primary btn-sm">
                                                                Save Changes
                                                            </button>
                                                        </form>
                                                    </div>
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
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/app/footer.php'; ?>