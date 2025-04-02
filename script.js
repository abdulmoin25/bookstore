/**
 * Bookstore Website - Main JavaScript File
 * Includes all core functionality for the website
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components when DOM is fully loaded
    initBootstrapComponents();
    initDropdowns();
    initForms();
    initImagePreviews();
    initConfirmationDialogs();
    initRatingSystem();
    initSearchFunctionality();
    initToastSystem();
});

/**
 * Initialize all Bootstrap components
 */
function initBootstrapComponents() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Initialize toasts if any exist on page load
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.map(function(toastEl) {
        return new bootstrap.Toast(toastEl);
    });
}

/**
 * Enhanced dropdown functionality with manual control
 */
function initDropdowns() {
    // Manual dropdown handling for better reliability
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling;
            const isShown = dropdownMenu.classList.contains('show');
            
            // Close all other dropdowns first
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== dropdownMenu) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            dropdownMenu.classList.toggle('show');
            
            // Update aria-expanded attribute
            const expanded = dropdownMenu.classList.contains('show');
            this.setAttribute('aria-expanded', expanded);
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
            document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                toggle.setAttribute('aria-expanded', 'false');
            });
        }
    });

    // Close dropdown when selecting an item (for mobile)
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
            this.closest('.dropdown-menu').classList.remove('show');
            const toggle = this.closest('.dropdown').querySelector('.dropdown-toggle');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });
}

/**
 * Form validation and handling
 */
function initForms() {
    // Registration form validation
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validateRegisterForm()) {
                e.preventDefault();
            }
        });
    }

    // Login form validation
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!validateLoginForm()) {
                e.preventDefault();
            }
        });
    }

    // Checkout form validation
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            if (!validateCheckoutForm()) {
                e.preventDefault();
            }
        });
    }
}

function validateRegisterForm() {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    let isValid = true;

    // Reset error messages
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.textContent = '';
    });

    // Validate name
    if (name === '') {
        showError('name', 'Name is required');
        isValid = false;
    }

    // Validate email
    if (email === '') {
        showError('email', 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    }

    // Validate password
    if (password === '') {
        showError('password', 'Password is required');
        isValid = false;
    } else if (password.length < 8) {
        showError('password', 'Password must be at least 8 characters');
        isValid = false;
    }

    // Validate confirm password
    if (confirmPassword === '') {
        showError('confirm_password', 'Please confirm your password');
        isValid = false;
    } else if (password !== confirmPassword) {
        showError('confirm_password', 'Passwords do not match');
        isValid = false;
    }

    return isValid;
}

function validateLoginForm() {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    let isValid = true;

    // Reset error messages
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });

    // Validate email
    if (email === '') {
        showError('email', 'Email is required');
        isValid = false;
    }

    // Validate password
    if (password === '') {
        showError('password', 'Password is required');
        isValid = false;
    }

    return isValid;
}

function validateCheckoutForm() {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const address = document.getElementById('address').value.trim();
    const paymentMethod = document.getElementById('payment_method').value;
    let isValid = true;

    // Reset error messages
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });

    // Validate name
    if (name === '') {
        showError('name', 'Full name is required');
        isValid = false;
    }

    // Validate email
    if (email === '') {
        showError('email', 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    }

    // Validate address
    if (address === '') {
        showError('address', 'Shipping address is required');
        isValid = false;
    }

    // Validate payment method
    if (paymentMethod === '') {
        showError('payment_method', 'Please select a payment method');
        isValid = false;
    }

    return isValid;
}

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.add('is-invalid');
    
    let feedback = field.nextElementSibling;
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentNode.appendChild(feedback);
    }
    
    feedback.textContent = message;
}

function isValidEmail(email) {
    const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return re.test(String(email).toLowerCase());
}

/**
 * Image preview functionality for file uploads
 */
function initImagePreviews() {
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.dataset.preview;
            const preview = document.getElementById(previewId);
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    });
}

/**
 * Confirmation dialogs for delete actions
 */
function initConfirmationDialogs() {
    document.querySelectorAll('a[data-confirm], button[data-confirm]').forEach(element => {
        element.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure you want to perform this action?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Rating system for products
 */
function initRatingSystem() {
    document.querySelectorAll('.rating-input input').forEach(input => {
        input.addEventListener('change', function() {
            const rating = this.value;
            const starsContainer = this.closest('.rating-input');
            starsContainer.querySelectorAll('label').forEach((label, index) => {
                const star = label.querySelector('i');
                if (index < rating) {
                    star.classList.add('bi-star-fill');
                    star.classList.remove('bi-star');
                } else {
                    star.classList.add('bi-star');
                    star.classList.remove('bi-star-fill');
                }
            });
        });
    });
}

/**
 * Search functionality
 */
function initSearchFunctionality() {
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = document.getElementById('search-input').value.trim();
            if (searchTerm) {
                window.location.href = `products.php?search=${encodeURIComponent(searchTerm)}`;
            }
        });
    }
}

/**
 * Toast notification system
 */
function initToastSystem() {
    // Check for any flash messages in the session
    if (document.querySelector('.alert')) {
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                setTimeout(() => bsAlert.close(), 5000);
            });
        }, 100);
    }
}

/**
 * Show toast notification
 * @param {string} message - The message to display
 * @param {string} type - Type of toast (success, danger, warning, info)
 * @param {number} delay - Auto-hide delay in ms (default: 5000)
 */
function showToast(message, type = 'success', delay = 5000) {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.id = toastId;
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: delay });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '1100';
    document.body.appendChild(container);
    return container;
}

/**
 * Utility function to debounce rapid events
 */
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

// Make showToast available globally
window.showToast = showToast;