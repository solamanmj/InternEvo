<?php
require_once 'conn.php';

if (isset($_GET['degree_id'])) {
    $degree_id = filter_var($_GET['degree_id'], FILTER_SANITIZE_NUMBER_INT);
    
    try {
        $stmt = $conn->prepare("
            SELECT f.id, f.field_name 
            FROM fields_of_study f 
            JOIN degree_fields df ON f.id = df.field_id 
            WHERE df.degree_id = ?
            ORDER BY f.field_name
        ");
        $stmt->execute([$degree_id]);
        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($fields);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Degree ID not provided']);
}