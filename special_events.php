<?php
include('db.php');
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Events</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .event-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .event-card:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<?php include('Cus-NavBar/navBar.php'); ?>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center mb-10 text-gray-800">Special Events</h1>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            include('db.php');

            // Prepare SQL statement
            $query = "SELECT * FROM special_events ORDER BY date ASC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($event = $result->fetch_assoc()) {
                ?>
                <div class="bg-white rounded-lg overflow-hidden shadow-md event-card">
                    <?php if (!empty($event['image'])): ?>
                        <div class="h-48 overflow-hidden">
                            <img src="data:image/jpeg;base64,<?= base64_encode($event['image']) ?>" 
                                 alt="<?= htmlspecialchars($event['title']) ?>" 
                                 class="w-full h-full object-cover">
                        </div>
                    <?php else: ?>
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500">No Image Available</span>
                        </div>
                    <?php endif; ?>

                    <div class="p-6">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-3">
                            <?= htmlspecialchars($event['title']) ?>
                        </h2>
                        <p class="text-gray-600 mb-4">
                            <?= htmlspecialchars($event['description']) ?>
                        </p>
                        <div class="flex items-center text-gray-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span><?= htmlspecialchars($event['date']) ?></span>
                        </div>
                    </div>
                </div>
                <?php
            }

            $stmt->close();
            $conn->close();
            ?>
        </div>

        <div class="text-center mt-10">
            <a href="create_event.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300 inline-block">
                Publish New Event
            </a>
        </div>
    </div>
    <?php include('footer.php'); ?>
</body>
</html>
