

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Event</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-blue-500 text-white p-6">
                <h1 class="text-3xl font-bold">Create New Event</h1>
                <p class="text-blue-100">Fill out the details for your upcoming event</p>
            </div>

            <form id="eventForm" action="process_event.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-gray-700 font-bold mb-2">Event Title</label>
                        <input type="text" id="title" name="title" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter event title">
                    </div>

                    <div>
                        <label for="date" class="block text-gray-700 font-bold mb-2">Event Date</label>
                        <input type="date" id="date" name="date" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-gray-700 font-bold mb-2">Event Description</label>
                    <textarea id="description" name="description" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Provide a detailed description of the event"></textarea>
                </div>

                <div>
                    <label for="image" class="block text-gray-700 font-bold mb-2">Event Image</label>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col border-4 border-dashed border-gray-200 hover:bg-gray-100 hover:border-blue-300 group relative">
                            <div class="flex flex-col items-center justify-center pt-7 cursor-pointer">
                                <svg class="w-10 h-10 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-sm text-gray-400 group-hover:text-blue-500">
                                    Click to upload image (optional)
                                </p>
                            </div>
                            <input type="file" name="image" id="image" accept="image/*" class="hidden">
                        </label>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <a href="index.php" class="text-gray-600 hover:text-gray-800 transition">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                        Create Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Basic client-side validation
        const title = document.getElementById('title').value.trim();
        const date = document.getElementById('date').value;
        const description = document.getElementById('description').value.trim();

        if (!title || !date || !description) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please fill out all required fields!'
            });
            return;
        }

        // If validation passes, submit the form
        this.submit();
    });

    // Image preview functionality
    document.getElementById('image').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // You could add image preview logic here if desired
            }
            reader.readAsDataURL(file);
        }
    });
    </script>
</body>
</html>