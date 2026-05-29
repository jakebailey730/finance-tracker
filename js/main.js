// Control the transaction form behaviour when the user selects a transaction type.
// If the type is "Income" and a category called "Income" exists, the matching category is selected automatically and the dropdown is disabled to reduce user
// input and keep category selection consistent.
function handleTransactionType() {
    const typeSelect = document.getElementById('type');
    const categorySelect = document.getElementById('category_id');

    // Stop the function early if the relevant form fields are not present.
    // This allows the same JavaScript file to be loaded across multiple pages without causing errors on pages that do not include this form.
    if (!typeSelect || !categorySelect) {
        return;
    }

    // Read the optional income category ID stored in the category select element.
    // A data attribute is used so server side PHP can pass the correct category ID into JavaScript without hard coding values in the script.
    const incomeCategoryId = categorySelect.dataset.incomeId || '';

    // Update the category field depending on the selected transaction type.
    // Disabling the field for income transactions prevents the user from accidentally assigning income to the wrong category.
    function updateCategoryState() {
        if (typeSelect.value === 'Income' && incomeCategoryId !== '') {
            categorySelect.value = incomeCategoryId;
            categorySelect.disabled = true;
        } else {
            categorySelect.disabled = false;
        }
    }

    // Apply the correct state as soon as the page loads, then keep it updated whenever the transaction type changes.
    updateCategoryState();
    typeSelect.addEventListener('change', updateCategoryState);

    const form = typeSelect.closest('form');

    if (form) {
        // Re-enable the category field just before submission if it was disabled.
        // Disabled fields are not submitted with forms, so this ensures the selected category value is still sent to the server.
        form.addEventListener('submit', function () {
            if (typeSelect.value === 'Income' && incomeCategoryId !== '') {
                categorySelect.disabled = false;
                categorySelect.value = incomeCategoryId;
            }
        });
    }
}

// Display a validation error message underneath a form field.
// This provides immediate visual feedback to the user and applies a consistent invalid style using the CSS validation classes.
function showError(input, message) {
    clearError(input);

    input.classList.add('is-invalid');

    const error = document.createElement('div');
    error.className = 'invalid-feedback';
    error.textContent = message;

    if (input.parentNode) {
        input.parentNode.appendChild(error);
    }
}

// Remove any existing validation styling and error message from a field.
// Clearing old errors first prevents duplicate messages from being shown.
function clearError(input) {
    input.classList.remove('is-invalid');

    const parent = input.parentNode;
    if (!parent) {
        return;
    }

    const existingError = parent.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
}

// Validate email format on the client side before form submission.
// A regular expression is used here for quick feedback, while server side validation is still required for security and data integrity.
function isValidEmail(email) {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(email);
}

// Apply basic client-side validation to all forms marked with data-validate="true".
// Using a data attribute allows validation behaviour to be enabled only on the forms that need it, which keeps the script reusable across the application.
function setupValidation() {
    const forms = document.querySelectorAll('form[data-validate="true"]');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            let isFormValid = true;

            const requiredFields = form.querySelectorAll('[required]');

            requiredFields.forEach(function (field) {
                clearError(field);

                // Ignore disabled fields because they are not intended to be edited or validated by the user in their current state.
                if (field.disabled) {
                    return;
                }

                const value = field.value.trim();

                // Check that required fields are not left empty before the form submits.
                if (value === '') {
                    showError(field, 'This field is required.');
                    isFormValid = false;
                    return;
                }

                // Perform a client-side email format check for faster feedback.
                if (field.type === 'email' && !isValidEmail(value)) {
                    showError(field, 'Please enter a valid email address.');
                    isFormValid = false;
                    return;
                }

                // Validate amount fields to ensure they contain a numeric value that is not negative.
                if (field.name === 'amount' && (isNaN(value) || Number(value) < 0)) {
                    showError(field, 'Please enter a valid amount.');
                    isFormValid = false;
                    return;
                }
            });

            // Stop the form submitting if any validation rule failed.
            // This gives the user a chance to correct their input first.
            if (!isFormValid) {
                event.preventDefault();
            }
        });
    });
}

// Clear validation errors as the user types or changes values.
// This improves usability by removing outdated error messages once the user starts correcting their input.
function setupLiveValidationClear() {
    const fields = document.querySelectorAll('input, select, textarea');

    fields.forEach(function (field) {
        field.addEventListener('input', function () {
            clearError(field);
        });

        field.addEventListener('change', function () {
            clearError(field);
        });
    });
}

// Wait until the page has loaded before attaching event listeners.
// This ensures the relevant form elements exist before the script runs.
document.addEventListener('DOMContentLoaded', function () {
    handleTransactionType();
    setupValidation();
    setupLiveValidationClear();
});