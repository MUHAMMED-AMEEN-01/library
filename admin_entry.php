<?php
include 'db_connect.php';
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
    $msg = "<div class='alert success'>Student deleted successfully.</div>";
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

// --- INSERT STUDENT LOGIC ---
if (isset($_POST['add_student'])) {
    $name = $_POST['full_name'];
    $admission = $_POST['card_number']; // Admission Number
    $phone = $_POST['phone'];
    
    $sql = "INSERT INTO patrons (full_name, card_number, phone) VALUES ('$name', '$admission', '$phone')";
    if($conn->query($sql)) { $msg = "<div class='alert success'>Student Added!</div>"; } 
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
        body { background-color: #f3f3f3; font-family: 'Segoe UI', sans-serif; }
        .admin-header { background: #2c3e50; padding: 1rem; color: white; display:flex; justify-content:space-between; align-items: center; }
        .admin-header a { color: white; text-decoration: none; margin-left: 15px; }
        .container { display: flex; max-width: 1300px; margin: 20px auto; gap: 20px; }
        
        .sidebar { width: 200px; background: white; padding: 15px; height: fit-content; border-radius: 4px; }
        .sidebar a { display: block; padding: 10px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; }
        .sidebar a:hover { color: #3498db; background: #f9f9f9; }

        .main-content { flex: 1; display: flex; gap: 20px; flex-wrap: wrap; }
        
        .card { background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex: 1; min-width: 45%; }
        h2 { margin-top: 0; border-bottom: 2px solid #85ca11; padding-bottom: 10px; font-size: 1.2rem; }
        
        /* Forms */
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        .btn-add { background: #3498db; color: white; border: none; padding: 10px; width: 100%; cursor: pointer; font-weight: bold; }
        .btn-add:hover { background: #2980b9; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem; }
        th { background: #f8f9fa; text-align: left; padding: 8px; border-bottom: 2px solid #ddd; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        
        .action-btn { padding: 4px 8px; text-decoration: none; border-radius: 3px; font-size: 0.8rem; margin-right: 5px; }
        .edit { background: #f39c12; color: white; }
        .delete { background: #e74c3c; color: white; }
        
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background: #dff0d8; color: #3c763d; }
        .error { background: #f2dede; color: #a94442; }
    </style>
</head>
<body>

<div class="admin-header">
    <div><strong>Rook Data Management</strong></div>
    <div><a href="books.php" target="_blank">View Public Site</a></div>
</div>

<div class="container">
    <div class="sidebar">
        <a href="admin_manage.php" style="font-weight:bold; color:#3498db;">Manage Data</a>
        <a href="admin_circulation.php">Circulation</a>
    </div>

    <div class="main-content">
        <div style="width:100%"><?php echo $msg; ?></div>

        <!-- LEFT COLUMN: BOOKS -->
        <div class="card">
            <h2><i class="fas fa-book"></i> Manage Books</h2>
            
            <!-- ADD BOOK FORM -->
            <form method="POST" style="background:#f9f9f9; padding:15px; margin-bottom:20px; border:1px solid #eee;">
                <strong>Add New Book</strong>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:10px;">
                    <input type="text" name="title" placeholder="Title" required>
                    <input type="text" name="author" placeholder="Author" required>
                    <input type="text" name="call_number" placeholder="Call No (Unique)" required>
                    <input type="text" name="shelf_number" placeholder="Shelf Location">
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

            <!-- BOOK LIST -->
            <div style="max-height: 400px; overflow-y: auto;">
                <table>
                    <thead><tr><th>Title</th><th>Call No</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php
                        $res = $conn->query("SELECT * FROM books ORDER BY id DESC");
                        while($row = $res->fetch_assoc()){
                            echo "<tr>
                                <td>{$row['title']}</td>
                                <td>{$row['call_number']}</td>
                                <td>
                                    <a href='edit_book.php?id={$row['id']}' class='action-btn edit'><i class='fas fa-edit'></i></a>
                                    <a href='?delete_book={$row['id']}' class='action-btn delete' onclick='return confirm(\"Delete this book?\")'><i class='fas fa-trash'></i></a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RIGHT COLUMN: STUDENTS -->
        <div class="card">
            <h2><i class="fas fa-users"></i> Manage Students</h2>
            
            <!-- ADD STUDENT FORM -->
            <form method="POST" style="background:#f9f9f9; padding:15px; margin-bottom:20px; border:1px solid #eee;">
                <strong>Add New Student</strong>
                <div style="margin-top:10px;">
                    <input type="text" name="full_name" placeholder="Student Name" required>
                    <input type="text" name="card_number" placeholder="Admission Number (Unique)" required>
                    <input type="text" name="phone" placeholder="Phone Number">
                </div>
                <button type="submit" name="add_student" class="btn-add">Add Student</button>
            </form>

            <!-- STUDENT LIST -->
            <div style="max-height: 400px; overflow-y: auto;">
                <table>
                    <thead><tr><th>Name</th><th>Admission No</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php
                        $res = $conn->query("SELECT * FROM patrons ORDER BY id DESC");
                        while($row = $res->fetch_assoc()){
                            echo "<tr>
                                <td>{$row['full_name']}</td>
                                <td>{$row['card_number']}</td>
                                <td>
                                    <a href='edit_student.php?id={$row['id']}' class='action-btn edit'><i class='fas fa-edit'></i></a>
                                    <a href='?delete_student={$row['id']}' class='action-btn delete' onclick='return confirm(\"Delete this student?\")'><i class='fas fa-trash'></i></a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>