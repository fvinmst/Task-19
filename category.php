<?php
// Koneksi ke database
$connection = new mysqli('localhost', 'root', '', 'tech_blog');

// Cek koneksi
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Ambil semua kategori dari database
$categories = $connection->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blog Categories</title>
    <style>
        .category-btn {
            display: inline-block;
            margin: 5px;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Filter Articles by Category</h1>
    <div>
        <a href="index.php" class="category-btn">All</a> <!-- Tombol untuk menampilkan semua artikel -->
        <?php while($category = $categories->fetch_assoc()): ?>
            <a href="index.php?category_id=<?= $category['id']; ?>" class="category-btn"><?= $category['category_name']; ?></a>
        <?php endwhile; ?>
    </div>

    <!-- Bagian untuk menampilkan artikel -->
    <div id="articles">
        <?php
        // Cek apakah ada parameter category_id di URL
        $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;

        // Query untuk mengambil artikel, filter berdasarkan category_id jika ada
        if ($category_id) {
            $articles = $connection->query("SELECT * FROM articles WHERE category_id = $category_id");
        } else {
            $articles = $connection->query("SELECT * FROM articles");
        }

        // Tampilkan artikel
        if ($articles->num_rows > 0) {
            while($article = $articles->fetch_assoc()) {
                echo "<div>";
                echo "<h2>" . $article['title'] . "</h2>";
                echo "<p>" . $article['content'] . "</p>";
                if (!empty($article['image'])) {
                    echo "<img src='" . $article['image'] . "' alt='" . $article['title'] . "' style='max-width: 200px;'/>";
                }
                echo "</div>";
            }
        } else {
            echo "<p>No articles found in this category.</p>";
        }

        // Tutup koneksi
        $connection->close();
        ?>
    </div>
</body>
</html>
