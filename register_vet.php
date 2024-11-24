<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $qualification = mysqli_real_escape_string($conn, $_POST['qualification']);
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);
    $clinic_name = mysqli_real_escape_string($conn, $_POST['clinic_name']);
    $clinic_address = mysqli_real_escape_string($conn, $_POST['clinic_address']);
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
    $consultation_fee = mysqli_real_escape_string($conn, $_POST['consultation_fee']);
    $contact_details = mysqli_real_escape_string($conn, $_POST['contact_details']);
    $services = mysqli_real_escape_string($conn, $_POST['services']);
    $license_number = mysqli_real_escape_string($conn, $_POST['license_number']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Handle profile picture
        $profile_picture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
            $profile_picture = file_get_contents($_FILES['profile_picture']['tmp_name']);
        } else {
            throw new Exception("Profile picture is required");
        }

        // Handle verification document
        $document = null;
        $document_type = null;
        if (isset($_FILES['vet_document']) && $_FILES['vet_document']['error'] === 0) {
            $document = file_get_contents($_FILES['vet_document']['tmp_name']);
            $document_type = pathinfo($_FILES['vet_document']['name'], PATHINFO_EXTENSION);
        } else {
            throw new Exception("Verification document is required");
        }

        // Begin transaction
        mysqli_begin_transaction($conn);

        // Insert vet data (use 'b' type for binary data)
        $sql_vet = "INSERT INTO vets (
            name, qualification, specialization, experience, 
            clinic_name, clinic_address, latitude, longitude, 
            consultation_fee, contact_details, services, 
            profile_picture, license_number, email, password, approval_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = mysqli_prepare($conn, $sql_vet);
        mysqli_stmt_bind_param($stmt, "ssssssddsssssss", 
            $name, $qualification, $specialization, $experience, 
            $clinic_name, $clinic_address, $latitude, $longitude, 
            $consultation_fee, $contact_details, $services, 
            $profile_picture, $license_number, $email, $hashed_password
        );
        
        mysqli_stmt_execute($stmt);
        $vet_id = mysqli_insert_id($conn);

        // Insert document
        $sql_docs = "INSERT INTO vet_documents (vet_id, document, document_type) VALUES (?, ?, ?)";
        $stmt_docs = mysqli_prepare($conn, $sql_docs);
        mysqli_stmt_bind_param($stmt_docs, "iss", $vet_id, $document, $document_type);
        mysqli_stmt_execute($stmt_docs);

        // Commit transaction
        mysqli_commit($conn);
        
        // Redirect to waiting area or provide a success response
        header("Location: vet_waiting_area.php?success=1");
        exit;
    } catch (Exception $e) {
        // Rollback transaction in case of error
        mysqli_rollback($conn);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

    // Close the database connection
    mysqli_close($conn);
}
