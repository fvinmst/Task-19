<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article Detail</title>
    <link rel="stylesheet" href="body.css">
</head>
<body>
    <header>
        <h1>ALPHACORE</h1>
    </header>

    <main>
        <section id="article-detail">
            <?php
            if (isset($_GET['article_id'])) {
                $article_id = $_GET['article_id']; 
            } else {
                echo '<p>No article ID provided.</p>';
                exit(); 
            }

            if ($stmt = $mysqli->prepare("
                SELECT articles.*, 
                       GROUP_CONCAT(categories.category_name SEPARATOR ', ') AS category_names 
                FROM articles 
                JOIN article_categories ON articles.id = article_categories.article_id 
                JOIN categories ON article_categories.category_id = categories.id 
                WHERE articles.id = ?")) {
                
                $stmt->bind_param("i", $article_id); 
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
                    echo '<div class="image-container">';
                    echo '<img src="' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['title']) . '">';
                    echo '</div>';
                    echo '<p class="category">Category: ' . htmlspecialchars($row['category_names']) . '</p>';
                    echo '<div class="content">' . htmlspecialchars_decode($row['content']) . '</div>';
                } else {
                    echo '<p>Article not found.</p>';
                }
                $stmt->close();
            }
            ?>
        </section>
    </main>
</body>
</html>
