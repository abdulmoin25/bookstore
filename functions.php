<?php
// includes/functions.php

// Check if functions are already declared
if (!function_exists('sanitize')) {
    function sanitize($data) {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit();
    }
}

if (!function_exists('displayError')) {
    function displayError($error) {
        if (!empty($error)) {
            echo '<div class="alert alert-danger">' . $error . '</div>';
        }
    }
}

if (!function_exists('displaySuccess')) {
    function displaySuccess($message) {
        if (!empty($message)) {
            echo '<div class="alert alert-success">' . $message . '</div>';
        }
    }
}

// Remove formatPrice from here and keep it only in one place

if (!function_exists('validateFileUpload')) {
    function validateFileUpload($file) {
        $errors = [];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error: " . $file['error'];
            return $errors;
        }

        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = "File is too large. Maximum size is " . (MAX_FILE_SIZE / 1024 / 1024) . "MB.";
        }

        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, ALLOWED_TYPES)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }

        return $errors;
    }
}

if (!function_exists('uploadFile')) {
    function uploadFile($file) {
        $errors = validateFileUpload($file);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $destination = UPLOAD_DIR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'errors' => ['Failed to move uploaded file.']];
        }

        return ['success' => true, 'filename' => $filename];
    }
}
?>