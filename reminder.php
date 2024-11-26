<?php
session_start();
include('db.php'); // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from the session
$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
$sql = "SELECT is_premium FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if form is submitted for editing an existing reminder
if (isset($_POST['edit_reminder'])) {
    $reminder_id = $_POST['reminder_id'];
    $pet_name = $_POST['pet_name'];
    $email = $_POST['email'];
    $vaccination_date = $_POST['vaccination_date'];
    $note = $_POST['note'];

    // Update the reminder in the database
    $sql = "UPDATE vaccination_reminders SET pet_name = ?, email = ?, vaccination_date = ?, note = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $pet_name, $email, $vaccination_date, $note, $reminder_id, $user_id);
    $stmt->execute();
    echo "Reminder updated successfully!";
}

// Check if a delete action was requested
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Delete the reminder
    $sql = "DELETE FROM vaccination_reminders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    echo "Reminder deleted successfully!";
}

// Fetch the logged-in user's reminders
$sql = "SELECT * FROM vaccination_reminders WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($user['is_premium'] == 1) { // Check if user is a premium member
    // Display form for adding or editing reminders
    if (isset($_GET['edit_id'])) {
        // If editing, fetch reminder data
        $edit_id = $_GET['edit_id'];
        $sql = "SELECT * FROM vaccination_reminders WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $edit_id, $user_id);
        $stmt->execute();
        $reminder_to_edit = $stmt->get_result()->fetch_assoc();
    }
    ?>
    <h2><?php echo isset($reminder_to_edit) ? 'Edit' : 'Add'; ?> Vaccination Reminder</h2>
    
    <form action="reminder.php" method="POST">
        <input type="hidden" name="reminder_id" value="<?php echo isset($reminder_to_edit) ? $reminder_to_edit['id'] : ''; ?>">
        
        <label for="pet_name">Pet Name:</label>
        <input type="text" id="pet_name" name="pet_name" value="<?php echo isset($reminder_to_edit) ? htmlspecialchars($reminder_to_edit['pet_name']) : ''; ?>" required>

        <label for="email">Your Email:</label>
        <input type="email" id="email" name="email" value="<?php echo isset($reminder_to_edit) ? htmlspecialchars($reminder_to_edit['email']) : ''; ?>" required>

        <label for="vaccination_date">Vaccination Date:</label>
        <input type="date" id="vaccination_date" name="vaccination_date" value="<?php echo isset($reminder_to_edit) ? $reminder_to_edit['vaccination_date'] : ''; ?>" required>

        <label for="note">Additional Notes:</label>
        <textarea id="note" name="note"><?php echo isset($reminder_to_edit) ? htmlspecialchars($reminder_to_edit['note']) : ''; ?></textarea>

        <button type="submit" name="edit_reminder">Save Reminder</button>
    </form>

    <h2>Your Vaccination Reminders</h2>

    <table border="1">
        <thead>
            <tr>
                <th>Pet Name</th>
                <th>Email</th>
                <th>Vaccination Date</th>
                <th>Note</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($reminder = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($reminder['pet_name']); ?></td>
                    <td><?php echo htmlspecialchars($reminder['email']); ?></td>
                    <td><?php echo htmlspecialchars($reminder['vaccination_date']); ?></td>
                    <td><?php echo htmlspecialchars($reminder['note']); ?></td>
                    <td>
                        <a href="reminder.php?edit_id=<?php echo $reminder['id']; ?>">Edit</a>
                        <a href="reminder.php?delete_id=<?php echo $reminder['id']; ?>" onclick="return confirm('Are you sure you want to delete this reminder?');">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php
} else {
    // Message for non-premium users
    echo "<p>This feature is available for premium users only. Please upgrade your plan to use this feature.</p>";
}
?>
