<!-- login_modal.php -->
<div id="loginModal">
    <div class="login-container">
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
    // Function to open the login modal
    function openLoginModal() {
        document.getElementById('loginModal').style.display = 'flex';
    }

</script>

<style>
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

#loginModal {
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
</style>
