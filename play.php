<?php
// Read games.json and handle errors
$gamesData = file_get_contents('games.json');
if ($gamesData === false) {
    die("Error: Could not read games.json.");
}

$games = json_decode($gamesData, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("JSON Error: " . json_last_error_msg());
}

// Get ID from URL
$id = $_GET['id'] ?? '';

// Check if ID is a number and within range
if (!is_numeric($id) || $id < 0 || $id >= count($games)) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Game Not Found – SimadaGames</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <style>
        body { font-family: system-ui, sans-serif; }
    </style>
    </head>
    <body class='bg-gray-900 min-h-screen text-white flex items-center justify-center'>
        <div class='text-center'>
            <h1 class='text-2xl font-bold mb-4'>Game Not Found</h1>
            <a href='index.php' class='px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors'>
                ← Back to Games
            </a>
        </div>
    </body>
    </html>";
    exit;
}

$game = $games[intval($id)];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($game['name']) ?> – SimadaGames</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900">
    <!-- Simple Top Bar -->
    <header class="bg-gray-800 border-b border-gray-700 px-4 py-2 flex items-center justify-between">
        <a href="index.php" class="text-white hover:text-gray-300 transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </a>
        
        <h1 class="text-white font-medium truncate max-w-xs md:max-w-md text-sm md:text-base">
            <?= htmlspecialchars($game['name']) ?>
        </h1>
        
        <button id="fullscreen-btn" class="text-white hover:text-gray-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
            </svg>
        </button>
    </header>

    <!-- Game Container -->
    <div class="relative w-full h-screen" style="height: calc(100vh - 49px);">
        <!-- Loading State -->
        <div id="loading" class="absolute inset-0 bg-gray-900 flex items-center justify-center z-10">
            <div class="text-center">
                <div class="w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                <p class="text-white text-sm">Loading game...</p>
            </div>
        </div>

        <!-- Game Frame -->
        <iframe 
            id="game-frame"
            src="<?= htmlspecialchars($game['url']) ?>" 
            class="w-full h-full"
            allow="fullscreen"
            onload="hideLoading()"
            onerror="showError()">
        </iframe>
    </div>

    <script>
        // Loading states
        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        function showError() {
            const loading = document.getElementById('loading');
            loading.innerHTML = `
                <div class="text-center">
                    <div class="text-red-500 mb-2">⚠️</div>
                    <p class="text-white text-sm">Failed to load game</p>
                    <button onclick="location.reload()" class="mt-2 px-4 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition-colors">
                        Retry
                    </button>
                </div>
            `;
        }

        // Fullscreen functionality
        document.getElementById('fullscreen-btn').addEventListener('click', toggleFullscreen);
        
        function toggleFullscreen() {
            const gameFrame = document.getElementById('game-frame');
            
            if (!document.fullscreenElement) {
                gameFrame.requestFullscreen?.();
            } else {
                document.exitFullscreen?.();
            }
        }

        // ESC key to go back
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                } else {
                    window.location.href = 'index.php';
                }
            }
        });

        // Show timeout message after 10 seconds
        setTimeout(() => {
            const loading = document.getElementById('loading');
            if (loading.style.display !== 'none') {
                const loadingText = loading.querySelector('p');
                if (loadingText) {
                    loadingText.textContent = 'Still loading...';
                }
            }
        }, 10000);
    </script>
</body>
</html>
