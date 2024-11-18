<?php
include './db.php';


session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle search and category filter
$searchQuery = '';
$categoryFilter = 'lost'; // Default to show all pets
if (isset($_POST['search'])) {
    $searchQuery = $_POST['search'];
}
if (isset($_POST['category'])) {
    $categoryFilter = $_POST['category'];
}

// Prepare SQL query based on the category filter
$sql = "SELECT pet_name, pet_type, description, location, status, date, image, contact_info FROM lost_and_found_pets WHERE pet_name LIKE ? AND approved = 1"; // Only approved pets

if ($categoryFilter !== 'all') {
    $sql .= " AND status = ?";
}

$sql .= " ORDER BY date DESC LIMIT 10";

$stmt = $conn->prepare($sql);
$searchParam = "%" . $searchQuery . "%";

if ($categoryFilter !== 'all') {
    $stmt->bind_param("ss", $searchParam, $categoryFilter);
} else {
    $stmt->bind_param("s", $searchParam);
}

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
    <style>
        /* Pop-up styles */
        .popup {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .popup-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            position: relative;
        }
        .popup-content img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .close-popup {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            background: red;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
        }
    </style>
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

    <!-- Category Filter -->
    <div class="category-filter">
        <form method="POST" class="category-form">
            <button type="submit" name="category" value="lost" class="filter-btn <?php if ($categoryFilter === 'lost') echo 'active'; ?>">Lost</button>
            <button type="submit" name="category" value="found" class="filter-btn <?php if ($categoryFilter === 'found') echo 'active'; ?>">Found</button>
            <button type="submit" name="category" value="all" class="filter-btn <?php if ($categoryFilter === 'all') echo 'active'; ?>">All</button>
        </form>
    </div>

    <div class="pets-list">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($pet = $result->fetch_assoc()): ?>
                <div class="pet-card" onclick="openPopup('<?php echo htmlspecialchars($pet['pet_name']); ?>', '<?php echo htmlspecialchars($pet['pet_type']); ?>', '<?php echo htmlspecialchars($pet['description']); ?>', '<?php echo htmlspecialchars($pet['location']); ?>', '<?php echo htmlspecialchars($pet['status']); ?>', '<?php echo htmlspecialchars($pet['date']); ?>', '<?php echo $pet['image'] ? base64_encode($pet['image']) : ''; ?>', '<?php echo htmlspecialchars($pet['contact_info']); ?>')">
                    <!-- Display pet image if available, otherwise show a placeholder -->
                    <?php if (!empty($pet['image'])): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($pet['image']); ?>" alt="<?php echo htmlspecialchars($pet['pet_name']); ?>" class="pet-image">
                    <?php else: ?>
                        <img src="assets/img/placeholder.jpg" alt="No image available" class="pet-image">
                    <?php endif; ?>
                    
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

    <!-- Popup for pet details -->
    <div id="popup" class="popup">
        <div class="popup-content">
            <button class="close-popup" onclick="closePopup()">Close</button>
            <img id="popup-image" src="" alt="Pet Image">
            <h3 id="popup-name"></h3>
            <p><strong>Type:</strong> <span id="popup-type"></span></p>
            <p><strong>Description:</strong> <span id="popup-description" style="white-space: pre-wrap;"></span></p>
            <p><strong>Location:</strong> <span id="popup-location"></span></p>
            <p><strong>Status:</strong> <span id="popup-status"></span></p>
            <p><strong>Date:</strong> <span id="popup-date"></span></p>
            <p><strong>Contact Info:</strong> <span id="popup-contact"></span></p>
        </div>
    </div>
</section>

<footer>
    <div class="footer-content">
        <p>&copy; 2024 Petiverse. All Rights Reserved.</p>
    </div>
</footer>

<script>
    function openPopup(name, type, description, location, status, date, image, contact) {
        document.getElementById('popup-name').innerText = name;
        document.getElementById('popup-type').innerText = type;
        document.getElementById('popup-description').innerHTML = description.replace(/\n/g, "<br>"); // Convert newlines to <br>
        document.getElementById('popup-location').innerText = location;
        document.getElementById('popup-status').innerText = status;
        document.getElementById('popup-date').innerText = date;
        document.getElementById('popup-contact').innerText = contact;
        if (image) {
            document.getElementById('popup-image').src = 'data:image/jpeg;base64,' + image;
        } else {
            document.getElementById('popup-image').src = 'assets/img/placeholder.jpg'; // Fallback image
        }
        document.getElementById('popup').style.display = 'flex'; // Show popup
    }

    function closePopup() {
        document.getElementById('popup').style.display = 'none'; // Hide popup
    }
</script>

</body>
</html>
