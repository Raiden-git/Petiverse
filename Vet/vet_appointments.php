<?php
include('../db.php');
session_start();

// Check if vet is logged in
if (!isset($_SESSION['vet_id'])) {
    header("Location: index.php");
    exit();
}
$vet_id = $_SESSION['vet_id'];


// Pagination setup
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$results_per_page = 10;
$offset = ($page - 1) * $results_per_page;

// Filter options
$status_filter = $_GET['status'] ?? 'all';
$mode_filter = $_GET['mode'] ?? 'all';
$date_filter = $_GET['date'] ?? '';

// Build base query
$sql = "SELECT 
            a.id AS appointment_id,
            CONCAT(u.first_name, ' ', u.last_name) AS user_name,
            u.email AS user_email,
            a.appointment_type_id,
            a.appointment_date,
            a.start_time,
            a.end_time,
            a.status,
            a.notes,
            a.mode
        FROM 
            appointments a
        JOIN 
            users u ON a.user_id = u.id
        WHERE 
            a.vet_id = ?";

// Add filters
$params = [$vet_id];
$param_types = 'i';

if ($status_filter !== 'all') {
    $sql .= " AND a.status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if ($mode_filter !== 'all') {
    $sql .= " AND a.mode = ?";
    $params[] = $mode_filter;
    $param_types .= 's';
}

if (!empty($date_filter)) {
    $sql .= " AND a.appointment_date = ?";
    $params[] = $date_filter;
    $param_types .= 's';
}

// Add ordering and pagination
$sql .= " ORDER BY a.appointment_date DESC, a.start_time 
          LIMIT ? OFFSET ?";
$params[] = $results_per_page;
$params[] = $offset;
$param_types .= 'ii';

// Prepare and execute main query
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get total count for pagination
$count_sql = "SELECT COUNT(*) AS total 
              FROM appointments a
              JOIN users u ON a.user_id = u.id
              WHERE a.vet_id = ?";
$count_params = [$vet_id];
$count_param_types = 'i';

if ($status_filter !== 'all') {
    $count_sql .= " AND a.status = ?";
    $count_params[] = $status_filter;
    $count_param_types .= 's';
}

if ($mode_filter !== 'all') {
    $count_sql .= " AND a.mode = ?";
    $count_params[] = $mode_filter;
    $count_param_types .= 's';
}

if (!empty($date_filter)) {
    $count_sql .= " AND a.appointment_date = ?";
    $count_params[] = $date_filter;
    $count_param_types .= 's';
}

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($count_param_types, ...$count_params);
$count_stmt->execute();
$total_result = $count_stmt->get_result()->fetch_assoc();
$total_appointments = $total_result['total'];
$total_pages = ceil($total_appointments / $results_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veterinarian Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<?php include('nav.php'); ?>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800">My Appointments</h1>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-end">
                <a href="manage_appointments.php" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">
                    Edit Schedule
                </a>
            </div>

            <!-- Filters -->
            <form method="GET" class="p-6 bg-white">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <!-- Mode Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Appointment Mode</label>
                        <select name="mode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="all" <?php echo $mode_filter === 'all' ? 'selected' : ''; ?>>All Modes</option>
                            <option value="online" <?php echo $mode_filter === 'online' ? 'selected' : ''; ?>>Online</option>
                            <option value="in-person" <?php echo $mode_filter === 'in-person' ? 'selected' : ''; ?>>In-Person</option>
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="date" 
                               value="<?php echo htmlspecialchars($date_filter); ?>" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>

            <!-- Appointments Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($row['user_name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($row['user_email']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo date('F d, Y', strtotime($row['appointment_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo date('h:i A', strtotime($row['start_time'])); ?> - 
                                        <?php echo date('h:i A', strtotime($row['end_time'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $status_colors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'confirmed' => 'bg-green-100 text-green-800',
                                            'completed' => 'bg-blue-100 text-blue-800',
                                            'cancelled' => 'bg-red-100 text-red-800'
                                        ];
                                        $status_color = $status_colors[$row['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_color; ?>">
                                            <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $mode_colors = [
                                            'online' => 'bg-green-100 text-green-800',
                                            'in-person' => 'bg-blue-100 text-blue-800'
                                        ];
                                        $mode_color = $mode_colors[$row['mode']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $mode_color; ?>">
                                            <?php echo ucfirst(htmlspecialchars($row['mode'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div x-data="{ open: false }" class="relative">
                                            <button @click="open = !open" class="text-indigo-600 hover:text-indigo-900">
                                                Actions
                                            </button>
                                            <div x-show="open" 
                                                 @click.away="open = false"
                                                 class="absolute z-10 right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                                <div class="py-1" role="menu" aria-orientation="vertical">
                                                    <a href="view_details.php?id=<?php echo $row['appointment_id']; ?>" 
                                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        View Details
                                                    </a>
                                                    <?php if ($row['status'] === 'pending'): ?>
                                                        <a href="confirm_appointment.php?id=<?php echo $row['appointment_id']; ?>" 
                                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            Confirm
                                                        </a>
                                                        <a href="cancel_appointment.php?id=<?php echo $row['appointment_id']; ?>" 
                                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            Cancel
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No appointments found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="px-6 py-4 bg-white border-t border-gray-200 flex justify-center">
                    <nav class="inline-flex rounded-md shadow-sm -space-x-px">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&mode=<?php echo urlencode($mode_filter); ?>&date=<?php echo urlencode($date_filter); ?>" 
                               class="<?php echo $page === $i ? 'bg-blue-500 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'; ?> 
                               relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$count_stmt->close();
$conn->close();
?>