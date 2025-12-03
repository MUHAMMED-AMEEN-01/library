<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rook Library | Books</title>
    <link rel="stylesheet" href="splash-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Exact styles from your design */
        :root { --primary: #2c3e50; --secondary: #3498db; --accent: #e74c3c; --text: #333; --success: #2ecc71; }
        [data-theme="dark"] { --primary: #1a1a2e; --secondary: #4cc9f0; --text: #f8f9fa; background-color: #121212; }
        body { background-color: #f9f9f9; color: var(--text); font-family: 'Segoe UI', system-ui, sans-serif; padding-bottom: 60px; margin: 0; }
        header { background-color: var(--primary); color: white; padding: 1rem; position: sticky; top:0; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header-container { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { font-size: 1.25rem; font-weight: 700; display:flex; gap:10px; align-items:center;}
        nav ul { display: flex; gap: 1rem; list-style:none; }
        nav a { color: white; text-decoration: none; padding: 5px 10px; border-radius: 4px; transition: 0.3s; }
        nav a:hover { background: rgba(255,255,255,0.1); }
        main { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        
        .section-title { border-bottom: 2px solid var(--secondary); display: inline-block; padding-bottom: 5px; margin-bottom: 20px; color: var(--primary); }
        [data-theme="dark"] .section-title { color: white; }

        .book-item { 
            background: white; padding: 1.5rem; margin-bottom: 1rem; border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center;
            transition: transform 0.2s;
        }
        .book-item:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        [data-theme="dark"] .book-item { background: #1e1e1e; box-shadow: 0 2px 5px rgba(0,0,0,0.4); }
        
        .book-title { font-weight: bold; font-size: 1.1rem; color: var(--primary); margin-bottom: 5px; }
        [data-theme="dark"] .book-title { color: #c3dce7; }
        
        .book-details { margin-top: 8px; }
        .book-details span { margin-right: 20px; font-size: 0.9rem; color: #666; display: inline-flex; align-items: center; gap: 5px; }
        [data-theme="dark"] .book-details span { color: #aaa; }
        
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-available { background: rgba(46, 204, 113, 0.15); color: #27ae60; }
        .status-checked-out { background: rgba(231, 76, 60, 0.15); color: #c0392b; }
    </style>
</head>
<body>
    <script src="splash.js"></script>
    <header>
        <div class="header-container">
            <div class="logo"><i class="fas fa-book-open"></i> Rook Library</div>
            <nav>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="eresource.html">E-Resources</a></li>
                    <li><a href="books.php" style="background:var(--secondary);">Books</a></li>
                    <li><a href="community.html">Community</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="account.html">Account</a></li>
                    <li><a href="admin_entry.php" style="border: 1px solid rgba(255,255,255,0.3);">Staff Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <h2 class="section-title">Library Catalog</h2>
        
        <div class="books-list">
            <?php
            // Fetch books from database
            $sql = "SELECT * FROM books ORDER BY id DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Logic for status badge color
                    $statusClass = ($row['status'] == 'available') ? 'status-available' : 'status-checked-out';
                    
                    echo "
                    <div class='book-item'>
                        <div>
                            <div class='book-title'>{$row['title']}</div>
                            <div style='color:#3498db; margin-bottom:8px; font-weight:500;'>{$row['author']}</div>
                            <div class='book-details'>
                                <span><i class='fas fa-bookmark'></i> {$row['genre']}</span>
                                <span><i class='fas fa-map-marker-alt'></i> {$row['shelf_number']}</span>
                                <span><i class='fas fa-barcode'></i> {$row['call_number']}</span>
                                <span><i class='fas fa-language'></i> {$row['language']}</span>
                            </div>
                        </div>
                        <div class='status-badge {$statusClass}'>" . ucfirst($row['status']) . "</div>
                    </div>";
                }
            } else {
                echo "<p style='text-align:center; padding: 20px;'>No books found in the library database yet.</p>";
            }
            ?>
        </div>
    </main>
</body>
</html>