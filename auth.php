<?php
require_once 'User.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

function isAdmin() {
    if (!isLoggedIn()) return false;
    
    // Check if role is set in session
    if (isset($_SESSION['user']['role'])) {
        return $_SESSION['user']['role'] === 'admin';
    }
    
    // If not, check database
    $userModel = new User();
    return $userModel->isAdmin($_SESSION['user']['id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: index.php");
        exit();
    }
}

function loginUser($user) {
    // Ensure role is set
    if (!isset($user['role'])) {
        $user['role'] = $user['is_admin'] ? 'admin' : 'user';
    }
    
    $_SESSION['user'] = $user;
    // Remove sensitive data
    unset($_SESSION['user']['password']);
}

function logoutUser() {
    unset($_SESSION['user']);
    session_destroy();
}

function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

function redirectIfNotAdmin() {
    requireAdmin();
}
?>