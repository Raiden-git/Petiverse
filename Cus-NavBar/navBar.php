<?php
require 'google-config.php'; // Google Client configuration
require_once 'db.php'; // Database connection



$error_message = '';
$google_login_url = $google_client->createAuthUrl(); // Google login URL


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.cdnfonts.com/css/cheri" rel="stylesheet">                
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script> 

<style>
/* Base Header Styles */
header {
    background-color: #ECDFCC;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.logo h1 {
    margin: 0;
    font-size: 24px;
}

.logo a {
    font-family: 'Cheri', sans-serif;
    text-decoration: none;
    color: #DA8359;
}

/* Hamburger Menu Button */
.menu-toggle {
    display: none; /* Hidden by default, shown on mobile */
    background: none;
    border: none;
    cursor: pointer;
    padding: 10px;
    align-items: center;
}

.menu-toggle:hover {
    opacity: 0.8;
}

/* Desktop Navigation */
nav.desktop-nav {
    display: flex;
    align-items: center;
}

nav.desktop-nav ul {
    list-style: none;
    display: flex;
    gap: 20px;
    margin: 0;
    padding: 0;
}

nav.desktop-nav a {
    text-decoration: none;
    color: #333;
    padding: 5px 9px;
    font-size: 15px;
    transition: color 0.3s ease, transform 0.3s ease;
    position: relative;
}

nav.desktop-nav a::before {
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

nav.desktop-nav a:hover::before {
    visibility: visible;
    width: 100%;
}

nav.desktop-nav a:hover {
    color: #DA8359;
    transform: translateY(-3px);
}

/* Right Side Elements */
.header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Cart Icon */
.custom-cart-icon {
    position: relative;
    color: #333;
    text-decoration: none;
    display: flex;
    align-items: center;
}

.custom-cart-badge {
    position: absolute;
    top: -5px;
    right: -10px;
    background-color: #ff5722;
    color: white;
    font-size: 0.8rem;
    font-weight: bold;
    border-radius: 50%;
    padding: 2px 6px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Profile Styles */
.profile-container {
    position: relative;
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
    min-width: 200px;
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

/* Mobile Navigation Drawer */
.mobile-nav {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100vh;
    background-color: #FCFAEE;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out;
    z-index: 1001;
    overflow-y: auto;
}

.mobile-nav.open {
    transform: translateX(280px);
}

.mobile-nav ul {
    list-style: none;
    padding: 20px 0;
    margin: 0;
}

.mobile-nav li {
    padding: 0;
    margin: 0;
}

.mobile-nav a {
    text-decoration: none;
    color: #333;
    padding: 15px 25px;
    display: block;
    transition: background-color 0.3s ease;
    font-size: 16px;
}

.mobile-nav a:hover {
    background-color: #da845970;
    color: #DA8359;
}

/* Overlay */
.nav-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.nav-overlay.show {
    display: block;
}

/* Login Button Styles */
.loginbutton {
    text-decoration: none;
    font-size: 16px;
    color: black;
    background-color: #da845970;
    border: 2px solid #DA8359;
    padding: 8px 15px;
    border-radius: 25px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.loginbutton:hover {
    background-color: #DA8359;
    color: #FCFAEE;
}

.mobile-nav .drawer-login {
    padding: 20px 25px;
    border-top: 1px solid #e0e0e0;
}

/* Responsive Breakpoints */
@media (max-width: 1024px) {
    nav.desktop-nav ul {
        gap: 15px;
    }
    
    nav.desktop-nav a {
        font-size: 14px;
    }
}

@media (max-width: 768px) {
    /* Show mobile elements */
    .menu-toggle {
        display: flex;
    }
    
    .mobile-nav {
        display: block;
    }
    
    /* Hide desktop navigation */
    nav.desktop-nav {
        display: none;
    }
    
    /* Adjust header for mobile */
    header {
        padding: 10px 15px;
    }
    
    .logo h1 {
        font-size: 22px;
    }
}

@media (max-width: 480px) {
    .logo h1 {
        font-size: 20px;
    }
    
    .profile-pic {
        width: 35px;
        height: 35px;
    }
    
    .custom-cart-badge {
        font-size: 0.7rem;
        padding: 1px 5px;
    }
    
    .mobile-nav {
        width: 260px;
        left: -260px;
    }
    
    .mobile-nav.open {
        transform: translateX(260px);
    }
}












</style>


</head>
<body>
<header>
    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" aria-label="Toggle Menu">
        <box-icon name='menu' size="md"></box-icon>
    </button>
    
    <div class="logo">
        <h1><a href="index.php">Petiverse</a></h1>
    </div>
    
    <!-- Desktop Navigation -->
    <nav class="desktop-nav">
        <ul>
            <li><a href="shop.php">Shop</a></li>
            <li><a href="vets_map.php">Vet Services</a></li>
            <li><a href="#">Day Care</a></li>
            <li><a href="community.php">Community</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li><a href="./pets.php">Lost & Found Pets</a></li>
            <li><a href="#">Pet Selling</a></li>
            <li><a href="about.php">About Us</a></li>
        </ul>
    </nav>
    
    <div class="header-right">
    <a href="cart.php" class="custom-cart-icon">
        <box-icon name="cart" type="solid" size="lg"></box-icon>
        <?php 
        $total_cart_items = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; 
        if ($total_cart_items > 0): ?>
            <span class="custom-cart-badge"><?= $total_cart_items ?></span>
        <?php endif; ?>
    </a>
        
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

<!-- Mobile Navigation -->
<nav class="mobile-nav" id="mobileNav">
    <ul>
        <li><a href="shop.php">Shop</a></li>
        <li><a href="vets_map.php">Vet Services</a></li>
        <li><a href="#">Day Care</a></li>
        <li><a href="community.php">Community</a></li>
        <li><a href="blog.php">Blog</a></li>
        <li><a href="./pets.php">Lost & Found Pets</a></li>
        <li><a href="#">Pet Selling</a></li>
        <li><a href="about.php">About Us</a></li>
    </ul>
    <?php if(!isset($_SESSION['username'])): ?>
        <div class="drawer-login">
            <a href="login.php" class="loginbutton">Login</a>
        </div>
    <?php endif; ?>
</nav>

<!-- Overlay -->
<div class="nav-overlay" id="navOverlay"></div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const mobileNav = document.getElementById('mobileNav');
    const navOverlay = document.getElementById('navOverlay');
    const profilePic = document.getElementById('profilePic');
    const profileDropdown = document.getElementById('profileDropdown');
    const logoutLink = document.getElementById('logoutLink');

    // Mobile menu toggle
    menuToggle?.addEventListener('click', () => {
        mobileNav.classList.toggle('open');
        navOverlay.classList.toggle('show');
        document.body.style.overflow = mobileNav.classList.contains('open') ? 'hidden' : '';
    });

    // Close mobile nav when clicking overlay
    navOverlay?.addEventListener('click', () => {
        mobileNav.classList.remove('open');
        navOverlay.classList.remove('show');
        document.body.style.overflow = '';
    });

    // Profile dropdown
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