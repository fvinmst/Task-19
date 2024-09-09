<?php
// Koneksi ke database
$connection = new mysqli('localhost', 'root', '', 'tech_blog');

// Cek koneksi
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Ambil semua kategori dari database
$categories = $connection->query("SELECT * FROM categories");

// Ambil input pencarian dari URL jika ada
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Ambil dua kategori dari URL jika ada
$category_id1 = isset($_GET['category_id1']) ? (int)$_GET['category_id1'] : null;
$category_id2 = isset($_GET['category_id2']) ? (int)$_GET['category_id2'] : null;

// SQL Query untuk pencarian artikel dengan multiple categories
$sql = "
    SELECT articles.*, GROUP_CONCAT(categories.category_name SEPARATOR ', ') AS category_names
    FROM articles
    JOIN article_categories ON articles.id = article_categories.article_id
    JOIN categories ON article_categories.category_id = categories.id
    WHERE 1=1
";

// Filter berdasarkan kategori jika ada
if ($category_id1 && $category_id2) {
    // Filter berdasarkan dua kategori
    $sql .= " AND (article_categories.category_id = ? OR article_categories.category_id = ?)";
} elseif ($category_id1) {
    // Filter berdasarkan satu kategori
    $sql .= " AND article_categories.category_id = ?";
}

// Filter berdasarkan pencarian jika ada
if (!empty($searchQuery)) {
    $searchQuery = $connection->real_escape_string($searchQuery); // Mengamankan input
    $sql .= " AND (articles.title LIKE ? OR articles.content LIKE ?)";
}

$sql .= " GROUP BY articles.id"; // Menggabungkan hasil berdasarkan artikel

// Siapkan dan eksekusi query
$stmt = $connection->prepare($sql);

// Bind parameter
$params = [];
$types = '';

if ($category_id1 && $category_id2) {
    $params[] = $category_id1;
    $params[] = $category_id2;
    $types .= 'ii'; // dua integer
} elseif ($category_id1) {
    $params[] = $category_id1;
    $types .= 'i'; // satu integer
}

if (!empty($searchQuery)) {
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
    $types .= 'ss'; // dua string
}

// Bind params and execute
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$articles = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Page</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">ALPHACORE</div>
            <ul class="nav-links">
                <li><a href="#">Home</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Services</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>
        <div class="header-content">
            <h1>Our mission is to make knowledge and news accessible for everyone.</h1>
            <p>With our integrated CRM, project management, collaboration and invoicing capabilities, you can manage your business in one secure platform.</p>
            <div class="search-bar">
                <form method="GET" action="index.php">
                    <span class="search-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px" fill="#000000">
                            <path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($searchQuery); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
        </div>
    </header>
    
    <nav class="categories">
        <a href="index.php" class="category-btn">All</a>
        <!-- Menampilkan kategori -->
        <?php while($category = $categories->fetch_assoc()): ?>
            <a href="index.php?category_id1=<?= $category['id']; ?>" class="category-btn"><?= htmlspecialchars($category['category_name']); ?></a>
        <?php endwhile; ?>
    </nav>
    
    <main class="main-content">
        <!-- Jika ada artikel, tampilkan -->
        <?php if ($articles->num_rows > 0): ?>
            <?php while($article = $articles->fetch_assoc()): ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($article['image']); ?>" alt="<?= htmlspecialchars($article['title']); ?>">
                    <div class="card-content">
                        <!-- Tampilkan nama kategori -->
                        <span class="category-label"><?= strtoupper(htmlspecialchars($article['category_names'])); ?></span>
                        <span class="post-info">Favian Mustafa Syaukani</span>
                        <h2><?= htmlspecialchars($article['title']); ?></h2>
                        <!-- Potong deskripsi artikel hingga 100 karakter -->
                        <p><?= htmlspecialchars(substr($article['content'], 0, 100)) . (strlen($article['content']) > 100 ? '...' : ''); ?></p>
                        <a href="detail.php?article_id=<?= $article['id']; ?>">Read Post</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No articles found in this category.</p>
        <?php endif; ?>
    </main>
    
    <footer class="footer">
        <button class="load-more">View more</button>
    </footer>
</body>
</html>

<?php
// Tutup koneksi database
$connection->close();
?>
