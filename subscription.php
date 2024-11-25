
<?php
    session_start();
    include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetCare Premium Membership</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
    --primary: #DA8359;
    --primary-dark: #9b6143;
    --secondary: #818cf8;
    --success: #22c55e;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background: linear-gradient(135deg, #fffdf8 0%, #f6f3e8 100%);
    line-height: 1.5;
    color: var(--gray-800);
    min-height: 100vh;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 1rem;
}

.header {
    text-align: center;
    margin-bottom: 2rem;
}

.header h1 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    background: linear-gradient(to right, var(--primary), var(--secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
}

.header p {
    color: var(--gray-600);
    font-size: 1rem;
    max-width: 500px;
    margin: 0 auto;
}

.pricing-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin: 1.5rem 0;
}

.pricing-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    flex-direction: column;
}

.pricing-card.featured {
    border: 2px solid var(--primary);
}

.featured-badge {
    position: absolute;
    top: -10px;
    right: 1.5rem;
    background: var(--primary);
    color: white;
    padding: 0.2rem 0.75rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 500;
}

.save-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: #fbf3f3;
    color: #ef4444;
    padding: 0.2rem 0.5rem;
    border-radius: 15px;
    font-size: 0.7rem;
    font-weight: 500;
}

.price-header {
    text-align: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray-200);
}

.price-header h2 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
}

.price {
    font-size: 2.25rem;
    font-weight: 700;
    color: var(--gray-800);
    margin: 0.5rem 0;
}

.price span {
    font-size: 0.875rem;
    color: var(--gray-600);
    font-weight: normal;
}

.features-list {
    list-style: none;
    margin: 1.5rem 0;
    flex-grow: 1;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    color: var(--gray-700);
    font-size: 0.9rem;
}

.feature-icon {
    width: 20px;
    height: 20px;
    background: #f0fdf4;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--success);
    flex-shrink: 0;
}

.feature-icon i {
    font-size: 0.75rem;
}

.subscribe-btn {
    display: inline-block;
    width: 100%;
    padding: 0.75rem;
    background: var(--primary);
    color: white;
    text-decoration: none;
    text-align: center;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
}

.subscribe-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
}

.guarantee {
    text-align: center;
    margin-top: 2rem;
    color: var(--gray-600);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

.guarantee i {
    color: var(--primary);
}

@media (max-width: 768px) {
    .pricing-cards {
        grid-template-columns: 1fr;
    }
    
    .container {
        padding: 0.75rem;
    }
    
    .header h1 {
        font-size: 1.75rem;
    }
    
    .price {
        font-size: 2rem;
    }
}
    </style>
</head>
<body>
    <?php include 'Cus-NavBar/navBar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>Upgrade to Premium</h1>
            <p>Get exclusive access to premium features and enhance your pet care experience</p>
        </div>



        <div class="pricing-cards">
            <!-- Monthly Plan -->
            <div class="pricing-card">
                <div class="price-header">
                    <h2>Monthly Premium</h2>
                    <div class="price">
                        $9.99 <span>/month</span>
                    </div>
                    <p>Perfect for pet owners who want full access</p>
                </div>

                <ul class="features-list">
                    <li class="feature-item">
                        <span class="feature-icon"><i class="fas fa-calendar-check"></i></span>
                        <span>Veterinary Appointment Booking</span>
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon"><i class="fas fa-comments"></i></span>
                        <span>24/7 Chat with Veterinarians</span>
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon"><i class="fas fa-bell"></i></span>
                        <span>Email Reminder Services</span>
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon"><i class="fas fa-paw"></i></span>
                        <span>Add Unlimited Pets</span>
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon"><i class="fas fa-clock"></i></span>
                        <span>Priority Support Response</span>
                    </li>
                </ul>

                <a href="payment_process.php?plan=monthly" class="subscribe-btn">Subscribe Monthly</a>
            </div>

            <!-- Annual Plan -->
            <div class="pricing-card featured">
                <div class="featured-badge">BEST VALUE</div>
                <div class="save-badge">Save 16%</div>
                
                <div class="price-header">
                    <h2>Annual Premium</h2>
                    <div class="price">
                        $99.99 <span>/year</span>
                    </div>
                    <p>Best value for dedicated pet owners</p>
                </div>

                <ul class="features-list">
                    <li class="feature-item">
                        <span class="feature-icon"><i class="fas fa-calendar-check"></i></span>
                        <span>Veterinary Appointment Booking</span>
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon"><i class="fas fa-comments"></i></span>
                        <span>24/7 Chat with Veterinarians</span>
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon"><i class="fas fa-bell"></i></span>
                        <span>Email Reminder Services</span>
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon"><i class="fas fa-paw"></i></span>
                        <span>Add Unlimited Pets</span>
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon"><i class="fas fa-clock"></i></span>
                        <span>Priority Support Response</span>
                    </li>
                </ul>

                <a href="payment_process.php?plan=annual" class="subscribe-btn">Subscribe Annually</a>
            </div>
        </div>

        
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>