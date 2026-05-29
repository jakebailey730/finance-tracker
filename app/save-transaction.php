<?php

require_once __DIR__ . '/functions.php';

// Restrict access to authenticated users only.
requireLogin();

// Only allow POST requests to create transactions.
// This prevents the action being triggered through direct URL access and ensures it only occurs via form submission.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('danger', 'Invalid request.');
    redirect('../transactions.php');
}

// Retrieve and trim all submitted form inputs.
// Trimming ensures accidental whitespace does not affect validation or result in inconsistent data being stored.
$date = trim($_POST['date'] ?? '');
$itemName = trim($_POST['item_name'] ?? '');
$categoryId = trim($_POST['category_id'] ?? '');
$amount = trim($_POST['amount'] ?? '');
$currency = trim($_POST['currency'] ?? '');
$type = trim($_POST['type'] ?? '');

// Store previous form input so it can be repopulated if validation fails.
// This improves usability by preventing the user from re-entering all data.
$_SESSION['old_transaction'] = [
    'date' => $date,
    'item_name' => $itemName,
    'category_id' => $categoryId,
    'amount' => $amount,
    'currency' => $currency,
    'type' => $type
];

// Ensure all required fields are completed before processing.
// Server-side validation is used to guarantee correctness even if client-side validation is bypassed.
if ($date === '' || $itemName === '' || $categoryId === '' || $amount === '' || $currency === '' || $type === '') {
    setFlash('danger', 'Please complete all required fields.');
    redirect('../add-transactions.php');
}

// Validate the date format using a dedicated helper function.
// This ensures dates are stored in the correct format.
if (!isValidDate($date)) {
    setFlash('danger', 'Please enter a valid date.');
    redirect('../add-transactions.php');
}

// Ensure the category ID is numeric.
// This prevents invalid or tampered input being used in lookups.
if (!is_numeric($categoryId)) {
    setFlash('danger', 'Please select a valid category.');
    redirect('../add-transactions.php');
}

// Validate the transaction amount.
if (!isValidAmount($amount)) {
    setFlash('danger', 'Amount must be a valid number.');
    redirect('../add-transactions.php');
}

// Validate the selected currency against predefined allowed values.
// This prevents invalid or unexpected values being written to storage.
if (!in_array($currency, VALID_CURRENCIES, true)) {
    setFlash('danger', 'Please select a valid currency.');
    redirect('../add-transactions.php');
}

// Validate the transaction type (Income or Expense).
// Restricting values ensures consistency when calculating totals and reports.
if (!in_array($type, VALID_TRANSACTION_TYPES, true)) {
    setFlash('danger', 'Please select a valid transaction type.');
    redirect('../add-transactions.php');
}

// Retrieve the current user's ID from the session.
$userId = (int)$_SESSION['user_id'];

// Ensure the selected category exists and belongs to the user.
// This prevents invalid references and ensures users cannot assign transactions to categories they do not own.
$category = findCategoryById((int)$categoryId, $userId);

if (!$category) {
    setFlash('danger', 'Selected category was not found.');
    redirect('../add-transactions.php');
}

// Load existing transactions from the JSON file.
$transactions = readJson(TRANSACTIONS_FILE);

// Create a new transaction record.
// Data types are explicitly cast to ensure consistency when storing and later processing transaction data.
$newTransaction = [
    'id' => generateId($transactions),
    'user_id' => $userId,
    'date' => $date,
    'item_name' => $itemName,
    'category_id' => (int)$categoryId,
    'amount' => (float)$amount,
    'currency' => $currency,
    'type' => $type
];

// Add the new transaction to the existing dataset.
$transactions[] = $newTransaction;

// Save the updated transactions array back to the JSON file.
// If successful, clear stored form input and notify the user otherwise display an error message.
if (writeJson(TRANSACTIONS_FILE, $transactions)) {
    unset($_SESSION['old_transaction']);
    setFlash('success', 'Transaction added successfully.');
    redirect('../transactions.php');
}

setFlash('danger', 'There was a problem saving the transaction.');
redirect('../add-transactions.php');