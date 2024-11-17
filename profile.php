<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$message = '';

// Handle cropped image upload
if (isset($_POST['save_cropped'])) {
    $croppedImage = $_POST['cropped_image'];
    $croppedImage = str_replace('data:image/png;base64,', '', $croppedImage);
    $croppedImage = str_replace(' ', '+', $croppedImage);
    $imageData = base64_decode($croppedImage);
    
    $sql = "UPDATE users SET profile_pic = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("bi", $null, $userId);
    $stmt->send_long_data(0, $imageData);
    
    if ($stmt->execute()) {
        $message = "Profile photo updated successfully!";
    } else {
        $message = "Error updating profile photo.";
    }
}

// Handle profile photo deletion
if (isset($_POST['delete_photo'])) {
    $sql = "UPDATE users SET profile_pic = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $message = "Profile photo deleted successfully!";
    } else {
        $message = "Error deleting profile photo.";
    }
}

// Handle profile information update
if (isset($_POST['update_profile'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $contactNumber = $_POST['contact_number'];
    $address = $_POST['address'];
    
    $sql = "UPDATE users SET 
            first_name = ?, 
            last_name = ?, 
            email = ?,
            full_name = CONCAT(?, ' ', ?),
            contact_number = ?,
            address = ?
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", 
        $firstName, 
        $lastName, 
        $email, 
        $firstName, 
        $lastName, 
        $contactNumber, 
        $address, 
        $userId
    );
    
    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile.";
    }
}

// Fetch current user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <style>
        .image-container {
            max-width: 300px;
            max-height: 300px;
            margin: 0 auto;
        }
        #preview {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto;
        }
        .cropper-container {
            margin: 20px auto;
            max-width: 500px;
            height: 300px;
        }
    </style>
</head>
<body>
<?php include 'Cus-NavBar/navBar.php'; ?>
    <div class="container mt-5">
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Photo Section -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Profile Photo</h4>
                    </div>
                    <div class="card-body text-center">
                        <div id="preview">
                            <?php if ($user['profile_pic']): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($user['profile_pic']); ?>" 
                                     class="img-fluid">
                            <?php else: ?>
                                <img src="default-avatar.png" class="img-fluid">
                            <?php endif; ?>
                        </div>

                        <!-- Photo Upload Form -->
                        <form method="POST" enctype="multipart/form-data" id="uploadForm">
                            <div class="mb-3">
                                <input type="file" class="form-control" id="imageInput" accept="image/*">
                            </div>
                            <button type="button" class="btn btn-primary" id="cropButton" style="display: none;">
                                Crop & Save
                            </button>
                            <?php if ($user['profile_pic']): ?>
                                <button type="submit" name="delete_photo" class="btn btn-danger">Delete Photo</button>
                            <?php endif; ?>
                        </form>

                        <!-- Hidden form for cropped image -->
                        <form method="POST" id="cropForm" style="display: none;">
                            <input type="hidden" name="cropped_image" id="croppedImage">
                            <input type="hidden" name="save_cropped" value="1">
                        </form>
                    </div>
                </div>
            </div>

            <!-- Cropper Modal -->
            <div class="modal fade" id="cropperModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Crop Image</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="cropper-container">
                                <img id="cropperImage" src="" style="max-width: 100%;">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="saveCrop">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information Section -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Profile Information</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" 
                                           value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" 
                                           value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" name="contact_number" 
                                       value="<?php echo htmlspecialchars($user['contact_number']); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script>
        let cropper = null;
        const modal = new bootstrap.Modal(document.getElementById('cropperModal'));
        
        document.getElementById('imageInput').addEventListener('change', function(e) {
            if (e.target.files.length) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const cropperImage = document.getElementById('cropperImage');
                    cropperImage.src = e.target.result;
                    
                    modal.show();
                    
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    cropper = new Cropper(cropperImage, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: false,
                        cropBoxResizable: false,
                        toggleDragModeOnDblclick: false
                    });
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        document.getElementById('saveCrop').addEventListener('click', function() {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({
                    width: 400,
                    height: 400
                });
                
                const croppedImage = canvas.toDataURL('image/png');
                document.getElementById('croppedImage').value = croppedImage;
                document.getElementById('cropForm').submit();
                
                modal.hide();
            }
        });
    </script>
</body>
</html>