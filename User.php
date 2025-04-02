<?php
require_once __DIR__ . '/Database.php';

class User {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Register new user
    public function register($data) {
        // Validate input
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return false;
        }

        // Check if email exists
        if ($this->isEmailRegistered($data['email'])) {
            return false;
        }

        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("INSERT INTO users 
                                  (name, email, password, address, phone, is_admin) 
                                  VALUES (:name, :email, :password, :address, :phone, :is_admin)");
        
        return $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => $hashed_password,
            ':address' => $data['address'] ?? '',
            ':phone' => $data['phone'] ?? '',
            ':is_admin' => $data['is_admin'] ?? 0
        ]);
    }

    // Login user
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Add role information
            $user['role'] = $user['is_admin'] ? 'admin' : 'user';
            return $user;
        }
        return false;
    }

    // Check if email exists
    public function isEmailRegistered($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    // Check if user is admin
    public function isAdmin($userId) {
        $stmt = $this->db->prepare("SELECT is_admin FROM users WHERE id = :id");
        $stmt->bindParam(":id", $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result && $result['is_admin'] == 1;
    }

    // Get all users (for admin)
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY name");
        return $stmt->fetchAll();
    }

    public function countAll() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return (int)$stmt->fetchColumn();
    }

    // Get user by ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Update user
    public function update($id, $data) {
        $query = "UPDATE users SET name = :name, email = :email, address = :address, phone = :phone";
        $params = [
            ':id' => $id,
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':address' => $data['address'],
            ':phone' => $data['phone']
        ];

        if (!empty($data['password'])) {
            $query .= ", password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (isset($data['is_admin'])) {
            $query .= ", is_admin = :is_admin";
            $params[':is_admin'] = $data['is_admin'];
        }

        $query .= " WHERE id = :id";

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    // Delete user
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}