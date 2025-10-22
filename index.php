<?php
$games = json_decode(file_get_contents('games.json'), true);

// Split games into sections (you can tweak this)
$featured = array_slice($games, 0, 5);

// Get all unique categories
$categories = [];
foreach ($games as $game) {
    if (isset($game['category'])) {
        foreach ($game['category'] as $cat) {
            if (!in_array($cat, $categories)) {
                $categories[] = $cat;
            }
        }
    }
}
sort($categories);

// Group games by category
$gamesByCategory = [];
foreach ($categories as $category) {
    $gamesByCategory[$category] = array_filter($games, function($game) use ($category) {
        return isset($game['category']) && in_array($category, $game['category']);
    });
}

// Handle search and category filtering
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

// Filter games based on search and category
$filteredGames = $games;
if (!empty($searchQuery)) {
    $filteredGames = array_filter($filteredGames, function($game) use ($searchQuery) {
        return stripos($game['name'], $searchQuery) !== false || 
               stripos($game['description'], $searchQuery) !== false;
    });
}

if (!empty($selectedCategory)) {
    $filteredGames = array_filter($filteredGames, function($game) use ($selectedCategory) {
        return isset($game['category']) && in_array($selectedCategory, $game['category']);
    });
}

// Update sections with filtered games if needed
if (!empty($searchQuery) || !empty($selectedCategory)) {
    $featured = array_slice($filteredGames, 0, 5);
    
    // Update games by category for filtered results
    $gamesByCategory = [];
    foreach ($categories as $category) {
        $gamesByCategory[$category] = array_filter($filteredGames, function($game) use ($category) {
            return isset($game['category']) && in_array($category, $game['category']);
        });
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimadaGames - Play Free Games Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(10px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        .game-card {
            transition: all 0.3s ease;
        }
        .game-card:hover {
            transform: translateY(-8px);
        }
        .carousel-container {
            scrollbar-width: thin;
            scrollbar-color: #475569 #1e293b;
        }
        .carousel-container::-webkit-scrollbar {
            height: 6px;
        }
        .carousel-container::-webkit-scrollbar-track {
            background: #1e293b;
            border-radius: 10px;
        }
        .carousel-container::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 10px;
        }
        .featured-game {
            background: linear-gradient(to right, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.7)), url('<?= htmlspecialchars($featured[0]['thumbnail']) ?>');
            background-size: cover;
            background-position: center;
        }
        .search-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .category-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.25rem;
        }
    </style>
</head>
<body class="bg-dark-900 text-white font-poppins min-h-screen gradient-bg">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-dark-800/90 backdrop-blur-md border-b border-dark-700 shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <div class="bg-gradient-to-r from-primary-500 to-primary-700 p-2 rounded-lg">
                        <i class="fas fa-gamepad text-white text-xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">SimadaGames</h1>
                </div>
                
                <!-- Search and Filters -->
                <form method="GET" class="w-full md:w-auto flex flex-col md:flex-row gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-dark-400"></i>
                        </div>
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search games..." 
                            class="w-full pl-10 pr-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent search-shadow"
                            value="<?= htmlspecialchars($searchQuery) ?>"
                        >
                    </div>
                    
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-filter text-dark-400"></i>
                        </div>
                        <select 
                            name="category" 
                            class="w-full pl-10 pr-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent appearance-none search-shadow"
                        >
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $selectedCategory === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-dark-400"></i>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-700 hover:from-primary-600 hover:to-primary-800 text-white font-medium rounded-lg transition-all duration-300 flex items-center gap-2 search-shadow">
                            <i class="fas fa-search"></i>
                            <span>Search</span>
                        </button>
                        
                        <?php if (!empty($searchQuery) || !empty($selectedCategory)): ?>
                            <a href="?" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white font-medium rounded-lg transition-all duration-300 flex items-center gap-2 search-shadow">
                                <i class="fas fa-times"></i>
                                <span>Clear</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-6">
        <!-- Search Results Section -->
        <?php if (!empty($searchQuery) || !empty($selectedCategory)): ?>
            <section class="mb-12 animate-fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-white">
                        Search Results 
                        <span class="text-primary-400">(<?= count($filteredGames) ?> games found)</span>
                    </h2>
                </div>
                
                <?php if (count($filteredGames) > 0): ?>
                    <div class="carousel-container flex gap-6 overflow-x-auto pb-4">
                        <?php foreach ($filteredGames as $index => $game): ?>
                            <a href="play.php?id=<?= $index ?>" class="game-card flex-shrink-0 w-64 bg-dark-800 rounded-xl overflow-hidden border border-dark-700 hover:border-primary-500/50 shadow-lg">
                                <div class="relative">
                                    <img 
                                        src="<?= htmlspecialchars($game['thumbnail']) ?>" 
                                        alt="<?= htmlspecialchars($game['name']) ?>" 
                                        class="w-full h-40 object-cover"
                                    >
                                    <div class="absolute top-3 left-3">
                                        <span class="px-2 py-1 bg-primary-500 text-white text-xs font-semibold rounded-full">
                                            <?= isset($game['category'][0]) ? htmlspecialchars($game['category'][0]) : 'Game' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-white truncate"><?= htmlspecialchars($game['name']) ?></h3>
                                    <p class="text-dark-300 text-sm mt-2 line-clamp-2"><?= htmlspecialchars($game['description']) ?></p>
                                    <div class="mt-3 flex justify-between items-center">
                                        <span class="text-primary-400 text-sm font-medium">Play Now</span>
                                        <i class="fas fa-arrow-right text-primary-400"></i>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 bg-dark-800/50 rounded-xl border border-dark-700">
                        <i class="fas fa-gamepad text-5xl text-dark-500 mb-4"></i>
                        <h3 class="text-xl font-semibold text-white mb-2">No games found</h3>
                        <p class="text-dark-400">Try adjusting your search or filter criteria</p>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <!-- Featured Game Banner -->
        <section class="mb-12 animate-slide-up">
            <div class="rounded-2xl overflow-hidden shadow-2xl">
                <a href="play.php?id=0" class="featured-game block p-8 md:p-12 min-h-80 flex flex-col justify-end">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 bg-primary-500/20 backdrop-blur-sm text-primary-300 px-3 py-1 rounded-full text-sm font-medium mb-4">
                            <i class="fas fa-star"></i>
                            <span>Featured Game</span>
                        </div>
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-3"><?= htmlspecialchars($featured[0]['name']) ?></h2>
                        <p class="text-dark-200 mb-6 max-w-xl"><?= htmlspecialchars($featured[0]['description']) ?></p>
                        <button class="px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-700 hover:from-primary-600 hover:to-primary-800 text-white font-semibold rounded-lg transition-all duration-300 flex items-center gap-2 shadow-lg">
                            <i class="fas fa-play"></i>
                            <span>Play Now</span>
                        </button>
                    </div>
                </a>
            </div>
        </section>

        <!-- Featured Games Section -->
        <section class="mb-12 animate-slide-up">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-fire text-primary-500"></i>
                    <span>Featured Games</span>
                </h2>
                <a href="#" class="text-primary-400 hover:text-primary-300 font-medium flex items-center gap-1 transition-colors">
                    <span>View All</span>
                    <i class="fas fa-chevron-right text-sm"></i>
                </a>
            </div>
            
            <div class="carousel-container flex gap-6 overflow-x-auto pb-4">
                <?php foreach ($featured as $index => $game): ?>
                    <a href="play.php?id=<?= $index ?>" class="game-card flex-shrink-0 w-64 bg-dark-800 rounded-xl overflow-hidden border border-dark-700 hover:border-primary-500/50 shadow-lg">
                        <div class="relative">
                            <img 
                                src="<?= htmlspecialchars($game['thumbnail']) ?>" 
                                alt="<?= htmlspecialchars($game['name']) ?>" 
                                class="w-full h-40 object-cover"
                            >
                            <div class="absolute top-3 left-3">
                                <span class="px-2 py-1 bg-primary-500 text-white text-xs font-semibold rounded-full">
                                    Featured
                                </span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-white truncate"><?= htmlspecialchars($game['name']) ?></h3>
                            <p class="text-dark-300 text-sm mt-2 line-clamp-2"><?= htmlspecialchars($game['description']) ?></p>
                            <div class="mt-3 flex justify-between items-center">
                                <span class="text-primary-400 text-sm font-medium">Play Now</span>
                                <i class="fas fa-arrow-right text-primary-400"></i>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Category Sections -->
        <?php 
        $categoryColors = [
            'Action' => 'red',
            'Adventure' => 'emerald',
            'Puzzle' => 'yellow',
            'Strategy' => 'purple',
            'Racing' => 'blue',
            'Sports' => 'green',
            'Arcade' => 'pink',
            'Shooter' => 'orange',
            'Simulation' => 'indigo',
            'Casual' => 'teal'
        ];
        
        $categoryIcons = [
            'Action' => 'fa-explosion',
            'Adventure' => 'fa-mountain',
            'Puzzle' => 'fa-puzzle-piece',
            'Strategy' => 'fa-chess',
            'Racing' => 'fa-flag-checkered',
            'Sports' => 'fa-futbol',
            'Arcade' => 'fa-gamepad',
            'Shooter' => 'fa-crosshairs',
            'Simulation' => 'fa-plane',
            'Casual' => 'fa-coffee'
        ];
        
        foreach ($categories as $category): 
            if (count($gamesByCategory[$category]) > 0):
                $color = $categoryColors[$category] ?? 'primary';
                $icon = $categoryIcons[$category] ?? 'fa-gamepad';
        ?>
            <section class="mb-12 animate-slide-up">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                        <div class="category-icon bg-<?= $color ?>-500/20 text-<?= $color ?>-400">
                            <i class="fas <?= $icon ?>"></i>
                        </div>
                        <span><?= htmlspecialchars($category) ?> Games</span>
                    </h2>
                    <a href="?category=<?= urlencode($category) ?>" class="text-<?= $color ?>-400 hover:text-<?= $color ?>-300 font-medium flex items-center gap-1 transition-colors">
                        <span>View All</span>
                        <i class="fas fa-chevron-right text-sm"></i>
                    </a>
                </div>
                
                <div class="carousel-container flex gap-6 overflow-x-auto pb-4">
                    <?php foreach (array_slice($gamesByCategory[$category], 0, 10) as $index => $game): 
                        // Find the original index of the game
                        $originalIndex = array_search($game, $games);
                    ?>
                        <a href="play.php?id=<?= $originalIndex ?>" class="game-card flex-shrink-0 w-64 bg-dark-800 rounded-xl overflow-hidden border border-dark-700 hover:border-<?= $color ?>-500/50 shadow-lg">
                            <div class="relative">
                                <img 
                                    src="<?= htmlspecialchars($game['thumbnail']) ?>" 
                                    alt="<?= htmlspecialchars($game['name']) ?>" 
                                    class="w-full h-40 object-cover"
                                >
                                <div class="absolute top-3 left-3">
                                    <span class="px-2 py-1 bg-<?= $color ?>-500 text-white text-xs font-semibold rounded-full">
                                        <?= htmlspecialchars($category) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-white truncate"><?= htmlspecialchars($game['name']) ?></h3>
                                <p class="text-dark-300 text-sm mt-2 line-clamp-2"><?= htmlspecialchars($game['description']) ?></p>
                                <div class="mt-3 flex justify-between items-center">
                                    <span class="text-<?= $color ?>-400 text-sm font-medium">Play Now</span>
                                    <i class="fas fa-arrow-right text-<?= $color ?>-400"></i>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php 
            endif;
        endforeach; 
        ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark-800 border-t border-dark-700 py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <div class="bg-gradient-to-r from-primary-500 to-primary-700 p-2 rounded-lg">
                        <i class="fas fa-gamepad text-white"></i>
                    </div>
                    <h2 class="text-xl font-bold bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">SimadaGames</h2>
                </div>
                
                <div class="text-dark-400 text-center md:text-right">
                    <p class="mb-2">Play the best free online games at SimadaGames</p>
                    <p>&copy; <?= date('Y') ?> SimadaGames. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
