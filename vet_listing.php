<?php
session_start();
include 'db.php'; // Database connection

// Fetch all approved vets
$query = "SELECT id, name, qualification, specialization, experience, clinic_name, consultation_fee, services, profile_picture, rating 
          FROM vets 
          WHERE is_approved = 1";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veterinarian Network</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --text-color: #333;
            --background-color: #f4f6f7;
            --card-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--primary-color);
            font-weight: 600;
            position: relative;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--secondary-color);
        }

        .vet-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .vet-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .vet-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .vet-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        .vet-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-color);
            margin: 0 auto 1rem;
            transition: transform 0.3s ease;
        }

        .vet-card:hover .vet-avatar {
            transform: scale(1.05);
        }

        .vet-name {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .vet-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #666;
        }

        .view-profile-btn {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.6rem 1.2rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .view-profile-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
        }

        .rating {
            color: #f39c12;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .rating i {
            margin-right: 0.25rem;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
<?php include 'Cus-NavBar/navBar.php'; ?>
    <div class="container">
        <h1 class="page-title">Veterinarian Network</h1>
        <div class="vet-grid">
            <?php while ($vet = $result->fetch_assoc()) { ?>
                <div class="vet-card">
                    <?php 
                    $profilePicture = !empty($vet['profile_picture']) 
                        ? 'data:image/jpeg;base64,'.base64_encode($vet['profile_picture']) 
                        : 'default-profile.png'; 
                    ?>
                    <img src="<?php echo $profilePicture; ?>" alt="<?php echo $vet['name']; ?>" class="vet-avatar">
                    
                    <h2 class="vet-name"><?php echo $vet['name']; ?></h2>
                    
                    <div class="rating">
                        <i class="fas fa-star"></i> <?php echo number_format($vet['rating'], 1); ?>
                    </div>

                    <div class="vet-details">
                        <strong>Specialization:</strong> 
                        <span><?php echo $vet['specialization'] ?: 'General'; ?></span>
                        
                        <strong>Experience:</strong> 
                        <span><?php echo $vet['experience']; ?> years</span>
                        
                        <strong>Clinic:</strong> 
                        <span><?php echo $vet['clinic_name']; ?></span>
                        
                        <strong>Consultation Fee:</strong> 
                        <span>LKR <?php echo $vet['consultation_fee']; ?></span>
                    </div>

                    <a href="vet_profile.php?id=<?php echo $vet['id']; ?>" class="view-profile-btn">
                        View Full Profile
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
<?php include 'footer.php'; ?>
</body>
</html>