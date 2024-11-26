<?php
    // Start the session and include database connection
    session_start();
    include('../db.php');

    // Get vet ID from session
    $vet_id = isset($_SESSION['vet_id']) ? $_SESSION['vet_id'] : 0;

    // Handle status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id']) && isset($_POST['new_status'])) {
        $appointment_id = $_POST['appointment_id'];
        $new_status = $_POST['new_status'];
        $notes = $_POST['notes'];
        
        $update_sql = "UPDATE appointments SET status = ?, notes = ? WHERE id = ? AND vet_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssii", $new_status, $notes, $appointment_id, $vet_id);
        
        if ($stmt->execute()) {
            echo '<div class="alert alert-success">Appointment status updated successfully!</div>';
        } else {
            echo '<div class="alert alert-error">Error updating appointment status.</div>';
        }
    }


    // Base query
    $sql = "SELECT 
            a.*, 
            u.name AS user_name,
            u.email AS user_email,
            u.contact_number AS user_contact,
            at.name AS appointment_type
        FROM appointments a
        JOIN users u ON a.user_id = u.id
        JOIN appointment_types at ON a.appointment_type_id = at.id
        WHERE a.vet_id = ?
        ORDER BY a.appointment_date ASC, a.start_time ASC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $vet_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($appointment = $result->fetch_assoc()) {
            $status_class = "status-" . $appointment['status'];
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Management</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .appointment-filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filter-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .filter-group {
            flex: 1;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .appointments-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }

        .appointment-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .appointment-date {
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
        }

        .appointment-time {
            color: #666;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-completed {
            background-color: #cce5ff;
            color: #004085;
        }

        .appointment-details {
            margin: 15px 0;
        }

        .detail-row {
            margin-bottom: 10px;
        }

        .detail-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 3px;
        }

        .detail-value {
            color: #333;
        }

        .status-update {
            margin-top: 15px;
        }

        .status-update select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .update-btn {
            width: 100%;
            padding: 8px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .update-btn:hover {
            background-color: #45a049;
        }

        .notes-field {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 60px;
        }

        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    

    <div class="container">
        <div class="appointment-filters">
            <form id="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="date-filter">Date</label>
                        <input type="date" id="date-filter" name="date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="status-filter">Status</label>
                        <select id="status-filter" name="status">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <div class="appointments-grid">
        
                <div class="appointment-card">
                    <div class="appointment-header">
                        <div>
                            <div class="appointment-date">
                                <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?>
                            </div>
                            <div class="appointment-time">
                                <?php echo date('g:i A', strtotime($appointment['start_time'])) . ' - ' . 
                                           date('g:i A', strtotime($appointment['end_time'])); ?>
                            </div>
                        </div>
                        <span class="status-badge <?php echo $status_class; ?>">
                            <?php echo ucfirst($appointment['status']); ?>
                        </span>
                    </div>

                    <div class="appointment-details">
                        <div class="detail-row">
                            <div class="detail-label">Client Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($appointment['user_name']); ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Contact</div>
                            <div class="detail-value">
                                <?php echo htmlspecialchars($appointment['user_contact']); ?><br>
                                <?php echo htmlspecialchars($appointment['user_email']); ?>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Appointment Type</div>
                            <div class="detail-value"><?php echo htmlspecialchars($appointment['appointment_type']); ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Notes</div>
                            <div class="detail-value"><?php echo nl2br(htmlspecialchars($appointment['notes'])); ?></div>
                        </div>
                    </div>

                    <form class="status-update" method="POST">
                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                        <select name="new_status" class="status-select">
                            <option value="pending" <?php echo $appointment['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $appointment['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="cancelled" <?php echo $appointment['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="completed" <?php echo $appointment['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                        <textarea name="notes" class="notes-field" placeholder="Add notes about the appointment"><?php echo htmlspecialchars($appointment['notes']); ?></textarea>
                        <button type="submit" class="update-btn">Update Status</button>
                    </form>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Filter functionality
            $('#date-filter, #status-filter').on('change', function() {
                const date = $('#date-filter').val();
                const status = $('#status-filter').val();
                
                $('.appointment-card').each(function() {
                    let show = true;
                    
                    // Date filter
                    if (date) {
                        const cardDate = $(this).find('.appointment-date').text().trim();
                        if (new Date(cardDate).toISOString().split('T')[0] !== date) {
                            show = false;
                        }
                    }
                    
                    // Status filter
                    if (status && show) {
                        const cardStatus = $(this).find('.status-badge').text().trim().toLowerCase();
                        if (cardStatus !== status) {
                            show = false;
                        }
                    }
                    
                    $(this).toggle(show);
                });
            });
        });
    </script>
</body>
</html>