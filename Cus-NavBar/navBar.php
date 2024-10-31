<?php
//require 'google-config.php'; // Google Client configuration



$error_message = '';
//$google_login_url = $google_client->createAuthUrl(); // Google login URL

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
}

.login a, .login button {
    text-decoration: none;
    font-size: 16px;
    color: black;
    margin-left: 20px;
    background-color: #da845970;
    border: 2px solid #DA8359;
    padding: 8px 15px;
    border-radius: 25px;
    transition: background-color 0.3s ease, color 0.3s ease;
    cursor: pointer;
}

.login a:hover, .login button:hover {
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

#questionModal {
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.login-container {
    font-family: 'Poppins', sans-serif;
    background-color: #ffffff;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-width: 400px;
    animation: slideIn 0.8s ease;
    position: relative;
}

.close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: transparent;
    border: none;
    font-size: 30px;
    cursor: pointer;
}

#LoginPet {
    text-align: center;
    margin-bottom: 30px;
    font-weight: 600;
    color: #FC5C7D;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

input[type="email"], input[type="password"] {
    width: calc(100% - 24px); /* Adjust width to match Google button */
    padding: 12px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    transition: border-color 0.3s ease;
}

input[type="email"]:focus, input[type="password"]:focus {
    border-color: #FC5C7D;
}

input[type="submit"] {
    width: 100%;
    padding: 12px;
    background-color: #6A82FB;
    border: none;
    border-radius: 5px;
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

input[type="submit"]:hover {
    background-color: #FC5C7D;
}

.google-login-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    background-color: #4285f4;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
    width: 100%; /* Ensure full width */
    box-sizing: border-box; /* Include padding and border in width */
}

.google-login-btn:hover {
    background-color: #357ae8;
}

.google-login-btn svg {
    margin-right: 10px;
}

#lgn {
    color: #6A82FB;
    text-decoration: none;
}

#lgn:hover {
    text-decoration: none;
}

#lgnp {
    text-align: center;
    margin-top: 20px;
}

/* Forgot Password Styles */
.forgot-password {
    display: block;
    margin-top: -10px;
    margin-bottom: 20px;
    font-size: 14px;
    color: #6A82FB;
    text-decoration: none;
    text-align: right;
}

.forgot-password:hover {
    text-weight: bold;
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
            <li><a href="#" class="restricted-page" data-page="daycare.php">Day Care</a></li>
            <li><a href="community.php">Community</a></li>
            <li><a href="../Blog.php">Blog</a></li>
            <li><a href="./pets.php">Lost & Found Pets</a></li>
            <li><a href="#" class="restricted-page" data-page="petselling.php">Pet Selling</a></li>
            <li><a href="about.php">About Us</a></li>
        </ul>
    </nav>
    <div class="login">
    <?php if(isset($_SESSION['username'])): ?>
        <a href="profile.php">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
        <a href="#" id="logoutLink">Logout</a>
    <?php else: ?>
        <button onclick="openQuestionModal()">Login</button>
    <?php endif; ?>
    </div>
</header>

<div id="questionModal">
    <div class="login-container">
        <button class="close-btn" onclick="closeQuestionModal()">&times;</button>
        <h2 id="LoginPet">Login to Petiverse</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <label>Email:</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>Password:</label>
            <input type="password" name="password" placeholder="Enter your password" required>

            <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>

            <input type="submit" value="Login">

            <p id="lgnp">OR</p>
            <a href="<?php echo $google_login_url; ?>" class="google-login-btn">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="google" viewBox="-380.2 274.7 65.7 65.8" width="25" height="25">
                    <circle cx="-347.3" cy="307.6" r="32.9" style="fill:#e0e0e0"></circle>
                    <circle cx="-347.3" cy="307.1" r="32.4" style="fill:#fff"></circle>
                    <g>
                        <defs>
                            <path id="SVGID_1_" d="M-326.3 303.3h-20.5v8.5h11.8c-1.1 5.4-5.7 8.5-11.8 8.5-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4c-3.9-3.4-8.9-5.5-14.5-5.5-12.2 0-22 9.8-22 22s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path>
                        </defs>
                        <clipPath id="SVGID_2_">
                            <use xlink:href="#SVGID_1_" overflow="visible"></use>
                        </clipPath>
                        <path d="M-370.8 320.3v-26l17 13z" style="clip-path:url(#SVGID_2_);fill:#fbbc05"></path>
                        <defs>
                            <path id="SVGID_3_" d="M-326.3 303.3h-20.5v8.5h11.8c-1.1 5.4-5.7 8.5-11.8 8.5-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4c-3.9-3.4-8.9-5.5-14.5-5.5-12.2 0-22 9.8-22 22s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path>
                        </defs>
                        <clipPath id="SVGID_4_">
                            <use xlink:href="#SVGID_3_" overflow="visible"></use>
                        </clipPath>
                        <path d="M-370.8 294.3l17 13 7-6.1 24-3.9v-14h-48z" style="clip-path:url(#SVGID_4_);fill:#ea4335"></path>
                        <g>
                            <defs>
                                <path id="SVGID_5_" d="M-326.3 303.3h-20.5v8.5h11.8c-1.1 5.4-5.7 8.5-11.8 8.5-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4c-3.9-3.4-8.9-5.5-14.5-5.5-12.2 0-22 9.8-22 22s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path>
                            </defs>
                            <clipPath id="SVGID_6_">
                                <use xlink:href="#SVGID_5_" overflow="visible"></use>
                            </clipPath>
                            <path d="M-370.8 320.3l30-23 7.9 1 10.1-15v48h-48z" style="clip-path:url(#SVGID_6_);fill:#34a853"></path>
                        </g>
                        <g>
                            <defs>
                                <path id="SVGID_7_" d="M-326.3 303.3h-20.5v8.5h11.8c-1.1 5.4-5.7 8.5-11.8 8.5-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4c-3.9-3.4-8.9-5.5-14.5-5.5-12.2 0-22 9.8-22 22s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path>
                            </defs>
                            <clipPath id="SVGID_8_">
                                <use xlink:href="#SVGID_7_" overflow="visible"></use>
                            </clipPath>
                            <path d="M-322.8 331.3l-31-24-4-3 35-10z" style="clip-path:url(#SVGID_8_);fill:#4285f4"></path>
                        </g>
                    </g>
                </svg>Continue with Google
                </a>
        </form>
        <p id="lgnp">Not registered? <a id="lgn" href="signup.php">Sign up here</a></p>
    </div>
</div>

<script>
    document.getElementById("logoutLink").addEventListener("click", function(event) {
        event.preventDefault(); // Prevents immediate redirect
        var confirmation = confirm("Are you sure you want to logout?");
        if (confirmation) {
            window.location.href = "logout.php"; // Redirects to logout page if confirmed
        }
    });

    // Open modal function
    function openQuestionModal() {
        document.getElementById('questionModal').style.display = 'flex';
    }

    // Close modal function
    function closeQuestionModal() {
        document.getElementById('questionModal').style.display = 'none';
    }

    // Check for restricted pages and redirect to login if not logged in
    const restrictedPages = document.querySelectorAll('.restricted-page');
    restrictedPages.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const page = this.getAttribute('data-page');
            
            // Check if user is logged in
            <?php if(!isset($_SESSION['username'])): ?>
                // If not logged in, show login modal
                openQuestionModal();
            <?php else: ?>
                // If logged in, redirect to the restricted page
                window.location.href = page;
            <?php endif; ?>
        });
    });
</script>

</body>
</html>