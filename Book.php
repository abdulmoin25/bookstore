<?php
require_once __DIR__ . '/Database.php';

class Book {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    /**
     * Get all books with optional filters and pagination
     */
    public function getAll($options = []) {
        $query = "SELECT b.*, c.name as category_name 
                 FROM books b 
                 LEFT JOIN categories c ON b.category_id = c.id 
                 WHERE 1=1";
        $params = [];

        // Apply filters
        if (!empty($options['category'])) {
            $query .= " AND b.category_id = :category";
            $params[':category'] = (int)$options['category'];
        }

        if (!empty($options['search'])) {
            $query .= " AND (b.title LIKE :search OR b.author LIKE :search)";
            $params[':search'] = '%' . $options['search'] . '%';
        }

        if (!empty($options['min_price'])) {
            $query .= " AND b.price >= :min_price";
            $params[':min_price'] = (float)$options['min_price'];
        }

        if (!empty($options['max_price'])) {
            $query .= " AND b.price <= :max_price";
            $params[':max_price'] = (float)$options['max_price'];
        }

        $query .= " ORDER BY b.title";

        // Apply pagination
        if (!empty($options['limit'])) {
            $query .= " LIMIT :limit";
            $params[':limit'] = (int)$options['limit'];
            
            if (!empty($options['offset'])) {
                $query .= " OFFSET :offset";
                $params[':offset'] = (int)$options['offset'];
            }
        }

        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get a single book by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT b.*, c.name as category_name 
                                  FROM books b 
                                  LEFT JOIN categories c ON b.category_id = c.id 
                                  WHERE b.id = :id");
        $stmt->bindValue(":id", (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get all categories
     */
    public function getCategories() {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Count all books (for pagination)
     */
    public function countAll($filters = []) {
        $query = "SELECT COUNT(*) FROM books WHERE 1=1";
        $params = [];

        // Similar filter logic as getAll()
        // ...

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
?>