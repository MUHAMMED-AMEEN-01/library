<?php
session_start();
include 'db_connect.php';

// Security Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$msg = "";

// --- DELETE LOGIC ---
if (isset($_GET['delete_book'])) {
    $id = $_GET['delete_book'];
    $conn->query("DELETE FROM books WHERE id=$id");
    $msg = "<div class='alert success'>Book deleted successfully.</div>";
}
if (isset($_GET['delete_student'])) {
    $id = $_GET['delete_student'];
    $conn->query("DELETE FROM patrons WHERE id=$id");
    $msg = "<div class='alert success'>Patron deleted successfully.</div>";
}

// --- INSERT BOOK LOGIC ---
if (isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $call_num = $_POST['call_number'];
    $shelf = $_POST['shelf_number'];
    $genre = $_POST['genre'];
    $lang = $_POST['language'];
    
    $sql = "INSERT INTO books (title, author, call_number, shelf_number, genre, language) VALUES ('$title', '$author', '$call_num', '$shelf', '$genre', '$lang')";
    if($conn->query($sql)) { $msg = "<div class='alert success'>Book Added!</div>"; } 
    else { $msg = "<div class='alert error'>Error: " . $conn->error . "</div>"; }
}

// --- INSERT PATRON LOGIC (Updated for Category) ---
if (isset($_POST['add_student'])) {
    $name = $_POST['full_name'];
    $admission = $_POST['card_number']; 
    $phone = $_POST['phone'];
    $category = $_POST['category']; // UG, PG, or Staff
    
    $sql = "INSERT INTO patrons (full_name, card_number, phone, category) VALUES ('$name', '$admission', '$phone', '$category')";
    if($conn->query($sql)) { $msg = "<div class='alert success'>Patron Added!</div>"; } 
    else { $msg = "<div class='alert error'>Error: Admission Number likely exists.</div>"; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rook Staff | Manage Data</title>
    <link rel="stylesheet" href="splash-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Shared Theme Styles */
        :root { --primary: #2c3e50; --secondary: #3498db; --accent: #e74c3c; --text: #333; --bg: #f3f3f3; --card-bg: #fff; --border: #eee; }
        [data-theme="dark"] { --primary: #1a1a2e; --secondary: #4cc9f0; --accent: #f72585; --text: #f8f9fa; --bg: #121212; --card-bg: #1e1e1e; --border: #333; }

        body { background-color: var(--bg); color: var(--text); font-family: 'Segoe UI', sans-serif; transition: 0.3s; }
        .admin-header { background: var(--primary); padding: 1rem; color: white; display:flex; justify-content:space-between; align-items: center; }
        .admin-header a { color: white; text-decoration: none; margin-left: 15px; }
        
        .container { display: flex; max-width: 1300px; margin: 20px auto; gap: 20px; }
        
        .sidebar { width: 200px; background: var(--card-bg); padding: 15px; height: fit-content; border-radius: 4px; border: 1px solid var(--border); }
        .sidebar a { display: block; padding: 10px; color: var(--text); text-decoration: none; border-bottom: 1px solid var(--border); }
        .sidebar a:hover { color: var(--secondary); background: rgba(52, 152, 219, 0.1); }

        .main-content { flex: 1; display: flex; gap: 20px; flex-wrap: wrap; }
        
        .card { background: var(--card-bg); padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex: 1; min-width: 45%; border: 1px solid var(--border); }
        h2 { margin-top: 0; border-bottom: 2px solid var(--secondary); padding-bottom: 10px; font-size: 1.2rem; color: var(--primary); }
        [data-theme="dark"] h2 { color: var(--secondary); }
        
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; background: var(--bg); color: var(--text); }
        .btn-add { background: var(--secondary); color: white; border: none; padding: 10px; width: 100%; cursor: pointer; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem; }
        th { background: rgba(0,0,0,0.05); text-align: left; padding: 8px; border-bottom: 2px solid var(--border); }
        td { padding: 8px; border-bottom: 1px solid var(--border); }
        
        .action-btn { padding: 4px 8px; text-decoration: none; border-radius: 3px; font-size: 0.8rem; margin-right: 5px; }
        .edit { background: #f39c12; color: white; }
        .delete { background: var(--accent); color: white; }
        
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background: rgba(46, 204, 113, 0.2); color: #27ae60; }
        .error { background: rgba(231, 76, 60, 0.2); color: #c0392b; }
        
        .theme-toggle { background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem; }
    </style>
</head>
<body>

<div class="admin-header">
    <div><strong>Rook Management</strong></div>
    <div>
        <button class="theme-toggle" id="themeBtn"><i class="fas fa-moon"></i></button>
        <span style="margin: 0 15px;">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
        <a href="admin_logout.php" style="color: var(--accent);">Logout</a>
    </div>
</div>

<div class="container">
    <div class="sidebar">
        <a href="admin_entry.php" style="font-weight:bold; color:var(--secondary);">Manage Data</a>
        <a href="admin_circulation.php">Circulation & Fines</a>
    </div>

    <div class="main-content">
        <div style="width:100%"><?php echo $msg; ?></div>

        <div class="card">
            <h2><i class="fas fa-book"></i> Manage Books</h2>
            <form method="POST">
                <strong>Add New Book</strong>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:10px;">
                    <input type="text" name="title" placeholder="Title" required>
                    <input type="text" name="author" placeholder="Author" required>
                    <input type="text" name="call_number" placeholder="Call No" required>
                    <input type="text" name="shelf_number" placeholder="Shelf">
                    <input type="text" name="genre" placeholder="Genre">
                    <select name="language">
                        <option value="English">English</option>
                        <option value="Malayalam">Malayalam</option>
                        <option value="Hindi">Hindi</option>
                        <option value="Arabic">Arabic</option>
                    </select>
                </div>
                <button type="submit" name="add_book" class="btn-add">Add Book</button>
            </form>
            
            <div style="margin-top:20px; max-height:300px; overflow-y:auto;">
                <table>
                    <thead><tr><th>Title</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php 
                        $res = $conn->query("SELECT id, title FROM books ORDER BY id DESC LIMIT 10");
                        while($row = $res->fetch_assoc()) {
                            echo "<tr><td>{$row['title']}</td><td><a href='?delete_book={$row['id']}' class='action-btn delete'><i class='fas fa-trash'></i></a></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2><i class="fas fa-users"></i> Manage Patrons</h2>
            <form method="POST">
                <strong>Add New Patron</strong>
                <div style="margin-top:10px;">
                    <input type="text" name="full_name" placeholder="Full Name" required>
                    <input type="text" name="card_number" placeholder="Admission Number" required>
                    <input type="text" name="phone" placeholder="Phone Number">
                    <label style="font-size:0.9rem; font-weight:bold;">Category:</label>
                    <select name="category" required>
                        <option value="UG">UG Student (Max 3 Books)</option>
                        <option value="PG">PG Student (Max 4 Books)</option>
                        <option value="Staff">Staff (Max 4 Books)</option>
                    </select>
                </div>
                <button type="submit" name="add_student" class="btn-add">Add Patron</button>
            </form>
             <div style="margin-top:20px; max-height:300px; overflow-y:auto;">
                <table>
                    <thead><tr><th>Name</th><th>Category</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php 
                        $res = $conn->query("SELECT id, full_name, category FROM patrons ORDER BY id DESC LIMIT 10");
                        while($row = $res->fetch_assoc()) {
                            echo "<tr><td>{$row['full_name']}</td><td>{$row['category']}</td><td><a href='?delete_student={$row['id']}' class='action-btn delete'><i class='fas fa-trash'></i></a></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Theme Toggle Logic
    const themeBtn = document.getElementById('themeBtn');
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.body.setAttribute('data-theme', currentTheme);
    
    themeBtn.addEventListener('click', () => {
        let theme = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.body.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    });
</script>
</body>
</html>