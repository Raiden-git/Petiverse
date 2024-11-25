<?php
// admin_vet_review.php

session_start();
include('../db.php');

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vet_id = $_POST['vet_id'];
    $action = $_POST['action'];
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    $stmt = $conn->prepare("UPDATE vets SET approval_status = ?, is_approved = ? WHERE id = ?");
    $is_approved = ($status === 'approved') ? 1 : 0;
    $stmt->bind_param("sii", $status, $is_approved, $vet_id);
    
    if ($stmt->execute()) {
        // Send email notification to vet
        $vet_email = getVetEmail($vet_id, $conn);
        sendStatusNotification($vet_email, $status);
        $message = "Vet registration has been " . $status;
    } else {
        $error = "Error updating status";
    }
}

// Get pending vet registrations
$query = "SELECT v.*, GROUP_CONCAT(vd.document_type) as document_types 
          FROM vets v 
          LEFT JOIN vet_documents vd ON v.id = vd.vet_id 
          WHERE v.approval_status = 'pending'
          GROUP BY v.id";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

function getVetEmail($vet_id, $conn) {
    $stmt = $conn->prepare("SELECT email FROM vets WHERE id = ?");
    $stmt->bind_param("i", $vet_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['email'];
}

function sendStatusNotification($email, $status) {
    $subject = "Vet Registration Status Update";
    $message = "Your registration has been " . $status;
    mail($email, $subject, $message);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Vet Registration Review</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Replace YOUR_API_KEY with your actual Google Maps API key -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHdLOieN0OIcTyyY6CmJv6gPNx-OX3MwA"></script>
    <style>
        .map-container {
            height: 300px;
            width: 100%;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Pending Vet Registrations</h2>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php 
        if ($result->num_rows > 0):
            while ($vet = $result->fetch_assoc()): 
        ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3><?php echo htmlspecialchars($vet['name']); ?></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?php if ($vet['profile_picture']): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($vet['profile_picture']); ?>" 
                                     class="img-fluid rounded" alt="Profile Picture">
                            <?php else: ?>
                                <p>No profile picture available</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Qualification:</strong> <?php echo htmlspecialchars($vet['qualification']); ?></p>
                                    <p><strong>Specialization:</strong> <?php echo htmlspecialchars($vet['specialization']); ?></p>
                                    <p><strong>Experience:</strong> <?php echo htmlspecialchars($vet['experience']); ?> years</p>
                                    <p><strong>License Number:</strong> <?php echo htmlspecialchars($vet['license_number']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Clinic:</strong> <?php echo htmlspecialchars($vet['clinic_name']); ?></p>
                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($vet['clinic_address']); ?></p>
                                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($vet['contact_details']); ?></p>
                                    <p><strong>Services:</strong> <?php echo htmlspecialchars($vet['services']); ?></p>
                                </div>
                            </div>
                            
                            <?php if ($vet['latitude'] && $vet['longitude']): ?>
                                <!-- Google Map -->
                                <div id="map-<?php echo $vet['id']; ?>" class="map-container"></div>
                                <script>
                                    function initMap<?php echo $vet['id']; ?>() {
                                        const location = {
                                            lat: <?php echo $vet['latitude']; ?>,
                                            lng: <?php echo $vet['longitude']; ?>
                                        };
                                        
                                        const map = new google.maps.Map(
                                            document.getElementById('map-<?php echo $vet['id']; ?>'),
                                            {
                                                zoom: 15,
                                                center: location,
                                            }
                                        );
                                        
                                        // Add marker for clinic location
                                        const marker = new google.maps.Marker({
                                            position: location,
                                            map: map,
                                            title: '<?php echo htmlspecialchars($vet['clinic_name']); ?>'
                                        });

                                        // Add info window
                                        const infoWindow = new google.maps.InfoWindow({
                                            content: `
                                                <div>
                                                    <h6>${marker.getTitle()}</h6>
                                                    <p><?php echo htmlspecialchars($vet['clinic_address']); ?></p>
                                                </div>
                                            `
                                        });

                                        marker.addListener('click', () => {
                                            infoWindow.open(map, marker);
                                        });
                                    }

                                    // Initialize the map
                                    initMap<?php echo $vet['id']; ?>();
                                </script>
                            <?php else: ?>
                                <p>No location data available</p>
                            <?php endif; ?>

                            <!-- Documents -->
                            <h4>Uploaded Documents</h4>
                            <?php
                            $doc_query = "SELECT * FROM vet_documents WHERE vet_id = " . $vet['id'];
                            $docs = $conn->query($doc_query);
                            if ($docs && $docs->num_rows > 0):
                                while ($doc = $docs->fetch_assoc()):
                            ?>
                                <div class="mb-2">
                                    <strong><?php echo htmlspecialchars($doc['document_type']); ?>:</strong>
                                    <a href="view_document.php?id=<?php echo $doc['id']; ?>" 
                                       class="btn btn-sm btn-primary" target="_blank">View Document</a>
                                </div>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <p>No documents uploaded</p>
                            <?php endif; ?>

                            <!-- Approval/Rejection Form -->
                            <form method="POST" class="mt-3">
                                <input type="hidden" name="vet_id" value="<?php echo $vet['id']; ?>">
                                <button type="submit" name="action" value="approve" 
                                        class="btn btn-success me-2">Approve</button>
                                <button type="submit" name="action" value="reject" 
                                        class="btn btn-danger">Reject</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php 
            endwhile;
        else:
        ?>
            <div class="alert alert-info">No pending vet registrations found.</div>
        <?php endif; ?>
    </div>
</body>
</html>