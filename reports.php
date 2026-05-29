<?php
// Display the reports page for the logged-in user.
// This page loads the user's categories and transactions, applies any selected filters and sorting options, then presents the results in
// both a table and a chart for easier analysis.

require_once __DIR__ . '/app/functions.php';

// Restrict access to authenticated users only.
requireLogin();

// Load any one-time feedback message and the logged-in user's data.
// Categories are needed for dropdown filters and category name lookups while transactions provide the raw data for the report output.
$flash = getFlash();
$categories = getUserCategories($_SESSION['user_id']);
$transactions = getUserTransactions($_SESSION['user_id']);

// Read the selected filter and sorting values from the query string.
// GET is used because reports are read-only and the URL can be reused or bookmarked with the current filter settings.
$selectedCategory = trim($_GET['category_id'] ?? '');
$selectedType = trim($_GET['type'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');
$sortBy = trim($_GET['sort_by'] ?? '');
$direction = trim($_GET['direction'] ?? 'asc');

// Apply the selected filters to reduce the transaction list to only the records relevant to the user's report criteria.
$filteredTransactions = filterTransactions(
    $transactions,
    $selectedCategory,
    $selectedType,
    $startDate,
    $endDate
);

// Apply sorting after filtering so the final report output appears in the order selected by the user.
$filteredTransactions = sortTransactions($filteredTransactions, $sortBy, $direction);

// Aggregate transaction totals by category for the chart output.
// This prepares the data in a format suitable for visualisation allowing users to compare values more easily across categories.
$chartLabels = [];
foreach ($filteredTransactions as $transaction) {
    $categoryName = getCategoryName((int)$transaction['category_id'], $categories);

    if (!isset($chartLabels[$categoryName])) {
        $chartLabels[$categoryName] = 0;
    }

    $chartLabels[$categoryName] += (float)$transaction['amount'];
}

// Split the aggregated chart data into separate label and value arrays so they can be safely passed to Chart.js as JSON.
$chartDataLabels = array_keys($chartLabels);
$chartDataAmounts = array_values($chartLabels);

// Set page-specific values used by the shared header template.
$pageTitle = 'Reports';
$currentPage = 'reports';
$showNavbar = true;
$useBootstrapJs = true;

require_once __DIR__ . '/app/header.php';
?>

<main id="main-content" class="container py-4">
    <!-- Page heading clearly explains the purpose of the reports screen -->
    <div class="mb-4">
        <h1 class="fw-bold mb-1">Reports</h1>
        <p class="text-muted mb-0">Analyse your financial data using filters, sorting options and a chart.</p>
    </div>

    <!-- Display a one-time success or error message from the session -->
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo e($flash['type']); ?>" role="alert">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h4 mb-3">Filter and Sort Report</h2>

            <!-- Report filter form uses GET so selected options appear in the URL.
                 This makes the report easier to reuse, refresh and share. -->
            <form action="reports.php" method="GET" novalidate>
                <div class="row g-3">
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option
                                    value="<?php echo (int)$category['id']; ?>"
                                    <?php echo ((string)$selectedCategory === (string)$category['id']) ? 'selected' : ''; ?>
                                >
                                    <?php echo e($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="type" class="form-label">Transaction Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <?php foreach (VALID_TRANSACTION_TYPES as $type): ?>
                                <option
                                    value="<?php echo e($type); ?>"
                                    <?php echo ($selectedType === $type) ? 'selected' : ''; ?>
                                >
                                    <?php echo e($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="sort_by" class="form-label">Sort By</label>
                        <select class="form-select" id="sort_by" name="sort_by">
                            <option value="">No Sorting</option>
                            <option value="date" <?php echo ($sortBy === 'date') ? 'selected' : ''; ?>>Date</option>
                            <option value="item_name" <?php echo ($sortBy === 'item_name') ? 'selected' : ''; ?>>Item Name</option>
                            <option value="amount" <?php echo ($sortBy === 'amount') ? 'selected' : ''; ?>>Amount</option>
                            <option value="type" <?php echo ($sortBy === 'type') ? 'selected' : ''; ?>>Type</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo e($startDate); ?>">
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo e($endDate); ?>">
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="direction" class="form-label">Sort Direction</label>
                        <select class="form-select" id="direction" name="direction">
                            <option value="asc" <?php echo ($direction === 'asc') ? 'selected' : ''; ?>>Ascending</option>
                            <option value="desc" <?php echo ($direction === 'desc') ? 'selected' : ''; ?>>Descending</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="reports.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h4 mb-3">Report Table</h2>

            <?php if (empty($filteredTransactions)): ?>
                <!-- Show a helpful empty-state message when no records match the selected filters -->
                <p class="mb-0 text-muted">No transactions found for the selected filters.</p>
            <?php else: ?>
                <!-- Responsive table allows filtered report data to remain readable
                     across different screen sizes -->
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Item</th>
                                <th scope="col">Category</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredTransactions as $transaction): ?>
                                <tr>
                                    <td><?php echo e($transaction['date']); ?></td>
                                    <td><?php echo e($transaction['item_name']); ?></td>
                                    <td><?php echo e(getCategoryName((int)$transaction['category_id'], $categories)); ?></td>
                                    <td>
                                        <?php echo e($transaction['currency']); ?>
                                        <?php echo number_format((float)$transaction['amount'], 2); ?>
                                    </td>
                                    <td>
                                        <!-- Colour-coded badges provide quick visual distinction
                                             between income and expense records -->
                                        <?php if (($transaction['type'] ?? '') === 'Income'): ?>
                                            <span class="badge bg-success">Income</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Expense</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="h4 mb-3">Chart Overview</h2>

            <?php if (empty($filteredTransactions)): ?>
                <!-- Avoid rendering an empty chart when no filtered data exists -->
                <p class="mb-0 text-muted">No chart data available for the selected filters.</p>
            <?php else: ?>
                <!-- Canvas element used by Chart.js to render a visual summary of totals by category -->
                <div class="chart-container" style="position: relative; min-height: 320px;">
                    <canvas id="reportChart" aria-label="Financial report chart" role="img"></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php if (!empty($filteredTransactions)): ?>
    <!-- Load Chart.js only when chart data exists.
         This avoids unnecessary script loading when there is nothing to display. -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Pass the aggregated PHP report data into JavaScript as JSON arrays so it can be used directly by the chart library.
        const chartLabels = <?php echo json_encode($chartDataLabels); ?>;
        const chartAmounts = <?php echo json_encode($chartDataAmounts); ?>;

        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('reportChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            // Create a bar chart to show total transaction amounts by category.
            // A chart is used here to complement the table view and make category comparisons easier to interpret visually.
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Total Amount by Category',
                        data: chartAmounts,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });
        });
    </script>
<?php endif; ?>

<?php require_once __DIR__ . '/app/footer.php'; ?>