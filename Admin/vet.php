<?php
include '../db.php'; // Database connection
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Handle approval/rejection
    if (isset($_POST['action'])) {
        $vet_id = $_POST['vet_id'];
        if ($_POST['action'] === 'approve') {
            $query = "UPDATE vets SET is_approved = 1 WHERE id = $vet_id";
        } elseif ($_POST['action'] === 'reject') {
            $query = "DELETE FROM vets WHERE id = $vet_id";
        }
        if ($conn->query($query)) {
            $message = "Action completed successfully.";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}

// Fetch all vets
$unapproved_vets_query = "SELECT * FROM vets WHERE is_approved = 0";
$approved_vets_query = "SELECT * FROM vets WHERE is_approved = 1";

$unapproved_vets = $conn->query($unapproved_vets_query);
$approved_vets = $conn->query($approved_vets_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Vet Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        h1 {
            text-align: center;
        }
        .vet-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .vet-table th, .vet-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        .vet-table th {
            background-color: #007BFF;
            color: white;
        }
        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        button {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .approve {
            background-color: #28a745;
            color: white;
        }
        .reject {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Vet Management</h1>

    <?php if (isset($message)) { ?>
        <p><?php echo $message; ?></p>
    <?php } ?>

    <h2>Unapproved Vets</h2>
    <table class="vet-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Qualification</th>
                <th>Experience</th>
                <th>Clinic Name</th>
                <th>Services</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($vet = $unapproved_vets->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $vet['name']; ?></td>
                    <td><?php echo $vet['qualification']; ?></td>
                    <td><?php echo $vet['experience']; ?> years</td>
                    <td><?php echo $vet['clinic_name']; ?></td>
                    <td><?php echo ucfirst($vet['services']); ?></td>
                    <td class="actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="vet_id" value="<?php echo $vet['id']; ?>">
                            <button type="submit" name="action" value="approve" class="approve">Approve</button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="vet_id" value="<?php echo $vet['id']; ?>">
                            <button type="submit" name="action" value="reject" class="reject">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h2>Approved Vets</h2>
    <table class="vet-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Qualification</th>
                <th>Experience</th>
                <th>Clinic Name</th>
                <th>Services</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($vet = $approved_vets->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $vet['name']; ?></td>
                    <td><?php echo $vet['qualification']; ?></td>
                    <td><?php echo $vet['experience']; ?> years</td>
                    <td><?php echo $vet['clinic_name']; ?></td>
                    <td><?php echo ucfirst($vet['services']); ?></td>
                    <td class="actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="vet_id" value="<?php echo $vet['id']; ?>">
                            <button type="submit" name="action" value="reject" class="reject">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>
