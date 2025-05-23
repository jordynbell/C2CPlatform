<?php

require_once __DIR__ . '/../../lib/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    

    // Acquires the image from the database
    $stmt = $conn->prepare("SELECT image FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();

        $imageData = $row['image'];
        $info = @getimagesizefromstring($imageData);
        
        if ($info !== false) {

            $conn->close();
            
            // Set the correct content type based on detected format
            header("Content-Type: " . $info['mime']);
            echo $imageData;
            exit;
        } else {

            $conn->close();

            // Fallback to jpeg if mime type cannot be detected.
            header("Content-Type: image/jpeg");
            echo $imageData;
            exit;
        }
    }
}

$conn->close();

header("HTTP/1.0 404 Not Found");
?>