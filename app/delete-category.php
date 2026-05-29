<?php

require_once __DIR__ . '/functions.php';

// Ensure the user is authenticated before allowing access to category deletion.
// This protects the action from unauthorised users and ensures only logged-in users can modify their own stored data.
requireLogin();

// Only allow this action to be triggered through a POST request.
// POST is used here because deleting data changes the application state, so it should not be performed through a normal page URL request.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('danger', 'Invalid request.');
    redirect('../categories.php');
}

// Read the submitted category ID from the form data.
// A fallback empty string is used to avoid undefined index errors if no ID was submitted with the request.
$id = $_POST['id'] ?? '';

// Validate that a category ID was provided and that it is numeric before using it.
// This helps prevent invalid or malformed input being processed by the script.
if ($id === '' || !is_numeric($id)) {
    setFlash('danger', 'Invalid category selected.');
    redirect('../categories.php');
}

// Cast values to integers before comparison so IDs are handled consistently.
// The user ID is taken from the session to ensure the operation only applies to the currently authenticated user.
$categoryId = (int)$id;
$userId = (int)$_SESSION['user_id'];

// Load the current user's transactions to check whether the category is in use.
// This validation prevents the deletion of categories that are still linked to transaction records, helping maintain referential consistency
$transactions = getUserTransactions($userId);

foreach ($transactions as $transaction) {
    if ((int)$transaction['category_id'] === $categoryId) {
        setFlash('danger', 'You cannot delete a category that is being used by a transaction.');
        redirect('../categories.php');
    }
}

// Load all categories from the JSON file so the selected category can be removed.
// A new array is built rather than deleting in place, which keeps the logic simple and avoids modifying the original array while looping through it.
$categories = readJson(CATEGORIES_FILE);
$updatedCategories = [];
$deleted = false;

// Copy every category except the one that matches both the submitted category ID and the logged-in user's ID. 
// Checking both values ensures a user cannot delete another user's category
foreach ($categories as $category) {
    $isMatch = isset($category['id'], $category['user_id']) &&
        (int)$category['id'] === $categoryId &&
        (int)$category['user_id'] === $userId;

    if ($isMatch) {
        $deleted = true;
        continue;
    }

    $updatedCategories[] = $category;
}

// If no matching category was found, stop the process and inform the user.
// This acts as a safeguard against invalid IDs or unauthorised deletion attempts.
if (!$deleted) {
    setFlash('danger', 'Category not found.');
    redirect('../categories.php');
}

// Write the filtered categories array back to the JSON file.
// A success message is shown if the update is saved correctly otherwise an error message is returned so the failure is handled gracefully.
if (writeJson(CATEGORIES_FILE, $updatedCategories)) {
    setFlash('success', 'Category deleted successfully.');
    redirect('../categories.php');
}

setFlash('danger', 'There was a problem deleting the category.');
redirect('../categories.php');