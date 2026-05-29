<?php

require_once __DIR__ . '/functions.php';

// Restrict access to logged-in users only.
requireLogin();

// Only allow the form to be processed through a POST request.
// POST is used because this action creates new data and should not be triggered by a normal page visit.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('danger', 'Invalid request.');
    redirect('../categories.php');
}

// Read and trim the submitted category name.
// Trimming removes accidental leading and trailing spaces so values are stored more consistently in the JSON file.
$name = trim($_POST['name'] ?? '');

// Store the submitted value so it can be repopulated if validation fails.
// This improves usability by avoiding the need for the user to retype input.
$_SESSION['old_category'] = [
    'name' => $name
];

// Ensure the category name has been provided before continuing.
// This server-side validation is important because client-side checks can be bypassed.
if ($name === '') {
    setFlash('danger', 'Category name is required.');
    redirect('../categories.php');
}

// Get the logged-in user's ID from the session so the category can be linked to the correct user account.
$userId = (int)$_SESSION['user_id'];

// Check for duplicate category names for this user.
// This prevents repeated category records being stored.
if (categoryExists($name, $userId)) {
    setFlash('danger', 'That category already exists.');
    redirect('../categories.php');
}

// Load the existing categories from the server-side JSON file.
// JSON storage is used to meet the assignment requirement of storing application data in server-side JSON files rather than a database.
$categories = readJson(CATEGORIES_FILE);

// Build the new category record.
// A generated ID is used to uniquely identify the category, while the user ID links the category to the currently authenticated user.
$newCategory = [
    'id' => generateId($categories),
    'user_id' => $userId,
    'name' => $name
];

// Add the new category to the existing categories array before saving.
$categories[] = $newCategory;

// Save the updated categories array back to the JSON file.
// If successful, the old form input is cleared and the user receives confirmation otherwise an error message is shown.
if (writeJson(CATEGORIES_FILE, $categories)) {
    unset($_SESSION['old_category']);
    setFlash('success', 'Category added successfully.');
    redirect('../categories.php');
}

setFlash('danger', 'There was a problem saving the category.');
redirect('../categories.php');