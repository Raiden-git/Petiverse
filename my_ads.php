<?php
session_start();
include './db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$message = '';

// Fetch ads posted by the logged-in user
$query = "SELECT * FROM pet_selling_ads WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Ads</title>
    <link rel="stylesheet" href="assets/css/pet_selling.css">
    <link rel="stylesheet" href="./assets/css/scrollbar.css">
    <style>
        .my-ads {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .my-ad-card {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #fff;
            width: 300px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .my-ad-card img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .delete-btn {
            display: inline-block;
            margin: 5px;
            padding: 8px 15px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            background-color: #e74c3c;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <?php include './Cus-NavBar/navBar.php'; ?>
    <header>
        <h1>My Pet Ads</h1>
    </header>

    <main>
        <div class="my-ads">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="my-ad-card">
                        <img src="data:image/jpeg;base64,<?= base64_encode($row['image1']) ?>" alt="<?= htmlspecialchars($row['pet_name']) ?>">
                        <h3><?= htmlspecialchars($row['pet_name']) ?></h3>
                        <p>Type: <?= htmlspecialchars($row['pet_type']) ?></p>
                        <p>Breed: <?= htmlspecialchars($row['pet_breed']) ?></p>
                        <p>Price: LKR <?= htmlspecialchars($row['price']) ?></p>
                        <p>Status: <?= htmlspecialchars($row['status']) ?></p>

                        <!-- Delete button -->
                        <form method="POST" action="delete_ad.php" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= $row['ad_id'] ?>">
                            <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this ad?');">Delete</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>You have not posted any ads yet. <a href="post_pet_add.php">Post a new ad</a> now!</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
