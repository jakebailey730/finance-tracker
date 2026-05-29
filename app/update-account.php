<?php

// Handle updates to the logged-in user's account details.
// This script allows a user to change their first name, last name and default currency preference, then saves the updated record back to the users JSON file.

require_once __DIR__ . '/functions.php';

// Restrict access to authenticated users only.
requireLogin();

// Only allow this action to be processed through a POST request.
// POST is used because this action modifies stored user data.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('danger', 'Invalid request.');
    redirect('../my-account.php');
}

// Retrieve the currently authenticated user's record.
// This ensures the update is applied to the correct account.
$user = getCurrentUser();

// Stop the process if no valid user record can be found.
// This acts as a safeguard against invalid session data.
if (!$user) {
    setFlash('danger', 'User account not found.');
    redirect('../index.php');
}

// Read and trim the submitted values so accidental whitespace does not affect validation or lead to inconsistent storage.
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$currency = trim($_POST['currency'] ?? '');

// Ensure all required fields have been completed before updating the account.
// Server side validation is important because client-side checks can be bypassed.
if ($firstName === '' || $lastName === '' || $currency === '') {
    setFlash('danger', 'Please complete all required fields.');
    redirect('../my-account.php');
}

// Validate the selected currency against the predefined allowed values.
// Restricting currency options helps keep stored account settings consistent.
if (!in_array($currency, VALID_CURRENCIES, true)) {
    setFlash('danger', 'Please select a valid currency.');
    redirect('../my-account.php');
}

// Apply the validated values to the current user's record in memory.
// Updating the array first keeps the logic clear before writing to storage.
$user['first_name'] = $firstName;
$user['last_name'] = $lastName;
$user['currency'] = $currency;

// Save the updated user record back to the users JSON file.
// If successful, show a confirmation message otherwise show an error so the user receives clear feedback about the outcome.
if (updateUser($user)) {
    setFlash('success', 'Account details updated successfully.');
    redirect('../my-account.php');
}

setFlash('danger', 'There was a problem updating your account details.');
redirect('../my-account.php');