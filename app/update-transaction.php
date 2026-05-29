<?php

// Handle updating an existing transaction record.

require_once __DIR__ . '/functions.php';

// Restrict access to authenticated users only.
requireLogin();

// Only process updates submitted through a POST request.
// POST is used because this action modifies existing data and should not be triggered through a normal page visit.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('danger', 'Invalid request.');
    redirect('../transactions.php');
}

// Retrieve submitted form values and trim text inputs so accidental whitespace does not cause validation issues or inconsistent storage.
$id = $_POST['id'] ?? '';
$date = trim($_POST['date'] ?? '');
$itemName = trim($_POST['item_name'] ?? '');
$categoryId = trim($_POST['category_id'] ?? '');
$amount = trim($_POST['amount'] ?? '');
$currency = trim($_POST['currency'] ?? '');
$type = trim($_POST['type'] ?? '');

// Validate the submitted transaction ID before using it.
// This prevents invalid or tampered IDs being processed.
if ($id === '' || !is_numeric($id)) {
    setFlash('danger', 'Invalid transaction selected.');
    redirect('../transactions.php');
}

// Cast IDs to integers so comparisons are consistent with the values stored in the JSON file and session.
$transactionId = (int)$id;
$userId = (int)$_SESSION['user_id'];

// Store the user's submitted values so the edit form can be repopulated if validation fails. This improves usability by avoiding re-entry.
$_SESSION['old_transaction'] = [
    'date' => $date,
    'item_name' => $itemName,
    'category_id' => $categoryId,
    'amount' => $amount,
    'currency' => $currency,
    'type' => $type
];

// Ensure all required fields have been completed before attempting the update.
// Server-side validation is essential because client-side validation can be bypassed.
if ($date === '' || $itemName === '' || $categoryId === '' || $amount === '' || $currency === '' || $type === '') {
    setFlash('danger', 'Please complete all required fields.');
    redirect('../edit-transactions.php?id=' . $transactionId);
}

// Validate the submitted date so only correctly formatted dates are stored.
if (!isValidDate($date)) {
    setFlash('danger', 'Please enter a valid date.');
    redirect('../edit-transactions.php?id=' . $transactionId);
}

// Ensure the category ID is numeric before it is used in lookups.
if (!is_numeric($categoryId)) {
    setFlash('danger', 'Please select a valid category.');
    redirect('../edit-transactions.php?id=' . $transactionId);
}

// Validate that the amount is numeric and non-negative.
// This helps preserve data integrity.
if (!isValidAmount($amount)) {
    setFlash('danger', 'Amount must be a valid number.');
    redirect('../edit-transactions.php?id=' . $transactionId);
}

// Restrict currency values to the predefined supported options.
// This avoids unexpected values being stored and keeps report calculations consistent.
if (!in_array($currency, VALID_CURRENCIES, true)) {
    setFlash('danger', 'Please select a valid currency.');
    redirect('../edit-transactions.php?id=' . $transactionId);
}

// Restrict transaction types to the allowed values used by the application.
// This ensures consistency when calculating totals and generating reports.
if (!in_array($type, VALID_TRANSACTION_TYPES, true)) {
    setFlash('danger', 'Please select a valid transaction type.');
    redirect('../edit-transactions.php?id=' . $transactionId);
}

// Check that the transaction exists and belongs to the logged in user.
// This prevents users from editing records they do not own.
$existingTransaction = findTransactionById($transactionId, $userId);

if (!$existingTransaction) {
    setFlash('danger', 'Transaction not found.');
    redirect('../transactions.php');
}

// Check that the selected category exists and also belongs to the logged-in user.
// This prevents invalid references and stops users assigning transactions to categories outside their own account.
$category = findCategoryById((int)$categoryId, $userId);

if (!$category) {
    setFlash('danger', 'Selected category was not found.');
    redirect('../edit-transactions.php?id=' . $transactionId);
}

// Load all stored transactions from the JSON file so the matching record can be updated.
$transactions = readJson(TRANSACTIONS_FILE);
$updated = false;

// Search for the transaction that matches both the submitted transaction ID and the current user's ID. 
// Matching both values ensures only the owner's record can be edited.
foreach ($transactions as $index => $transaction) {
    $isMatch = isset($transaction['id'], $transaction['user_id']) &&
        (int)$transaction['id'] === $transactionId &&
        (int)$transaction['user_id'] === $userId;

    if ($isMatch) {
        // Overwrite the existing record with the new validated values.
        // Numeric values are explicitly cast to preserve consistent data types.
        $transactions[$index]['date'] = $date;
        $transactions[$index]['item_name'] = $itemName;
        $transactions[$index]['category_id'] = (int)$categoryId;
        $transactions[$index]['amount'] = (float)$amount;
        $transactions[$index]['currency'] = $currency;
        $transactions[$index]['type'] = $type;
        $updated = true;
        break;
    }
}

// If no matching record was updated, stop the process and return an error.
// This acts as a safety check against invalid IDs or tampered requests.
if (!$updated) {
    setFlash('danger', 'Transaction not found.');
    redirect('../transactions.php');
}

// Save the updated transactions array back to the JSON file.
// On success, clear the stored form values and return the user to the transactions page otherwise display an error and return them to the edit form.
if (writeJson(TRANSACTIONS_FILE, $transactions)) {
    unset($_SESSION['old_transaction']);
    setFlash('success', 'Transaction updated successfully.');
    redirect('../transactions.php');
}

setFlash('danger', 'There was a problem updating the transaction.');
redirect('../edit-transactions.php?id=' . $transactionId);