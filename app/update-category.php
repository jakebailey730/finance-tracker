<?php

// Handle updates to an existing category for the logged-in user.
// This script validates the submitted values, checks ownership of the category record, and then writes the updated data back to the JSON file.

require_once __DIR__ . '/functions.php';

// Restrict access to authenticated users only.
requireLogin();

// Only allow this action to be performed through a POST request.
// POST is used because this operation updates existing application data.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('danger', 'Invalid request.');
    redirect('../categories.php');
}

// Read the submitted category ID and new category name.
// The name is trimmed so accidental whitespace does not cause validation issues or inconsistent storage.
$id = $_POST['id'] ?? '';
$name = trim($_POST['name'] ?? '');

// Validate that a category ID was submitted and that it is numeric.
// This helps prevent invalid or tampered input from being processed.
if ($id === '' || !is_numeric($id)) {
    setFlash('danger', 'Invalid category selected.');
    redirect('../categories.php');
}

// Ensure the category name has been provided before continuing.
// Server-side validation is used because client-side checks can be bypassed.
if ($name === '') {
    setFlash('danger', 'Category name is required.');
    redirect('../categories.php');
}

// Cast the category ID and user ID to integers so comparisons remain consistent with the values stored in the JSON data.
$categoryId = (int)$id;
$userId = (int)$_SESSION['user_id'];

// Check whether another category with the same name already exists for this user.
// The current category ID is excluded from the duplicate check so the record can keep its existing name without being flagged as a duplicate of itself.
if (categoryExists($name, $userId, $categoryId)) {
    setFlash('danger', 'That category already exists.');
    redirect('../categories.php');
}

// Load all categories from the JSON file so the matching record can be updated.
$categories = readJson(CATEGORIES_FILE);
$updated = false;

// Search for the category that matches both the submitted category ID and the logged-in user's ID. 
// Matching both values ensures a user can only update their own category records.
foreach ($categories as $index => $category) {
    $isMatch = isset($category['id'], $category['user_id']) &&
        (int)$category['id'] === $categoryId &&
        (int)$category['user_id'] === $userId;

    if ($isMatch) {
        // Update only the category name once the correct record is found.
        $categories[$index]['name'] = $name;
        $updated = true;
        break;
    }
}

// If no matching category was found, stop the process and show an error.
// This prevents silent failures and guards against invalid IDs.
if (!$updated) {
    setFlash('danger', 'Category not found.');
    redirect('../categories.php');
}

// Save the updated categories array back to the JSON file.
// A success message is shown if the write is successful otherwise the user is informed that the update could not be completed.
if (writeJson(CATEGORIES_FILE, $categories)) {
    setFlash('success', 'Category updated successfully.');
    redirect('../categories.php');
}

setFlash('danger', 'There was a problem updating the category.');
redirect('../categories.php');