<?php
require 'google-config.php'; // Google Client configuration



$error_message = '';
$google_login_url = $google_client->createAuthUrl(); // Google login URL

// Restricted pages requiring login
$restricted_pages = ['vets_map.php', 'daycare.php', 'lost_found.php', 'petselling.php'];

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
    color: black; /* Initial text color */
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
    background-color: #DA8359; /* Orange underline */
    visibility: hidden;
    transition: all 0.3s ease-in-out;
}

nav a:hover::before {
    visibility: visible;
    width: 100%; /* Expands underline to full width */
}

nav a:hover {
    color: #DA8359; 
    transform: translateY(-3px);
}


.login a {
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

.login a:hover {
    background-color: #DA8359;
    color: #FCFAEE;
}


#logoutLink{
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
            <!-- Pages requiring login -->
            <li><a href="#">Day Care</a></li>
            <li><a href="community.php">Community</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li><a href="./pets.php">Lost & Found Pets</a></li>
            <li><a href="#" >Pet Selling</a></li>
            <li><a href="about.php">About Us</a></li>
        </ul>
    </nav>
    <div class="login">
    <?php if(isset($_SESSION['username'])): ?>
        <a href="profile.php">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
        <a href="#" id="logoutLink">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
    <?php endif; ?>
</div>
</header>


<script>
    document.getElementById("logoutLink").addEventListener("click", function(event) {
        event.preventDefault(); // Prevents immediate redirect
        var confirmation = confirm("Are you sure you want to logout?");
        if (confirmation) {
            window.location.href = "logout.php"; // Redirects to logout page if confirmed
        }
    });
</script>
</body>
</html>