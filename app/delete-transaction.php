<?php

require_once __DIR__ . '/functions.php';

// Ensure only authenticated users can access this action.
// Deleting transactions changes stored user data, so this must be restricted to logged in users only.
requireLogin();

// Only accept POST requests for deletion.
// POST is used because this action modifies application data and should not be triggered by directly visiting a URL.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('danger', 'Invalid request.');
    redirect('../transactions.php');
}

// Read the submitted transaction ID from the form data.
// The fallback empty string prevents undefined index errors if no ID was included in the request.
$id = $_POST['id'] ?? '';

// Validate that the transaction ID exists and is numeric before using it.
// This prevents invalid or tampered input from being processed.
if ($id === '' || !is_numeric($id)) {
    setFlash('danger', 'Invalid transaction selected.');
    redirect('../transactions.php');
}

// Cast the submitted transaction ID and session user ID to integers so comparisons are performed consistently and match the stored JSON values.
$transactionId = (int)$id;
$userId = (int)$_SESSION['user_id'];

// Load all stored transactions from the JSON file.
// A new array is created so the selected transaction can be excluded while preserving all other records.
$transactions = readJson(TRANSACTIONS_FILE);
$updatedTransactions = [];
$deleted = false;

// Copy every transaction except the one that matches both the submitted transaction ID and the logged-in user's ID. 
foreach ($transactions as $transaction) {
    $isMatch = isset($transaction['id'], $transaction['user_id']) &&
        (int)$transaction['id'] === $transactionId &&
        (int)$transaction['user_id'] === $userId;

    if ($isMatch) {
        $deleted = true;
        continue;
    }

    $updatedTransactions[] = $transaction;
}

// If no matching transaction was found, stop the process and show an error.
// This handles invalid IDs safely and prevents silent failures.
if (!$deleted) {
    setFlash('danger', 'Transaction not found.');
    redirect('../transactions.php');
}

// Save the filtered transactions array back to the JSON file.
// A success or failure message is shown so the user receives clear feedback about the result of the delete action.
if (writeJson(TRANSACTIONS_FILE, $updatedTransactions)) {
    setFlash('success', 'Transaction deleted successfully.');
} else {
    setFlash('danger', 'There was a problem deleting the transaction.');
}

redirect('../transactions.php');