<?php
include('../db.php');
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM moderator WHERE id = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode([]);
    }
}
?>
