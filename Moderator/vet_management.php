<?php
include('../db.php');
include('session_check.php');

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Approve Vet
    if (isset($_POST['approve'])) {
        $vet_id = $_POST['vet_id'];
        $updateSql = "UPDATE vets SET approval_status = 'approved', is_approved = 1 WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("i", $vet_id);
        $stmt->execute();
        $stmt->close();
    }

    // Reject Vet
    if (isset($_POST['reject'])) {
        $vet_id = $_POST['vet_id'];
        $updateSql = "UPDATE vets SET approval_status = 'rejected', is_approved = 0 WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("i", $vet_id);
        $stmt->execute();
        $stmt->close();
    }

    // Delete Vet
    if (isset($_POST['delete'])) {
        $vet_id = $_POST['vet_id'];
        
        // First, delete associated documents
        $deleteDocs = "DELETE FROM vet_documents WHERE vet_id = ?";
        $stmt = $conn->prepare($deleteDocs);
        $stmt->bind_param("i", $vet_id);
        $stmt->execute();
        $stmt->close();

        // Then delete the vet
        $deleteVet = "DELETE FROM vets WHERE id = ?";
        $stmt = $conn->prepare($deleteVet);
        $stmt->bind_param("i", $vet_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch vets with their documents
$sql = "SELECT v.*, 
               GROUP_CONCAT(DISTINCT vd.document_type) AS document_types,
               COUNT(DISTINCT vd.id) AS document_count
        FROM vets v
        LEFT JOIN vet_documents vd ON v.id = vd.vet_id
        GROUP BY v.id";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Petiverse - Vet Management</title>
    <link rel="stylesheet" href="./moderator_sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHdLOieN0OIcTyyY6CmJv6gPNx-OX3MwA"></script>
    <style>
        .map {
            width: 100%;
            height: 200px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.12);
        }
        .status-pending { color: #F59E0B; }
        .status-approved { color: #10B981; }
        .status-rejected { color: #EF4444; }
    </style>
</head>
<body>
<header>
    <h1>Vet Management</h1>
</header>

<nav>
    <ul>
    <li><a href="moderator_dashboard.php">Home</a></li>
        <li><a href="Moderator_shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="admin_daycare_management.php">Daycare Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="petselling.php">Pet selling</a><li>
        <li><a href="view_feedback.php">Feedbacks</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-indigo-600 text-white px-6 py-4">
            <h1 class="text-2xl font-bold">Vet Management Dashboard</h1>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 gap-6">
                <?php
                $index = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $index++;
                    $statusClass = 'status-' . strtolower($row['approval_status']);
                    
                    // Fetch documents for this vet
                    $docSql = "SELECT document_type FROM vet_documents WHERE vet_id = {$row['id']}";
                    $docResult = mysqli_query($conn, $docSql);
                    $documents = [];
                    while ($docRow = mysqli_fetch_assoc($docResult)) {
                        $documents[] = $docRow['document_type'];
                    }
                    $documentList = implode(', ', $documents);
                ?>
                <div class="card bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <div class="grid md:grid-cols-3 gap-6">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 mb-4"><?php echo htmlspecialchars($row['name']); ?></h2>
                            <div class="space-y-2 text-gray-600">
                                <p><strong>Qualification:</strong> <?php echo htmlspecialchars($row['qualification']); ?></p>
                                <p><strong>Clinic:</strong> <?php echo htmlspecialchars($row['clinic_name']); ?></p>
                                <p><strong>Services:</strong> <?php echo htmlspecialchars($row['services']); ?></p>
                                <p><strong>Contact:</strong> <?php echo htmlspecialchars($row['contact_details']); ?></p>
                            </div>
                        </div>

                        <div>
                            <div id='map-<?php echo $index; ?>' class='map'></div>
                            <script>
                                function initMap<?php echo $index; ?>() {
                                    var location = {
                                        lat: <?php echo $row['latitude'] ?? 0; ?>, 
                                        lng: <?php echo $row['longitude'] ?? 0; ?>
                                    };
                                    var map = new google.maps.Map(document.getElementById('map-<?php echo $index; ?>'), {
                                        zoom: 12,
                                        center: location
                                    });
                                    var marker = new google.maps.Marker({
                                        position: location,
                                        map: map
                                    });
                                }
                                initMap<?php echo $index; ?>();
                            </script>
                        </div>

                        <div>
                            <div class="space-y-2">
                                <div>
                                    <strong>Approval Status:</strong> 
                                    <span class="<?php echo $statusClass; ?> font-bold">
                                        <?php echo ucfirst($row['approval_status']); ?>
                                    </span>
                                </div>
                                <div>
                                    <strong>Documents:</strong> 
                                    <span><?php echo $documentList ?: 'No documents'; ?></span>
                                </div>
                                <div class="mt-4 space-y-2">
                                    <form method='post' class="inline-block">
                                        <input type='hidden' name='vet_id' value='<?php echo $row['id']; ?>'>
                                        <button type='submit' name='approve' 
                                            class='bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 
                                            <?php echo ($row['approval_status'] == 'approved' ? 'opacity-50 cursor-not-allowed' : ''); ?>'
                                            <?php echo ($row['approval_status'] == 'approved' ? 'disabled' : ''); ?>>
                                            Approve
                                        </button>
                                    </form>
                                    <form method='post' class="inline-block">
                                        <input type='hidden' name='vet_id' value='<?php echo $row['id']; ?>'>
                                        <button type='submit' name='reject' 
                                            class='bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 
                                            <?php echo ($row['approval_status'] == 'rejected' ? 'opacity-50 cursor-not-allowed' : ''); ?>'
                                            <?php echo ($row['approval_status'] == 'rejected' ? 'disabled' : ''); ?>>
                                            Reject
                                        </button>
                                    </form>
                                    <form method='post' class="inline-block">
                                        <input type='hidden' name='vet_id' value='<?php echo $row['id']; ?>'>
                                        <button type='submit' name='delete' 
                                            onclick='return confirm("Are you sure you want to delete this vet?")'
                                            class='bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600'>
                                            Delete
                                        </button>
                                    </form>
                                    <form action='edit_vet.php' method='get' class="inline-block">
                                        <input type='hidden' name='vet_id' value='<?php echo $row['id']; ?>'>
                                        <button type='submit' 
                                            class='bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600'>
                                            Edit
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
</main>

<script>
    function confirmLogout() {
        return confirm("Do you really want to log out?");
    }
</script>
</body>
</html>