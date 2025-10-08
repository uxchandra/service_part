<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>STEP Service Part System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Feather Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js"></script>
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f7fafc;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .scanner-ready {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(66, 153, 225, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(66, 153, 225, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(66, 153, 225, 0);
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- Header with Logo -->
        <div class="flex justify-center mb-6">
            <div class="text-center">
                <img src="{{ asset('images/logo.png') }}" alt="Company Logo" class="h-16 mx-auto mb-2">
                <h1 class="text-2xl font-bold text-gray-800">Service Part System</h1>
                <p class="text-sm text-gray-600">Mobile Scanner Dashboard</p>
            </div>
        </div>

        <!-- User Info -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="flex items-center">
                <div class="bg-blue-500 rounded-full p-2 mr-3">
                    <i data-feather="user" class="text-white h-5 w-5"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                    <p class="text-sm text-gray-600">{{ Auth::user()->role->name }}</p>
                </div>
                <div class="ml-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-500 flex items-center">
                            <i data-feather="log-out" class="h-4 w-4 mr-1"></i>
                            <span class="text-sm">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Menu Cards -->
        <div class="grid grid-cols-1 gap-4 mb-6">
            <!-- Barang Masuk Card -->
            <a href="{{ route('barang-masuk.index') }}" class="card bg-white rounded-lg shadow-md p-5 flex items-center">
                <div class="bg-green-500 rounded-full p-3 mr-4">
                    <i data-feather="download" class="text-white h-6 w-6"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800 text-lg">Posting</h3>
                    <p class="text-gray-600 text-sm">Scan barang masuk</p>
                </div>
                <i data-feather="chevron-right" class="text-gray-400 h-5 w-5"></i>
            </a>

            <!-- Barang Keluar Card -->
            <a href="{{ route('orders.index') }}" class="card bg-white rounded-lg shadow-md p-5 flex items-center">
                <div class="bg-red-500 rounded-full p-3 mr-4">
                    <i data-feather="upload" class="text-white h-6 w-6"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800 text-lg">Pulling</h3>
                    <p class="text-gray-600 text-sm">Pengeluaran & scan barang keluar</p>
                </div>
                <i data-feather="chevron-right" class="text-gray-400 h-5 w-5"></i>
            </a>

        </div>

        <!-- Footer -->
        <div class="text-center text-gray-500 text-xs mt-6">
            <p>&copy; {{ date('Y') }} Step Service Part System. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Initialize Feather Icons
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();

            // Handle barcode scanner input
            let scannerInput = '';
            const scanTimeout = 20; // ms between keystrokes
            let lastKeyTime = 0;
            
            document.addEventListener('keydown', function(e) {
                const currentTime = new Date().getTime();
                
                // If it's a rapid keystroke (from scanner)
                if (currentTime - lastKeyTime < scanTimeout && e.key !== 'Enter') {
                    scannerInput += e.key;
                } else if (e.key !== 'Enter') {
                    scannerInput = e.key;
                }
                
                lastKeyTime = currentTime;
                
                // When Enter is pressed, process the scanned barcode
                if (e.key === 'Enter' && scannerInput) {
                    console.log('Barcode Scanned:', scannerInput);
                    
                    // You can add AJAX call here to process the barcode
                    // For example, send to server and get product details
                    fetch('/scan-label/scan', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ barcode: scannerInput })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Handle successful scan
                            showNotification('Scan berhasil: ' + data.item.name, 'success');
                        } else {
                            // Handle failed scan
                            showNotification('Barcode tidak ditemukan!', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Terjadi kesalahan saat memproses barcode', 'error');
                    });
                    
                    scannerInput = '';
                }
            });
        });
        
        // Simple notification function
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 500);
            }, 3000);
        }
    </script>
</body>
</html>