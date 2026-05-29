<?php

require_once __DIR__ . '/functions.php';

// Prevent authenticated users from accessing the login or registration process again.
// This improves user flow by sending already logged in users straight to the dashboard
if (isset($_SESSION['user_id'])) {
    redirect('../dashboard.php');
}

// Determine which form was submitted by reading the hidden action field from POST data.
// The null coalescing operator is used to avoid undefined index errors if no action was sent.
$action = $_POST['action'] ?? '';


// Handle registration
if ($action === 'register') {
    // Trim all user input before validation so accidental leading or trailing spaces do not cause valid entries to fail checks or get stored inconsistently in JSON.
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Store previously entered form values in the session so they can be repopulated if validation fails. 
    // This improves usability because the user does not need to retype all of their details after an error.
    $_SESSION['old_register'] = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email
    ];

    // Check that all required registration fields have been completed before attempting to create an account. 
    // Server-side validation is essential because client-side checks can be bypassed by the user.
    if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
        setFlash('danger', 'Please complete all required registration fields.');
        redirect('../register.php');
    }

    // Validate the structure of the email address before storing it.
    // This helps maintain data quality in the JSON file and prevents invalid email values being saved to the application.
    if (!isValidEmail($email)) {
        setFlash('danger', 'Please enter a valid email address.');
        redirect('../register.php');
    }

    // Check whether the email address is already associated with an existing account.
    // This prevents duplicate user records being written to the JSON file and ensures each account has a unique login credential.
    if (findUserByEmail($email)) {
        setFlash('danger', 'That email address is already registered.');
        redirect('../register.php');
    }

    // Load the current list of users from the server-side JSON file.
    $users = readJson(USERS_FILE);

    // Create a new user record ready to be appended to the users array.
    // A generated ID is used to uniquely identify each user, and the password is securely hashed rather than stored in plain text to improve security.
    $newUser = [
        'id' => generateId($users),
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'currency' => DEFAULT_CURRENCY
    ];

    // Append the new user record to the existing users array before saving it back to the JSON file.
    $users[] = $newUser;

    // Write the updated users array back to the JSON file.
    // If successful, the stored form data is cleared and a success message is shown.
    // If the write fails, the user is redirected back with an error message so the failure is handled gracefully.
    if (writeJson(USERS_FILE, $users)) {
        unset($_SESSION['old_register']);
        setFlash('success', 'Registration successful. You can now log in.');
        redirect('../index.php');
    } else {
        setFlash('danger', 'There was a problem creating your account. Please try again.');
        redirect('../register.php');
    }
}


// Handle login
if ($action === 'login') {
    // Trim the submitted login values so spacing errors do not affect authentication.
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Store the previously entered email address so it can be repopulated if login fails.
    // The password is not stored for security reasons.
    $_SESSION['old_login'] = [
        'email' => $email
    ];

    // Ensure both login fields have been completed before attempting authentication.
    // This avoids unnecessary processing and provides immediate feedback to the user.
    if ($email === '' || $password === '') {
        setFlash('danger', 'Please enter both email and password.');
        redirect('../index.php');
    }

    // Search for a user record that matches the submitted email address.
    // This separates the process of locating the account from verifying its password.
    $user = findUserByEmail($email);

    // Verify that the user exists and that the submitted password matches the stored hash.
    // password_verify() is used because passwords are stored securely in hashed form.
    if (!$user || !password_verify($password, $user['password'])) {
        setFlash('danger', 'Invalid email or password entered.');
        redirect('../index.php');
    }

    // Store the authenticated user's ID in the session to mark them as logged in.
    // Session-based authentication is used so protected pages can identify the user across multiple requests.
    $_SESSION['user_id'] = $user['id'];

    // Clear the stored login form value once authentication succeeds.
    unset($_SESSION['old_login']);

    setFlash('success', 'Login successful.');
    redirect('../dashboard.php');
}


// Handle invalid or unexpected form submissions.
// This acts as a fallback so the script fails safely if neither login nor register was submitted correctly.
setFlash('danger', 'Invalid request.');
redirect('../index.php');