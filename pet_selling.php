<?php
include './db.php';
session_start();

// Initialize variables for search and filters
$search_query = '';
$filter_type = '';
$filter_breed = '';

// Check if the search form was submitted
if (isset($_POST['search'])) {
    $search_query = $_POST['search_query'];
}

// Check if filter form was submitted
if (isset($_POST['filter'])) {
    $filter_type = $_POST['pet_type'];
}

// Query to fetch pet ads based on search and filters
$query = "SELECT * FROM pet_selling_ads WHERE status = 'approved'";

if (!empty($search_query)) {
    $search_query = $conn->real_escape_string($search_query);
    $query .= " AND (pet_name LIKE '%$search_query%' OR pet_type LIKE '%$search_query%' OR pet_breed LIKE '%$search_query%')";
}

if (!empty($filter_type)) {
    $filter_type = $conn->real_escape_string($filter_type);
    $query .= " AND pet_type = '$filter_type'";
}

if (!empty($filter_breed)) {
    $filter_breed = $conn->real_escape_string($filter_breed);
    $query .= " AND pet_breed = '$filter_breed'";
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Selling</title>
    <link rel="stylesheet" href="assets/css/pet_selling.css">
    <link rel="stylesheet" href="./assets/css/scrollbar.css">
    <script>
        function openPopup(detailsId) {
            document.getElementById(detailsId).style.display = 'flex';
        }

        function closePopup(detailsId) {
            document.getElementById(detailsId).style.display = 'none';
        }

        function changeImage(sliderId, direction) {
            var slider = document.getElementById(sliderId);
            var images = slider.getElementsByClassName("slider-image");
            var currentIndex = 0;
            for (var i = 0; i < images.length; i++) {
                if (images[i].style.display === "block") {
                    currentIndex = i;
                    break;
                }
            }
            images[currentIndex].style.display = "none";
            if (direction === 'next') {
                currentIndex = (currentIndex + 1) % images.length;
            } else {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
            }
            images[currentIndex].style.display = "block";
        }
    </script>
</head>
<body>
<?php include './Cus-NavBar/navBar.php'; ?>

<header>
    <h1>Pet Selling Platform</h1>
    <a href="post_pet_add.php" class="post-ad-btn">Post a New Pet Ad</a>
    <a href="my_ads.php" class="post-ad-btn">My Ads</a>
</header>

<main>
    <div class="filter-bar">
        <form method="POST">
            <input type="text" name="search_query" placeholder="Search by pet name, type, or breed" value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit" name="search">Search</button>
        </form>

        <form method="POST">
            <select name="pet_type">
                <option value="">Select Pet Type</option>
                <option value="dog" <?= $filter_type == 'dog' ? 'selected' : '' ?>>Dog</option>
                <option value="cat" <?= $filter_type == 'cat' ? 'selected' : '' ?>>Cat</option>
                <option value="bird" <?= $filter_type == 'bird' ? 'selected' : '' ?>>bird</option>
                <option value="hamster" <?= $filter_type == 'hamster' ? 'selected' : '' ?>>Hamster</option>
                <option value="fish" <?= $filter_type == 'fish' ? 'selected' : '' ?>>Fish</option>
                <option value="other" <?= $filter_type == 'other' ? 'selected' : '' ?>>other</option>
            </select>
            
            <button type="submit" name="filter">Filter</button>
        </form>
    </div>

    <div class="ad-list">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="ad-card" onclick="openPopup('details-<?= $row['ad_id'] ?>')">
                    <div class="slider" id="slider-<?= $row['ad_id'] ?>">
                        <img src="data:image/jpeg;base64,<?= base64_encode($row['image1']) ?>" alt="<?= htmlspecialchars($row['pet_name']) ?>" class="slider-image" style="display: block; width: 100%; height: auto;">
                        <img src="data:image/jpeg;base64,<?= base64_encode($row['image2']) ?>" alt="<?= htmlspecialchars($row['pet_name']) ?>" class="slider-image" style="display: none; width: 100%; height: auto;">
                    </div>
                    <h3><?= htmlspecialchars($row['pet_name']) ?></h3>
                    <p>Type: <?= htmlspecialchars($row['pet_type']) ?></p>
                    <p>Breed: <?= htmlspecialchars($row['pet_breed']) ?></p>
                    <p>Price: LKR.<?= htmlspecialchars($row['price']) ?></p>
                </div>

                <!-- Popup for showing pet details -->
                <div id="details-<?= $row['ad_id'] ?>" class="popup" style="display: none;">
                    <div class="popup-content">
                        <span class="close-btn" onclick="closePopup('details-<?= $row['ad_id'] ?>')">&times;</span>
                        <div class="slider" id="popup-slider-<?= $row['ad_id'] ?>">
                            <img src="data:image/jpeg;base64,<?= base64_encode($row['image1']) ?>" alt="<?= htmlspecialchars($row['pet_name']) ?>" class="slider-image" style="display: block; width: 100%; height: auto;">
                            <img src="data:image/jpeg;base64,<?= base64_encode($row['image2']) ?>" alt="<?= htmlspecialchars($row['pet_name']) ?>" class="slider-image" style="display: none; width: 100%; height: auto;">
                        </div>
                        <h3><?= htmlspecialchars($row['pet_name']) ?></h3>
                        <p>Type: <?= htmlspecialchars($row['pet_type']) ?></p>
                        <p>Breed: <?= htmlspecialchars($row['pet_breed']) ?></p>
                        <p>Age: <?= htmlspecialchars($row['age']) ?> years</p>
                        <p>Price: $<?= htmlspecialchars($row['price']) ?></p>
                        <p>Contact: <?= htmlspecialchars($row['contact_number']) ?: 'Not provided' ?></p>
                        <!-- Arrow buttons for navigation -->
                        <div class="slider-controls">
                            <button onclick="changeImage('popup-slider-<?= $row['ad_id'] ?>', 'prev')" class="slider-prev">&#8592;</button>
                            <button onclick="changeImage('popup-slider-<?= $row['ad_id'] ?>', 'next')" class="slider-next">&#8594;</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No ads found. Try searching or filtering with different criteria.</p>
        <?php endif; ?>
    </div>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
