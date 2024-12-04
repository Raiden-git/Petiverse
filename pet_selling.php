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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" href="assets/css/pet_selling.css"> -->
    <link rel="stylesheet" href="./assets/css/scrollbar.css">
    <script>

        function openPopup(detailsId) {
            const popup = document.getElementById(detailsId);
            popup.style.display = 'flex';
            setTimeout(() => {
                popup.classList.add('show');
            }, 10);
        }

        function closePopup(detailsId) {
            const popup = document.getElementById(detailsId);
            popup.classList.remove('show');
            setTimeout(() => {
                popup.style.display = 'none';
            }, 300);
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
    <style>
        /* Pet Selling Platform Main Stylesheet */
body {
    font-family: 'Inter', sans-serif;
    background-color: #f6f3e8;
    margin: 0;
    padding: 0;
    color: #333;
    line-height: 1.6;
}

header {
    background-color: #3b82f6;
    color: white;
    text-align: center;
    padding: 2rem 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

h1 {
    margin-bottom: 1rem;
    font-size: 2.5rem;
    font-weight: 700;
    margin-left: 3rem;
}

.post-ad-btn {

    background-color: #DA8359;
    color: white;
    border: none;
    padding: 0.8rem 1.5rem;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.post-ad-btn:hover {
    background-color: #f0f9ff;
    transform: scale(1.05);
}

/* Existing styles remain the same */
.filter-bar {
    background-color: white;
    padding: 1.5rem;
    margin: 1rem 3rem;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    border: 1px solid #e6e9ee;
    flex-wrap: wrap;
}
.filter-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-filter-container {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-container,
.filter-container {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.actions-container {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.filter-bar input,
.filter-bar select {
    padding: 0.75rem 1rem;
    border: 2px solid #e0e6ed;
    border-radius: 20px;
    font-family: 'Inter', sans-serif;
    font-size: 0.95rem;
    color: #2d3748;
    transition: all 0.3s ease;
    outline: none;
    background-color: #f8fafc;
    min-width: 250px;
}

.filter-bar input:focus,
.filter-bar select:focus {
    border-color: #b36a47;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    background-color: white;
}

.filter-bar button {
    background-color: #DA8359; 
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.actions-container .post-ad-btn {
    display: inline-block;
    background-color: #DA8359;
    color: white;
    text-decoration: none;
    padding: 0.75rem 1.5rem;
    border-radius: 20px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-bar button:hover,
.actions-container .post-ad-btn:hover {
    background-color: #b36a47;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(218, 133, 89, 0.3);
}

.filter-bar button:hover {
    background-color: #b36a47;
}

.actions-container .post-ad-btn:hover {
    background-color: #b36a47;
}

.search-container form,
.filter-container form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.search-container input,
.filter-container select {
    flex-grow: 1;
    margin-right: 0.5rem; 
}

.search-container button,
.filter-container button {
    white-space: nowrap;
    margin: 0;
}

@media (max-width: 1024px) {
    .search-container form,
    .filter-container form {
        width: 100%;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .search-container input,
    .filter-container select {
        width: 100%;
        margin-right: 0;
    }
    
    .search-container button,
    .filter-container button {
        width: 100%;
    }
}

.ad-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    padding: 1rem;
    margin: 2rem;
}

.ad-card {
    background-color: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
}

.ad-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}

.ad-card .slider {
    position: relative;
    width: 100%;
    aspect-ratio: 4/3;
    overflow: hidden;
}

.ad-card .slider-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ad-card h3 {
    padding: 1rem;
    margin: 0;
    background-color: #f9fafb;
    font-weight: 600;
}

.ad-card p {
    padding: 0 1rem;
    margin: 0.5rem 0;
}

.popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    backdrop-filter: blur(5px);
}

.popup-content {
    background-color: white;
    border-radius: 16px;
    max-width: 700px;
    width: 90%;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    position: relative;
    display: grid;
    grid-template-columns: 1fr 1fr;
    max-height: 80vh;
}

.popup-slider {
    position: relative;
    grid-column: 1;
}

.popup-details {
    grid-column: 2;
    padding: 2rem;
    background-color: #f9fafb;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.slider-container {
    position: relative;
    width: 100%;
    aspect-ratio: 1/1;
    overflow: hidden;
}

.slider-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.slider-controls {
    position: absolute;
    top: 50%;
    width: 100%;
    display: flex;
    justify-content: space-between;
    transform: translateY(-50%);
    padding: 0 1rem;
}

.slider-controls button {
    background-color: rgba(255, 255, 255, 0.7);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s;
}

.slider-controls button:hover {
    background-color: rgba(255, 255, 255, 0.9);
}

.close-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 40px;
    height: 40px;
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s;
}

.close-btn:hover {
    background-color: rgba(0, 0, 0, 0.2);
}

.popup-details h3 {
    font-size: 1.8rem;
    margin-bottom: 1rem;
    color: #2c3e50;
}

.popup-details .detail-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}

.detail-item .label {
    font-weight: 600;
    color: #718096;
}

.detail-item .value {
    color: #2d3748;
}

@media (max-width: 768px) {
    .popup-content {
        grid-template-columns: 1fr;
        max-height: none;
    }

    .popup-slider,
    .popup-details {
        grid-column: 1;
    }
}
    </style>
</head>
<body>
<?php include './Cus-NavBar/navBar.php'; ?>
<h1>Pet Selling Platform</h1>

<div class="filter-bar">
    <div class="search-filter-container">
        <div class="search-container">
            <form method="POST">
                <input type="text" name="search_query" placeholder="Search by pet name, type, or breed" value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit" name="search">Search</button>
            </form>
        </div>

        <div class="filter-container">
            <form method="POST">
                <select name="pet_type">
                    <option value="">Select Pet Type</option>
                    <option value="dog" <?= $filter_type == 'dog' ? 'selected' : '' ?>>Dog</option>
                    <option value="cat" <?= $filter_type == 'cat' ? 'selected' : '' ?>>Cat</option>
                    <option value="bird" <?= $filter_type == 'bird' ? 'selected' : '' ?>>Bird</option>
                    <option value="hamster" <?= $filter_type == 'hamster' ? 'selected' : '' ?>>Hamster</option>
                    <option value="fish" <?= $filter_type == 'fish' ? 'selected' : '' ?>>Fish</option>
                    <option value="other" <?= $filter_type == 'other' ? 'selected' : '' ?>>Other</option>
                </select>
                <button type="submit" name="filter">Filter</button>
            </form>
        </div>
    </div>

    <div class="actions-container">
        <a href="post_pet_add.php" class="post-ad-btn">Post a New Pet Ad</a>
        <a href="my_ads.php" class="post-ad-btn">My Ads</a>
    </div>
</div>


    
    

<main>
    

    <div class="ad-list">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="ad-card" onclick="openPopup('details-<?= $row['ad_id'] ?>')">
                    <div class="slider" id="slider-<?= $row['ad_id'] ?>">
                        <img src="data:image/jpeg;base64,<?= base64_encode($row['image1']) ?>" alt="<?= htmlspecialchars($row['pet_name']) ?>" class="slider-image" style="display: block; width: 100%; height: auto;">
                        <img src="data:image/jpeg;base64,<?= base64_encode($row['image2']) ?>" alt="<?= htmlspecialchars($row['pet_name']) ?>" class="slider-image" style="display: none; width: 100%; height: auto;">
                    </div>
                    <h3> <strong><?= htmlspecialchars($row['pet_name']) ?></strong></h3>
                    <p>Type: <?= htmlspecialchars($row['pet_type']) ?></p>
                    <p>Breed: <?= htmlspecialchars($row['pet_breed']) ?></p>
                    <p>Price: <strong> LKR <?= htmlspecialchars($row['price']) ?></strong></p>
                </div>

                <!-- Popup for showing pet details -->
                <div id="details-<?= $row['ad_id'] ?>" class="popup">
                    <div class="popup-content">
                        <div class="popup-slider">
                            <div class="slider-container" id="popup-slider-<?= $row['ad_id'] ?>">
                                <img src="data:image/jpeg;base64,<?= base64_encode($row['image1']) ?>" alt="<?= htmlspecialchars($row['pet_name']) ?>" class="slider-image" style="display: block;">
                                <img src="data:image/jpeg;base64,<?= base64_encode($row['image2']) ?>" alt="<?= htmlspecialchars($row['pet_name']) ?>" class="slider-image" style="display: none;">
                            </div>
                            <div class="slider-controls">
                                <button onclick="changeImage('popup-slider-<?= $row['ad_id'] ?>', 'prev')">&#8592;</button>
                                <button onclick="changeImage('popup-slider-<?= $row['ad_id'] ?>', 'next')">&#8594;</button>
                            </div>
                        </div>
                        <div class="popup-details">
                            <span class="close-btn" onclick="closePopup('details-<?= $row['ad_id'] ?>')">&times;</span>
                            <h3><?= htmlspecialchars($row['pet_name']) ?></h3>
                            <div class="detail-item">
                                <span class="label">Type</span>
                                <span class="value"><?= htmlspecialchars($row['pet_type']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Breed</span>
                                <span class="value"><?= htmlspecialchars($row['pet_breed']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Age</span>
                                <span class="value"><?= htmlspecialchars($row['age']) ?> years</span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Price</span>
                                <span class="value">LKR <?= htmlspecialchars($row['price']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Contact</span>
                                <span class="value"><?= htmlspecialchars($row['contact_number']) ?: 'Not provided' ?></span>
                            </div>
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
