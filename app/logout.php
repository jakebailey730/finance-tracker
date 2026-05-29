<?php

require_once __DIR__ . '/functions.php';

// Clear all session variables so any stored authentication data, flash messages or temporary form values are removed.
session_unset();

// Destroy the current session completely to log the user out securely.
session_destroy();

// Start a new session so a logout confirmation message can be stored.
session_start();

// Store a one-time success message to confirm to the user that the logout process completed successfully.
setFlash('success', 'You have been logged out successfully.');

// Redirect the user back to the login page after logout.
redirect('../index.php');