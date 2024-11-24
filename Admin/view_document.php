<?php
// view_document.php

include('../db.php');
include('session_check.php');

if (!isset($_GET['id'])) {
    die('Document ID not provided');
}


$stmt = $conn->prepare("SELECT document, document_type FROM vet_documents WHERE id = ?");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Set appropriate content type based on document type
    $mime_type = "application/pdf"; // Default to PDF
    if (stripos($row['document_type'], 'image') !== false) {
        $mime_type = "image/jpeg";
    }
    
    header("Content-Type: " . $mime_type);
    echo $row['document'];
} else {
    die('Document not found');
}
?>