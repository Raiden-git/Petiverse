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

// Handle the deletion of a pet health record
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Ensure the record belongs to the logged-in user before deleting
    $stmt = $conn->prepare("SELECT * FROM health_tracker WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Delete the pet health record
        $stmt = $conn->prepare("DELETE FROM health_tracker WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $delete_id, $user_id);

        if ($stmt->execute()) {
            // Redirect back to the health tracker page after deletion
            header("Location: health_tracker.php");
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    } else {
        echo "Record not found or you do not have permission to delete it.";
    }
    $stmt->close();
}








// Handle the deletion of pet health records
if (isset($_GET['delete_id'])) {
  $delete_id = $_GET['delete_id'];
  
  // Delete the record from the database
  $stmt = $conn->prepare("DELETE FROM health_tracker WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ii", $delete_id, $user_id);

  if ($stmt->execute()) {
      // Record deleted successfully
      header("Location: health_tracker.php"); 
      exit();
  } else {
      echo "Error deleting record: " . $conn->error;
  }
  $stmt->close();
}
// Fetch the pet health records for the user
$sql = "SELECT * FROM health_tracker WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<?php


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
    $bmi = $_POST['bmi']; 

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


// Handle the deletion of pet health records
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Delete the record from the database
    $stmt = $conn->prepare("DELETE FROM health_tracker WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);

    if ($stmt->execute()) {
        // Record deleted successfully
        header("Location: health_tracker.php"); // Redirect to avoid form resubmission
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
    $stmt->close();
}


// Fetch user-specific pet health data
$stmt = $conn->prepare("SELECT * FROM health_tracker WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pets = [];

while ($row = $result->fetch_assoc()) {
    $pets[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/health_tracker.css">
    <link rel="stylesheet" href="./assets/css/scrollbar.css">
    <title>Pet Health Tracking</title>

</head>
<body>
<?php include 'Cus-NavBar/navBar.php'; ?>
<h1>Pet BMI Calculator</h1>

<div class="paragraph"> 
    <div id="catParagraph" class="pet-info">
        <img src="./assets/img/overweight-cat.jpg" alt="">
        <H1>Calculate Your Cat's Body Mass Index</H1>
        <h2>Cats are amazing companions known for their playful and independent nature.<br> Ensure your cat maintains a healthy weight by monitoring their BMI.</h2>
    </div>
    <div id="dogParagraph" class="pet-info" style="display: none;">
    <img src="./assets/img/overweight-cat.jpg" alt="">
    <H1>Calculate Your Dog's Body Mass Index</H1>
        <h2>Dogs are loyal and energetic pets. Keeping track of their BMI helps ensure <br>they stay active and healthy throughout their life.</h2>
    </div>
</div>

<div class="container">
    <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="tabs">
        <button id="catTab" onclick="switchTab('Cat')" class="active">CAT</button>
        <button id="dogTab" onclick="switchTab('Dog')">DOG</button>
    </div>

    <form id="bmiForm" method="POST" action="health_tracker.php">
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

    
        <input type="hidden" id="bmi" name="bmi" value="0">

       
        <button type="button" class="calculate-btn" onclick="calculateBMI(event)">Calculate BMI</button>

    
        <button id="addtrack" type="submit" class="submit-btn">Add Track</button>
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

</div>


<!-- Display user's pet health tracker details in a table -->
<div class="petrecord">    
    <h2>Your Pet Health Records</h2>
    <p>Keep track of your pet's health records easily with our Pet Health Tracker! Below, you'll find a comprehensive table displaying the health details of all your registered pets. From their weight and height to their BMI, this information helps you monitor their well-being effectively. Use the delete option to remove outdated or incorrect records. Scroll down to add new pets and ensure you maintain an updated health record for each furry friend. A healthy pet is a happy pet!</p>
</div>

    <table class="health-table">
        <thead>
            <tr>
                <th>Pet Name</th>
                <th>Birthday</th>
                <th>Gender</th>
                <th>Breed</th>
                <th>Weight (kg)</th>
                <th>Height (cm)</th>
                <th>BMI</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($pets) > 0): ?>
                <?php foreach ($pets as $pet): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pet['pet_name']); ?></td>
                        <td><?php echo htmlspecialchars($pet['birthday']); ?></td>
                        <td><?php echo htmlspecialchars($pet['gender']); ?></td>
                        <td><?php echo htmlspecialchars($pet['breed']); ?></td>
                        <td><?php echo htmlspecialchars($pet['weight']); ?></td>
                        <td><?php echo htmlspecialchars($pet['height']); ?></td>
                        <td><?php echo htmlspecialchars($pet['bmi']); ?></td>
                        <td>
                            <a href="health_tracker.php?delete_id=<?php echo $pet['id']; ?>" onclick="return confirm('Are you sure you want to delete this pet record?')">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php include 'footer.php'; ?>

<script>
    // Data for breeds
    const breeds = {
 Cat: [
        "Persian", "Siamese", "Maine Coon", "Bengal", "Ragdoll", "British Shorthair",
        "Abyssinian", "Scottish Fold", "Birman", "Exotic Shorthair", "Russian Blue", 
        "Oriental Shorthair", "Savannah", "Sphynx", "Devon Rex", "Norwegian Forest Cat", 
        "Egyptian Mau", "Turkish Angora", "British Longhair", "American Shorthair"
    ],
    Dog: [
        "Labrador Retriever", "German Shepherd", "Bulldog", "Poodle", "Golden Retriever", 
        "Beagle", "French Bulldog", "Rottweiler", "Yorkshire Terrier", "Dachshund", 
        "Boxer", "Siberian Husky", "Chihuahua", "Shih Tzu", "Doberman Pinscher", 
        "Cocker Spaniel", "Australian Shepherd", "Great Dane", "Border Collie", 
        "Pomeranian", "Pit Bull Terrier", "Chow Chow", "Maltese", "Bichon Frise", 
        "Akita", "Saint Bernard", "English Springer Spaniel", "Cavalier King Charles Spaniel", 
        "Schnauzer", "Collie", "Havanese", "Newfoundland", "Weimaraner"
    ]
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


   // Show the relevant paragraph
   const catParagraph = document.getElementById("catParagraph");
        const dogParagraph = document.getElementById("dogParagraph");
        if (tab === "Cat") {
            catParagraph.style.display = "block";
            dogParagraph.style.display = "none";
        } else {
            catParagraph.style.display = "none";
            dogParagraph.style.display = "block";
        }


    }



    function calculateBMI(event) {
        event.preventDefault(); 

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
        const heightM = heightCm / 100; 
        const bmi = (weight / (heightM * heightM)).toFixed(2); 

        // Categorize BMI
        let category = "";
        let bmiPosition = 0; // To adjust the position of the indicator

        if (currentTab === "Dog") {
            if (bmi < 15) {
                category = "Underweight";
                bmiPosition = (bmi - 10) / 10 * 100;
            } else if (bmi < 25) {
                category = "Normal";
                bmiPosition = ((bmi - 15) / 10) * 100 + 25; 
            } else {
                category = "Overweight";
                bmiPosition = 90; 
            }
        } else if (currentTab === "Cat") {
            if (bmi < 10) {
                category = "Underweight";
                bmiPosition = (bmi - 5) / 5 * 100; 
            } else if (bmi < 15) {
                category = "Normal";
                bmiPosition = ((bmi - 10) / 5) * 100 + 25; 
            } else {
                category = "Overweight";
                bmiPosition = 90; 
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
        bmiValue.innerText = bmi; 

        resultDiv.style.display = "block"; 

        // Store the BMI value in the hidden field for form submission
        document.getElementById('bmi').value = bmi;
    }

    function resetBMIResult() {
        // Reset form and result section
        document.getElementById("bmiForm").reset();
        document.getElementById("result").style.display = "none";
        document.getElementById("bmi").value = "0"; 
    }

    // Initialize default tab and breeds
    switchTab("Cat");
</script>

</body>
</html>





