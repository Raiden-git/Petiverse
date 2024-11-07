<?php
include 'db.php';

$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$animalFilter = isset($_GET['animal']) ? $_GET['animal'] : '';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT posts.*, 
               COALESCE(users.full_name, users.first_name) AS username 
        FROM posts
        JOIN users ON posts.user_id = users.id";

$filters = [];
if (!empty($categoryFilter)) {
    $filters[] = "posts.category = '" . $conn->real_escape_string($categoryFilter) . "'";
}
if (!empty($animalFilter)) {
    $filters[] = "posts.pet_category = '" . $conn->real_escape_string($animalFilter) . "'";
}
if (!empty($searchTerm)) {
    $searchTermEscaped = $conn->real_escape_string($searchTerm);
    $filters[] = "(posts.title LIKE '%$searchTermEscaped%' OR posts.content LIKE '%$searchTermEscaped%')";
}

// If there are any filters, add them to the SQL query
if (!empty($filters)) {
    $sql .= " WHERE " . implode(" AND ", $filters);
}

// Sort by created date in descending order
$sql .= " ORDER BY posts.created_at DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<a href="post_detail.php?post_id=' . $row['id'] . '" class="post">';
        if (!empty($row['image'])) {
            echo '<img src="' . htmlspecialchars($row['image']) . '" alt="Post Image">';
        }
        echo '<div class="post-content">';
        echo '<h4 class="post-username">' . htmlspecialchars($row['username']) . '</h4>';
        echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
        echo '<div style="display: flex; align-items: center;">';
        echo '<span class="badge ' . strtolower($row['category']) . '">' . htmlspecialchars($row['category']) . '</span>';
        echo '<span class="pet-category-badge">Category: ' . htmlspecialchars($row['pet_category']) . '</span>';
        echo '</div>';
        echo '<p>' . htmlspecialchars($row['content']) . '</p>';
        echo '<span class="post-time">' . date("Y-m-d | h:i A", strtotime($row['created_at'])) . '</span>';
        echo '</div>';
        echo '</a>';
    }
} else {
    echo "<p>No posts available.</p>";
}
?>
