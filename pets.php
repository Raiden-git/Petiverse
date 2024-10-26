
<?php
//session_start();
include './db.php'; // Database connection

// Handle search
$searchQuery = '';
if (isset($_POST['search'])) {
    $searchQuery = $_POST['search'];
}

// Fetch lost and found pets from the database
$sql = "SELECT * FROM lost_and_found_pets WHERE pet_name LIKE ? ORDER BY date DESC LIMIT 10";
$stmt = $conn->prepare($sql);
$searchParam = "%" . $searchQuery . "%";
$stmt->bind_param("s", $searchParam);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost & Found Pets - Petiverse</title>
    <link rel="stylesheet" href="assets/css/styles.css"> <!-- General styles -->
    <link rel="stylesheet" href="./assets/css/lost_found.css"> <!-- Link to new CSS file -->


</head>
<body>



<?php include './Cus-NavBar/navBar.php'; ?> <!-- Corrected path to include navigation bar -->




    <section class="pets-section">
        <h2>Lost & Found Pets</h2>



            <!-- Submit Pet Button -->
    <a href="submit_pet.php" class="submit-btn">Report a pet</a>

        
        <form method="POST" class="search-form">
            <input type="text" name="search" placeholder="Search for pets..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">Search</button>
        </form>

        <div class="pets-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($pet = $result->fetch_assoc()): ?>
                    <div class="pet-card">
                        <h3><?php echo htmlspecialchars($pet['pet_name']); ?></h3>
                        <p><strong>Type:</strong> <?php echo htmlspecialchars($pet['pet_type']); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($pet['status']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($pet['location']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($pet['date']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No pets found.</p>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Petiverse. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>
