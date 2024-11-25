<?php
require 'google-config.php'; // Google Client configuration
require_once 'db.php'; // Database connection



$error_message = '';
$google_login_url = $google_client->createAuthUrl(); // Google login URL


/* $profile_pic = null;
if(isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    if ($conn) {
        // Updated query to select all columns
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $_SESSION['user_data'] = $user; // Store user data in session
        $profile_pic = $user['profile_pic'];
    } else {
        error_log("Database connection failed in navBar.php");
    }
} */

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.cdnfonts.com/css/cheri" rel="stylesheet">                


<style>
    /* General Styles for Header */
header {
    background-color: #ECDFCC; /* Light background */
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
    flex-wrap: wrap;
}

.logo h1 {
    margin: 0;
    font-size: 28px;
}

.logo a {
    font-family: 'Cheri', sans-serif;
    text-decoration: none;
    color: #DA8359; /* Vibrant orange */
}

/* Navigation Styles */
nav ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
    display: flex;
    gap: 20px;
}

nav a {
    text-decoration: none;
    font-size: 15px;
    color: black;
    padding: 5px 9px;
    transition: color 0.3s ease, background-color 0.3s ease, border-bottom 0.3s ease, transform 0.3s ease;
    position: relative;
    display: inline-block;
}

nav a::before {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: 0;
    left: 0;
    background-color: #DA8359; 
    visibility: hidden;
    transition: all 0.3s ease-in-out;
}

nav a:hover::before {
    visibility: visible;
    width: 100%; 
}

nav a:hover {
    color: #DA8359; 
    transform: translateY(-3px);
}


.login .loginbutton {
    text-decoration: none;
    font-size: 16px;
    color: black;
    margin-left: 20px;
    background-color: #da845970;
    border: 2px solid #DA8359;
    padding: 8px 15px;
    border-radius: 25px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.login .loginbutton:hover {
    background-color: #DA8359;
    color: #FCFAEE;
}


/* #logoutLink{
    text-decoration: none;
    font-size: 16px;
    color: black;
    margin-left: 20px;
    border: 2px solid #d14035f8;
    background-color: #d1403557;
    padding: 8px 15px;
    border-radius: 25px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

#logoutLink:hover {
    background-color: #d14035f8;
    color: #FCFAEE;
} */



.profile-container {
        position: relative;
        display: inline-block;
    }

    .profile-pic {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        object-fit: cover;
        border: 2px solid #DA8359;
    }

    .profile-dropdown {
        position: absolute;
        right: 0;
        top: 50px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 10px 0;
        min-width: 180px;
        display: none;
        z-index: 1000;
    }

    .profile-dropdown.show {
        display: block;
    }

    .profile-dropdown-item {
        padding: 8px 20px;
        display: block;
        color: black;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .profile-dropdown-item:hover {
        background-color: #f5f5f5;
        color: #DA8359;
    }

    .profile-dropdown-divider {
        height: 1px;
        background-color: #e0e0e0;
        margin: 8px 0;
    }

    .login {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    @media (max-width: 768px) {
        .profile-dropdown {
            right: -50px;
        }
    }





/* Responsive Design */
@media (max-width: 768px) {
    nav ul {
        flex-direction: column;
        gap: 10px;
        align-items: center;
        padding-top: 10px;
    }

    .logo h1 {
        font-size: 24px;
    }

    .login a {
        font-size: 14px;
        margin-left: 0;
        padding: 8px 10px;
    }

    header {
        flex-direction: column;
        align-items: flex-start;
        padding: 15px;
    }
}

@media (max-width: 480px) {
    nav a {
        font-size: 14px;
    }

    .login a {
        font-size: 12px;
    }

    .logo h1 {
        font-size: 20px;
    }
}

</style>


</head>
<body>
<header>
    <div class="logo">
        <h1> <a href="index.php">Petiverse</a></h1>
    </div>
    <nav>
        <ul>
            
            <li><a href="shop.php">Shop</a></li>
            <li><a href="vets_map.php">Vet Services</a></li>
            <li><a href="#">Day Care</a></li>
            <li><a href="community.php">Community</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li><a href="./pets.php">Lost & Found Pets</a></li>
            <li><a href="./pet_selling.php" >Pet Selling</a></li>
            <li><a href="about.php">About Us</a></li>
        </ul>
    </nav>
    <div class="login">
    <?php if(isset($_SESSION['username'])): ?>
        <div class="profile-container">
            <img src="<?php echo isset($profile_pic) && !empty($profile_pic) ? 'data:image/jpeg;base64,' . base64_encode($profile_pic) : './assets/img/default.webp'; ?>" 
                 alt="Profile" 
                 class="profile-pic" 
                 id="profilePic">
            <div class="profile-dropdown" id="profileDropdown">
                <div class="profile-dropdown-item">
                    Signed in as<br>
                    <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                </div>
                <div class="profile-dropdown-divider"></div>
                <a href="profile.php" class="profile-dropdown-item">My Profile</a>
                <a href="my-pets.php" class="profile-dropdown-item">My Pets</a>
                <a href="my-orders.php" class="profile-dropdown-item">My Orders</a>
                <div class="profile-dropdown-divider"></div>
                <a href="#" class="profile-dropdown-item" id="logoutLink">Sign Out</a>
            </div>
        </div>
    <?php else: ?>
        <a href="login.php" class="loginbutton">Login</a>
    <?php endif; ?>
</div>
</header>


<script>
    document.addEventListener('DOMContentLoaded', function() {
    const profilePic = document.getElementById('profilePic');
    const profileDropdown = document.getElementById('profileDropdown');
    const logoutLink = document.getElementById('logoutLink');

    // Toggle dropdown when clicking profile picture
    profilePic?.addEventListener('click', function(e) {
        e.stopPropagation();
        profileDropdown.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!profileDropdown?.contains(e.target) && !profilePic?.contains(e.target)) {
            profileDropdown?.classList.remove('show');
        }
    });

    // Logout confirmation
    logoutLink?.addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    });
});
</script>
</body>
</html>