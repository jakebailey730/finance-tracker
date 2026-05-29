<?php
// This file is included across the application to start the session and define shared constants such as file paths, application settings and validation values.
// Centralising this setup avoids repetition and makes the application easier to maintain.

if (session_status() === PHP_SESSION_NONE) {
    // Start a session only if one has not already been created.
    // This allows session data such as login state and flash messages to be accessed consistently throughout the application.
    session_start();
}

// Define core application paths in one place so other files can reuse them.
// Using absolute paths based on __DIR__ and dirname(__DIR__) is more reliable than hard-coded relative paths, especially when files are included from
// different locations in the project structure.
define('APP_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));
define('DATA_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'data');

// Define the paths to each server-side JSON file used by the application.
// Storing these as constants makes file access consistent 
define('USERS_FILE', DATA_PATH . DIRECTORY_SEPARATOR . 'users.json');
define('TRANSACTIONS_FILE', DATA_PATH . DIRECTORY_SEPARATOR . 'transactions.json');
define('CATEGORIES_FILE', DATA_PATH . DIRECTORY_SEPARATOR . 'categories.json');
define('SETTINGS_FILE', DATA_PATH . DIRECTORY_SEPARATOR . 'settings.json');

// Define shared application settings in one location.
// This makes values such as the application name and default currency easy to reuse and change without editing multiple files.
define('APP_NAME', 'FinanceTracking R US');
define('DEFAULT_CURRENCY', 'GBP');

// Define the accepted transaction types and currencies used during validation.
// Restricting these values helps preserve data integrity by preventing invalid or unexpected values from being written to the JSON data files.
define('VALID_TRANSACTION_TYPES', ['Income', 'Expense']);
define('VALID_CURRENCIES', ['GBP', 'EUR', 'USD']);

// Create a list of JSON files the application depends on.
// This is used to check that the required storage files exist before the rest of the application attempts to read from or write to them.
$requiredFiles = [
    USERS_FILE,
    TRANSACTIONS_FILE,
    CATEGORIES_FILE,
    SETTINGS_FILE
];

// Ensure each required JSON file exists.
// If a file is missing, it is created with an empty JSON array so the application can still run without crashing due to missing data files.
// JSON_PRETTY_PRINT is used to keep the stored data readable for development and maintenance, while LOCK_EX helps reduce the risk of file corruption during writes.
foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([], JSON_PRETTY_PRINT), LOCK_EX);
    }
}