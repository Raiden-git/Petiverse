<?php
include './db.php';
session_start();


// Fetch user-specific posts
$sqlUserPosts = "SELECT id, pet_name, pet_type, description, location, status, date, image FROM lost_and_found_pets WHERE id = ? AND approved = 1 ORDER BY date DESC";
$stmtUserPosts = $conn->prepare($sqlUserPosts);
$stmtUserPosts->bind_param("i", $userId);
$stmtUserPosts->execute();
$resultUserPosts = $stmtUserPosts->get_result();


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
$sql = "SELECT pet_name, pet_type, description, location, status, date, image, contact_info FROM lost_and_found_pets WHERE pet_name LIKE ? AND approved = 1";

if ($categoryFilter !== 'all') {
    $sql .= " AND status = ?";
}

$sql .= " ORDER BY date DESC";

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

    <title>Lost And Found</title>
    <link rel="stylesheet" href="assets/css/styles.css"> <!-- General styles -->
    <link rel="stylesheet" href="./assets/css/lost_found.css"> <!-- Link to new CSS file -->
    
    <title>Lost & Found Pets - Petiverse</title>
    <!-- <link rel="stylesheet" href="assets/css/styles.css"> 
    <link rel="stylesheet" href="./assets/css/lost_found.css">  -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #DA8359;
            --secondary-color: #F5A623;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --background-color: #f6f3e8;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }

        .pets-section {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .pets-section h2 {
            text-align: center;
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .pets-section h2:after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--primary-color);
            margin: 1rem auto;
            border-radius: 2px;
        }

        .hii {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            padding: 2rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .submit-btn {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all var(--transition-speed);
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .my-posts-btn {
            display: inline-block;
            background: white;
            color: black;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all var(--transition-speed);
            margin-bottom: 2rem;
            margin-left: 1rem;
            box-shadow: var(--card-shadow);
        }

        .my-posts-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .search-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .search-form input {
            flex: 1;
            padding: 0.8rem 1.2rem;
            border: 2px solid #e1e1e1;
            border-radius: 25px;
            font-size: 1rem;
            transition: border-color var(--transition-speed);
        }

        .search-form input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-form button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all var(--transition-speed);
        }

        .search-form button:hover {
            background: #357abd;
        }

        .category-filter {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .category-form {
            display: flex;
            gap: 1rem;
        }

        .filter-btn {
            background: white;
            border: 2px solid #e1e1e1;
            padding: 0.6rem 1.2rem;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 500;
            transition: all var(--transition-speed);
        }

        .filter-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .filter-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pets-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            padding: 1rem 0;
        }

        .pet-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: transform var(--transition-speed);
            cursor: pointer;
        }

        .pet-card:hover {
            transform: translateY(-5px);
        }

        .pet-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .pet-card-content {
            padding: 1.5rem;
        }

        .pet-card h3 {
            color: #333;
            margin: 0 0 1rem 0;
            font-size: 1.25rem;
        }

        .pet-card p {
            margin: 0.5rem 0;
            color: #666;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .status-lost {
            background-color: #ffebee;
            color: var(--danger-color);
        }

        .status-found {
            background-color: #e8f5e9;
            color: var(--success-color);
        }

        /* Enhanced popup styles */
        .popup {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .popup-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            max-width: 700px;
            width: 90%;
            height: 90%;
            position: relative;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow-y: auto;
        }

        .popup-content img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .close-popup {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-speed);
        }

        .close-popup:hover {
            transform: rotate(90deg);
            background: #c82333;
        }

        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
        }

        @media (max-width: 768px) {
            .pets-section h2 {
                font-size: 2rem;
            }

            .search-form {
                flex-direction: column;
            }

            .category-form {
                flex-wrap: wrap;
                justify-content: center;
            }

            .popup-content {
                padding: 1rem;
                margin: 1rem;
            }
        }

</style>
</head>
<body>
<?php include './Cus-NavBar/navBar.php'; ?>

<section class="pets-section">
    <h2>Lost & Found Pets</h2>

    <!-- Submit Pet Button -->
    <div class="hii">

    <a href="Lost_found_myposts.php" class="my-posts-btn">
        <i class="fas fa-user"></i> My Posts
    </a>

    <a href="submit_pet.php" class="submit-btn">
        <i class="fas fa-plus-circle"></i> Report a Pet
    </a>

    
    </div>

    <form method="POST" class="search-form">
        <input type="text" name="search" placeholder="Search for pets..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button type="submit"><i class="fas fa-search"></i> Search</button>
    </form>

    <div class="category-filter">
        <form method="POST" class="category-form">
            <button type="submit" name="category" value="lost" class="filter-btn <?php if ($categoryFilter === 'lost') echo 'active'; ?>">
                <i class="fas fa-search-location"></i> Lost
            </button>
            <button type="submit" name="category" value="found" class="filter-btn <?php if ($categoryFilter === 'found') echo 'active'; ?>">
                <i class="fas fa-paw"></i> Found
            </button>
            <button type="submit" name="category" value="all" class="filter-btn <?php if ($categoryFilter === 'all') echo 'active'; ?>">
                <i class="fas fa-list"></i> All
            </button>
        </form>
    </div>

    <div class="pets-list">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($pet = $result->fetch_assoc()): ?>
                <div class="pet-card" onclick="openPopup('<?php echo htmlspecialchars($pet['pet_name']); ?>', '<?php echo htmlspecialchars($pet['pet_type']); ?>', '<?php echo htmlspecialchars($pet['description']); ?>', '<?php echo htmlspecialchars($pet['location']); ?>', '<?php echo htmlspecialchars($pet['status']); ?>', '<?php echo htmlspecialchars($pet['date']); ?>', '<?php echo $pet['image'] ? base64_encode($pet['image']) : ''; ?>', '<?php echo htmlspecialchars($pet['contact_info']); ?>')">
                    <?php if (!empty($pet['image'])): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($pet['image']); ?>" alt="<?php echo htmlspecialchars($pet['pet_name']); ?>" class="pet-image">
                    <?php else: ?>
                        <img src="assets/img/placeholder.jpg" alt="No image available" class="pet-image">
                    <?php endif; ?>
                    
                    <div class="pet-card-content">
                        <span class="status-badge <?php echo $pet['status'] === 'lost' ? 'status-lost' : 'status-found'; ?>">
                            <?php echo ucfirst(htmlspecialchars($pet['status'])); ?>
                        </span>
                        <h3><?php echo htmlspecialchars($pet['pet_name']); ?></h3>
                        <p><i class="fas fa-dog"></i> <strong>Type:</strong> <?php echo htmlspecialchars($pet['pet_type']); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <?php echo htmlspecialchars($pet['location']); ?></p>
                        <p><i class="far fa-calendar-alt"></i> <strong>Date:</strong> <?php echo htmlspecialchars($pet['date']); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-results">No pets found. Try adjusting your search criteria.</p>
        <?php endif; ?>
    </div>

    <div id="popup" class="popup" onclick="if(event.target === this) closePopup()">
        <div class="popup-content">
            <button class="close-popup" onclick="closePopup()"><i class="fas fa-times"></i></button>
            <img id="popup-image" src="" alt="Pet Image">
            <h3 id="popup-name"></h3>
            <p><i class="fas fa-dog"></i> <strong>Type:</strong> <span id="popup-type"></span></p>
            <p><i class="fas fa-align-left"></i> <strong>Description:</strong> <span id="popup-description" style="white-space: pre-wrap;"></span></p>
            <p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <span id="popup-location"></span></p>
            <p><i class="fas fa-info-circle"></i> <strong>Status:</strong> <span id="popup-status"></span></p>
            <p><i class="far fa-calendar-alt"></i> <strong>Date:</strong> <span id="popup-date"></span></p>
            <p><i class="fas fa-phone"></i> <strong>Contact Info:</strong> <span id="popup-contact"></span></p>
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
        document.getElementById('popup-description').innerHTML = description.replace(/\n/g, "<br>"); 
        document.getElementById('popup-location').innerText = location;
        document.getElementById('popup-status').innerText = status;
        document.getElementById('popup-date').innerText = date;
        document.getElementById('popup-contact').innerText = contact;
        if (image) {
            document.getElementById('popup-image').src = 'data:image/jpeg;base64,' + image;
        } else {
            document.getElementById('popup-image').src = 'assets/img/placeholder.jpg'; 
        }
        document.getElementById('popup').style.display = 'flex'; 
    }

    function closePopup() {
        document.getElementById('popup').style.display = 'none'; 
    }
</script>

</body>
</html>
