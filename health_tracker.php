<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'petiverse');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = '';

// Handle form submission to save pet details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pet_name = $conn->real_escape_string($_POST['petName']);
    $birthday = $conn->real_escape_string($_POST['birthday']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $breed = $conn->real_escape_string($_POST['breed']);
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $bmi = $_POST['bmi']; // Get BMI value from the form submission

    if (empty($pet_name) || empty($birthday) || empty($gender) || empty($breed) || empty($weight) || empty($height)) {
        $error_message = "All fields are required.";
    } else {
        // Insert pet details and BMI into the health_tracker table
        $stmt = $conn->prepare("INSERT INTO health_tracker (user_id, pet_name, birthday, gender, breed, weight, height, bmi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("issssddi", $user_id, $pet_name, $birthday, $gender, $breed, $weight, $height, $bmi);

        if (!$stmt->execute()) {
            $error_message = "Error adding pet details.";
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/health_tracker.css">
    <!-- Add Font Awesome for the recycle icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Pet Health Tracking</title>
</head>
<body>

<div class="container">
    <h1>Pet BMI Calculator</h1>
    
    <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="tabs">
        <button id="catTab" onclick="switchTab('Cat')" class="active">CAT</button>
        <button id="dogTab" onclick="switchTab('Dog')">DOG</button>
    </div>

    <form id="bmiForm" method="POST" action="save_track_details.php">
        <label for="petName">Your pet's name*</label>
        <input type="text" id="petName" name="petName" placeholder="Enter your pet's name" required>

        <label for="birthday">Birthday*</label>
        <input type="date" id="birthday" name="birthday" required>

        <label for="gender">Gender*</label>
        <select id="gender" name="gender" required>
            <option value="">-- Select Gender --</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>

        <label for="breed">Breed*</label>
        <select id="breed" name="breed" required>
            <option value="">-- Select Breed --</option>
        </select>

        <label for="weight">Weight (kg)*</label>
        <input type="number" id="weight" name="weight" placeholder="Enter weight in kg" min="0" step="0.1" required>

        <label for="height">Height to Shoulder (cm)*</label>
        <input type="number" id="height" name="height" placeholder="Enter height in cm" min="0" step="0.1" required>

        <!-- Hidden field to store BMI value -->
        <input type="hidden" id="bmi" name="bmi" value="0">

        <!-- Button to calculate BMI, it doesn't submit the form yet -->
        <button type="button" class="calculate-btn" onclick="calculateBMI(event)">Calculate BMI</button>

        <!-- Button to submit and save details (and BMI) -->
        <button type="submit" class="submit-btn">Add Crack Table</button>
    </form>

    <!-- Display BMI after calculation -->
    <div id="result" class="result" style="display: none;">
        <p id="bmiText"></p>
        <div class="bmi-bar">
            <span>Underweight</span>
            <span class="normal">Normal</span>
            <span>Overweight</span>
            <div id="bmiIndicator" class="indicator"></div>
        </div>
        <p>BMI Value: <span id="bmiValue"></span></p>
    </div>

    <!-- Recycle Icon to reset and show the result again -->
    <button id="resetBtn" onclick="resetBMIResult()" style="margin-top: 20px; background: none; border: none;">
        <i class="fas fa-sync-alt" style="font-size: 30px; color: #333;"></i>
    </button>

</div>

<script>
    // Data for breeds
    const breeds = {
        Cat: ["Persian", "Siamese", "Maine Coon", "Bengal"],
        Dog: ["Labrador Retriever", "German Shepherd", "Bulldog", "Poodle"]
    };

    let currentTab = "Cat"; // Default tab

    function switchTab(tab) {
        currentTab = tab;
        document.getElementById("catTab").classList.remove("active");
        document.getElementById("dogTab").classList.remove("active");
        document.getElementById(tab.toLowerCase() + "Tab").classList.add("active");

        // Update breed options
        const breedSelect = document.getElementById("breed");
        breedSelect.innerHTML = '<option value="">-- Select Breed --</option>';
        breeds[tab].forEach(breed => {
            const option = document.createElement("option");
            option.value = breed;
            option.textContent = breed;
            breedSelect.appendChild(option);
        });
    }

    function calculateBMI(event) {
        event.preventDefault(); // Prevent form from submitting immediately

        const petName = document.getElementById("petName").value.trim();
        const weight = parseFloat(document.getElementById("weight").value);
        const heightCm = parseFloat(document.getElementById("height").value);

        // Validate input
        if (!petName) {
            alert("Please enter your pet's name!");
            return;
        }

        if (isNaN(weight) || isNaN(heightCm) || weight <= 0 || heightCm <= 0) {
            alert("Please enter valid weight and height values!");
            return;
        }

        // BMI calculation
        const heightM = heightCm / 100; // Convert height to meters
        const bmi = (weight / (heightM * heightM)).toFixed(2); // BMI formula

        // Categorize BMI
        let category = "";
        let bmiPosition = 0; // To adjust the position of the indicator

        if (currentTab === "Dog") {
            if (bmi < 15) {
                category = "Underweight";
                bmiPosition = (bmi - 10) / 10 * 100; // Adjust indicator for dogs
            } else if (bmi < 25) {
                category = "Normal";
                bmiPosition = ((bmi - 15) / 10) * 100 + 25; // Adjust indicator for dogs
            } else {
                category = "Overweight";
                bmiPosition = 90; // Set the indicator position for overweight dogs
            }
        } else if (currentTab === "Cat") {
            if (bmi < 10) {
                category = "Underweight";
                bmiPosition = (bmi - 5) / 5 * 100; // Adjust indicator for cats
            } else if (bmi < 15) {
                category = "Normal";
                bmiPosition = ((bmi - 10) / 5) * 100 + 25; // Adjust indicator for cats
            } else {
                category = "Overweight";
                bmiPosition = 90; // Set the indicator position for overweight cats
            }
        }

        // Display result
        const resultDiv = document.getElementById("result");
        const bmiText = document.getElementById("bmiText");
        const bmiIndicator = document.getElementById("bmiIndicator");
        const bmiValue = document.getElementById("bmiValue");

        // Show BMI result text
        bmiText.innerText = `${petName}'s BMI is ${bmi} (${category})`;

        // Set indicator position
        bmiIndicator.style.left = `${bmiPosition}%`;
        bmiValue.innerText = bmi; // Display BMI value inside the result

        resultDiv.style.display = "block"; // Ensure the result div stays visible

        // Store the BMI value in the hidden field for form submission
        document.getElementById('bmi').value = bmi;
    }

    function resetBMIResult() {
        // Reset form and result section
        document.getElementById("bmiForm").reset();
        document.getElementById("result").style.display = "none";
        document.getElementById("bmi").value = "0"; // Reset hidden BMI field
    }

    // Initialize default tab and breeds
    switchTab("Cat");
</script>

</body>
</html>
