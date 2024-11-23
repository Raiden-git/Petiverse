<?php
include 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch vet data including more details
$sql = "SELECT id, name, qualification, experience, clinic_name, consultation_fee, latitude, longitude FROM vets";
$result = mysqli_query($conn, $sql);
$vets = [];

while ($row = mysqli_fetch_assoc($result)) {
    $vets[] = $row;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        tr.expandable {
            cursor: pointer;
        }
        tr.details-row {
            display: none;
        }
        .appointment-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        .appointment-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<?php include 'Cus-NavBar/navBar.php'; ?>
    <!-- Table of vets -->
<h3>List of Registered Vets</h3>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Qualification</th>
            <th>Experience</th>
            <th>Clinic Name</th>
            <th>Consultation Fee</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($vets as $vet): ?>
        <tr class="expandable" data-vet-id="<?= $vet['id'] ?>">
            <td><?= htmlspecialchars($vet['name']) ?></td>
            <td><?= htmlspecialchars($vet['qualification']) ?></td>
            <td><?= htmlspecialchars($vet['experience']) ?> years</td>
            <td><?= htmlspecialchars($vet['clinic_name']) ?></td>
            <td>Rs.<?= htmlspecialchars($vet['consultation_fee']) ?></td>
        </tr>
        <tr class="details-row" id="details-<?= $vet['id'] ?>">
            <td colspan="5">
                <strong>Services:</strong> <?= htmlspecialchars($vet['services']) ?><br>
                <strong>More Info:</strong> This is where additional details about the vet can go.<br><br>
                <!-- Make an Appointment Button -->
                <a href="book_appointment.php?vet_id=<?= $vet['id'] ?>" class="appointment-btn">Make an Appointment</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    //Vet Table
     // Expand/Collapse row logic
     document.addEventListener('DOMContentLoaded', function() {
        var rows = document.querySelectorAll('tr.expandable');
        var expandedRow = null;

        rows.forEach(function(row) {
            row.addEventListener('click', function() {
                var vetId = row.getAttribute('data-vet-id');
                var detailsRow = document.getElementById('details-' + vetId);

                // Collapse the previously expanded row, if any
                if (expandedRow && expandedRow !== detailsRow) {
                    expandedRow.style.display = 'none';
                }

                // Toggle the clicked row's details
                if (detailsRow.style.display === 'table-row') {
                    detailsRow.style.display = 'none';
                } else {
                    detailsRow.style.display = 'table-row';
                    expandedRow = detailsRow;  
                }
            });
        });
    });
</script>
    
</body>
</html>