<?php
include('../db.php');

if (isset($_GET['main_category'])) {
    $main_category = $conn->real_escape_string($_GET['main_category']);
    $sql = "SELECT id, sub_category FROM subcategories WHERE main_category='$main_category'";
    $result = $conn->query($sql);
    
    $subcategories = [];
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row;
    }
    
    echo json_encode($subcategories);
}

$conn->close();
?>
