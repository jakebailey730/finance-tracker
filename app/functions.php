<?php

require_once __DIR__ . '/config.php';

// Escape output before displaying it in HTML.
// htmlspecialchars() is used to help prevent cross-site scripting (XSS) by converting special characters into safe HTML entities before output.
function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}


// Read a JSON file and return the decoded data as an array.
// Returning an empty array for missing, empty or invalid files allows the application to fail safely without generating unnecessary runtime errors.
function readJson($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }

    $json = file_get_contents($filePath);

    if ($json === false || trim($json) === '') {
        return [];
    }

    $data = json_decode($json, true);

    return is_array($data) ? $data : [];
}


// Write array data back to a JSON file.
// JSON_PRETTY_PRINT is used to keep the stored data readable during development and maintenance, while LOCK_EX helps reduce the risk of
// file corruption if multiple write operations happen close together.
function writeJson($filePath, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($filePath, $json, LOCK_EX) !== false;
}


// Generate the next available numeric ID for a new record.
// This provides a simple way to create unique identifiers when using JSON files instead of a relational database with auto-increment fields.
function generateId($items) {
    if (empty($items)) {
        return 1;
    }

    $ids = array_column($items, 'id');
    $ids = array_filter($ids, 'is_numeric');

    if (empty($ids)) {
        return 1;
    }

    return max($ids) + 1;
}


// Redirect the user to another page and stop further script execution.
// exit is used after the header redirect to ensure no extra code runs after the redirect has been sent.
function redirect($location) {
    header("Location: $location");
    exit;
}


// Store a flash message in the session.
// Flash messages are used for temporary user feedback, such as success or error messages, and are cleared after being displayed once.
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}


// Retrieve the flash message from the session and then clear it.
// This ensures the message is shown only once, which improves clarity and avoids repeating outdated feedback across multiple pages.
function getFlash() {
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}


// Prevent unauthenticated access to protected pages.
// This function centralises the login check so access control can be applied consistently across the application.
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        setFlash('danger', 'Please log in first.');
        redirect('index.php');
    }
}


// Search for a user record by email address.
// The comparison is made case-insensitive and trims whitespace so minor formatting differences do not prevent a valid match.
function findUserByEmail($email) {
    $users = readJson(USERS_FILE);

    foreach ($users as $user) {
        if (
            isset($user['email']) &&
            strtolower(trim($user['email'])) === strtolower(trim($email))
        ) {
            return $user;
        }
    }

    return null;
}


// Search for a user record by its unique ID.
// IDs are cast to integers to ensure comparisons are handled consistently.
function findUserById($userId) {
    $users = readJson(USERS_FILE);

    foreach ($users as $user) {
        if (isset($user['id']) && (int)$user['id'] === (int)$userId) {
            return $user;
        }
    }

    return null;
}


// Get the currently authenticated user's record.
// Returning null when no session exists allows calling pages to handle unauthenticated states safely.
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    return findUserById($_SESSION['user_id']);
}


// Update an existing user record in the users JSON file.
// The function replaces the matching record in the array and then writes the full updated dataset back to the file.
function updateUser($updatedUser) {
    $users = readJson(USERS_FILE);

    foreach ($users as $index => $user) {
        if ((int)$user['id'] === (int)$updatedUser['id']) {
            $users[$index] = $updatedUser;
            return writeJson(USERS_FILE, $users);
        }
    }

    return false;
}


// Return all categories that belong to a specific user.
// Filtering by user ID ensures each user only accesses their own data.
function getUserCategories($userId) {
    $categories = readJson(CATEGORIES_FILE);

    $filtered = array_filter($categories, function ($category) use ($userId) {
        return isset($category['user_id']) && (int)$category['user_id'] === (int)$userId;
    });

    return array_values($filtered);
}


// Return all transactions that belong to a specific user.
// This supports data separation between users when all records are stored together in the same JSON file.
function getUserTransactions($userId) {
    $transactions = readJson(TRANSACTIONS_FILE);

    $filtered = array_filter($transactions, function ($transaction) use ($userId) {
        return isset($transaction['user_id']) && (int)$transaction['user_id'] === (int)$userId;
    });

    return array_values($filtered);
}


// Find a single category by ID for the current user.
// This limits the search to the user's own categories for better security and simpler data handling.
function findCategoryById($categoryId, $userId) {
    $categories = getUserCategories($userId);

    foreach ($categories as $category) {
        if ((int)$category['id'] === (int)$categoryId) {
            return $category;
        }
    }

    return null;
}


// Find a single transaction by ID for the current user.
// Restricting the search to the current user's records helps prevent unauthorised access to another user's transaction data.
function findTransactionById($transactionId, $userId) {
    $transactions = getUserTransactions($userId);

    foreach ($transactions as $transaction) {
        if ((int)$transaction['id'] === (int)$transactionId) {
            return $transaction;
        }
    }

    return null;
}


// Check whether a category name already exists for a user.
// The excludeId parameter is used during updates so the current record does not count as a duplicate of itself.
function categoryExists($name, $userId, $excludeId = null) {
    $categories = getUserCategories($userId);

    foreach ($categories as $category) {
        $sameName = strtolower(trim($category['name'])) === strtolower(trim($name));
        $sameRecord = $excludeId !== null && (int)$category['id'] === (int)$excludeId;

        if ($sameName && !$sameRecord) {
            return true;
        }
    }

    return false;
}


// Validate that an email address is in a correct format.
// PHP's built in filter is used because it provides a reliable and concise method of server side email validation.
function isValidEmail($email) {
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
}


// Validate that a transaction amount is numeric and not negative.
// This helps preserve data integrity by preventing invalid amounts from being written to the JSON data files.
function isValidAmount($amount) {
    return is_numeric($amount) && (float)$amount >= 0;
}


// Validate that a date matches the expected YYYY-MM-DD format.
// DateTime::createFromFormat() is used so the application can confirm both the structure and the validity of the submitted date.
function isValidDate($date) {
    $dateObject = DateTime::createFromFormat('Y-m-d', $date);
    return $dateObject && $dateObject->format('Y-m-d') === $date;
}


// Store previous form input in the session.
// This improves usability by allowing forms to be repopulated after validation errors instead of forcing the user to re-enter all values.
function setOldInput($data) {
    $_SESSION['old'] = $data;
}


// Remove stored old form input from the session once it is no longer needed.
function clearOldInput() {
    unset($_SESSION['old']);
}


// Retrieve a previously submitted form value safely.
// Output is escaped before being returned so repopulated values can be displayed in HTML without introducing XSS risks.
function old($key, $default = '') {
    if (isset($_SESSION['old'][$key])) {
        return e($_SESSION['old'][$key]);
    }

    return e($default);
}


// Calculate the total income, total expenses and balance for dashboard display.
// This keeps summary logic in one reusable function rather than repeating the same calculations in multiple pages.
function calculateTotals($transactions) {
    $income = 0;
    $expense = 0;

    foreach ($transactions as $transaction) {
        $amount = isset($transaction['amount']) ? (float)$transaction['amount'] : 0;
        $type = $transaction['type'] ?? '';

        if ($type === 'Income') {
            $income += $amount;
        }

        if ($type === 'Expense') {
            $expense += $amount;
        }
    }

    return [
        'income' => $income,
        'expense' => $expense,
        'balance' => $income - $expense
    ];
}


// Filter transactions based on the report criteria selected by the user.
// Keeping filtering logic in a dedicated function improves reusability and makes the reporting code easier to read and maintain.
function filterTransactions($transactions, $categoryId = '', $type = '', $startDate = '', $endDate = '') {
    $filtered = array_filter($transactions, function ($transaction) use ($categoryId, $type, $startDate, $endDate) {
        if ($categoryId !== '' && (int)$transaction['category_id'] !== (int)$categoryId) {
            return false;
        }

        if ($type !== '' && $transaction['type'] !== $type) {
            return false;
        }

        if ($startDate !== '' && $transaction['date'] < $startDate) {
            return false;
        }

        if ($endDate !== '' && $transaction['date'] > $endDate) {
            return false;
        }

        return true;
    });

    return array_values($filtered);
}


// Sort transactions for report output.
// usort() is used so records can be ordered dynamically based on the user's selected field and direction.
function sortTransactions($transactions, $sortBy = '', $direction = 'asc') {
    if ($sortBy === '') {
        return $transactions;
    }

    usort($transactions, function ($a, $b) use ($sortBy, $direction) {
        $valueA = $a[$sortBy] ?? '';
        $valueB = $b[$sortBy] ?? '';

        // Convert amount values to floats before comparison so numeric sorting is accurate rather than treated as string sorting.
        if ($sortBy === 'amount') {
            $valueA = (float)$valueA;
            $valueB = (float)$valueB;
        }

        $result = $valueA <=> $valueB;

        if ($direction === 'desc') {
            return -$result;
        }

        return $result;
    });

    return $transactions;
}


// Return the category name that matches a category ID.
// This is used when displaying transactions so the user sees a readable category name rather than only a stored numeric ID.
function getCategoryName($categoryId, $categories) {
    foreach ($categories as $category) {
        if ((int)$category['id'] === (int)$categoryId) {
            return $category['name'];
        }
    }

    return 'Unknown';
}