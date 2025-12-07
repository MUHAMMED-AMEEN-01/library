<?php
session_start();
include 'db_connect.php';

// Security Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$msg = "";

// --- 1. RENEW LOGIC ---
if (isset($_GET['renew_id'])) {
    $checkout_id = $_GET['renew_id'];
    // Extend due date by 14 days from TODAY
    $new_due_date = date('Y-m-d', strtotime('+14 days'));
    $conn->query("UPDATE checkouts SET due_date = '$new_due_date' WHERE id=$checkout_id");
    $msg = "<div class='alert success'>Book renewed! New Due Date: $new_due_date</div>";
}

// --- 2. RETURN BOOK LOGIC (WITH FINE) ---
if (isset($_GET['return_id'])) {
    $checkout_id = $_GET['return_id'];
    $book_id = $_GET['book_id'];
    $fine = $_GET['fine']; // Get calculated fine from URL
    
    // Update Checkout Record (Mark returned and save fine)
    $conn->query("UPDATE checkouts SET status='returned', return_date=NOW(), fine_amount='$fine' WHERE id=$checkout_id");
    
    // Update Book Status to Available
    $conn->query("UPDATE books SET status='available' WHERE id=$book_id");
    
    $msg = "<div class='alert success'>Book returned successfully. Fine recorded: ₹$fine</div>";
}

// --- 3. CHECKOUT LOGIC (WITH LIMITS) ---
if (isset($_POST['checkout'])) {
    $book_id = $_POST['book_id'];
    $patron_card = $_POST['patron_card'];
    
    // A. Find Student & Category
    $p_query = $conn->query("SELECT id, category, full_name FROM patrons WHERE card_number='$patron_card'");
    
    if ($p_query->num_rows > 0) {
        $patron = $p_query->fetch_assoc();
        $patron_id = $patron['id'];
        $category = $patron['category']; // UG, PG, or Staff

        // B. Determine Book Limit
        $limit = 4; // Default for PG and Staff
        if ($category == 'UG') {
            $limit = 3;
        }

        // C. Count Current Active Books
        $count_q = $conn->query("SELECT COUNT(*) as count FROM checkouts WHERE patron_id=$patron_id AND status='active'");
        $count_data = $count_q->fetch_assoc();
        $current_books = $count_data['count'];

        if ($current_books >= $limit) {
            $msg = "<div class='alert error'>Limit Reached! $category students can only keep $limit books. (Currently has $current_books)</div>";
        } else {
            // D. Check if book is available
            $b_check = $conn->query("SELECT status FROM books WHERE id=$book_id");
            $book_data = $b_check->fetch_assoc();

            if ($book_data['status'] == 'available') {
                // E. Process Checkout (14 Days)
                $due_date = date('Y-m-d', strtotime('+14 days'));
                $sql = "INSERT INTO checkouts (book_id, patron_id, due_date) VALUES ($book_id, $patron_id, '$due_date')";
                
                if ($conn->query($sql)) {
                    $conn->query("UPDATE books SET status='checked-out' WHERE id=$book_id");
                    $msg = "<div class='alert success'>Book checked out to {$patron['full_name']} ($category). Due: $due_date</div>";
                } else {
                    $msg = "<div class='alert error'>Database Error.</div>";
                }
            } else {
                $msg = "<div class='alert error'>This book is already checked out!</div>";
            }
        }
    } else {
        $msg = "<div class='alert error'>Student Card Number not found.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rook Staff | Circulation</title>
    <link rel="stylesheet" href="splash-style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ===== ADMIN SPECIFIC STYLES WITH DARK MODE SUPPORT ===== */
        :root {
            --bg-color: #f3f3f3;
            --card-bg: white;
            --text-color: #333;
            --border-color: #ddd;
            --input-bg: white;
        }

        [data-theme="dark"] {
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --text-color: #f8f9fa;
            --border-color: #333;
            --input-bg: #2c2c2c;
            --primary: #1a1a2e; /* Override generic primary for admin */
        }

        body { background-color: var(--bg-color); font-family: 'Segoe UI', sans-serif; transition: 0.3s; color: var(--text-color); margin: 0; }
        
        /* Header */
        .admin-header { background: var(--primary); padding: 1rem; color: white; display:flex; justify-content:space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .admin-header a { color: white; text-decoration: none; margin-left: 15px; font-weight: 500; }
        
        /* Layout */
        .container { display: flex; max-width: 1300px; margin: 20px auto; gap: 20px; padding: 0 15px; }
        
        /* Sidebar */
        .sidebar { width: 200px; background: var(--card-bg); padding: 15px; height: fit-content; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .sidebar a { display: block; padding: 12px; color: var(--text-color); text-decoration: none; border-bottom: 1px solid var(--border-color); transition: 0.2s; }
        .sidebar a:hover { color: #3498db; background: rgba(52, 152, 219, 0.1); padding-left: 15px; }

        /* Main Content */
        .main-content { flex: 1; background: var(--card-bg); padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        
        /* Forms */
        .checkout-form { background: rgba(52, 152, 219, 0.05); padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid var(--border-color); }
        input, select { padding: 12px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-color); border-radius: 4px; margin-right: 10px; margin-bottom: 10px; }
        
        /* Buttons */
        .btn-action { background: #3498db; color: white; border: none; padding: 12px 25px; cursor: pointer; border-radius: 4px; font-weight: bold; }
        .btn-return { background: #e74c3c; padding: 6px 12px; text-decoration: none; color: white; border-radius: 4px; font-size: 0.85rem; margin-right: 5px; }
        .btn-renew { background: #2ecc71; padding: 6px 12px; text-decoration: none; color: white; border-radius: 4px; font-size: 0.85rem; }
        
        /* Table */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: rgba(0,0,0,0.05); text-align: left; padding: 12px; border-bottom: 2px solid var(--border-color); color: var(--text-color); }
        td { padding: 12px; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
        
        /* Alerts */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; border-left: 5px solid; }
        .success { background: rgba(46, 204, 113, 0.2); border-color: #2ecc71; color: #27ae60; }
        .error { background: rgba(231, 76, 60, 0.2); border-color: #e74c3c; color: #c0392b; }
        [data-theme="dark"] .success { color: #2ecc71; }
        [data-theme="dark"] .error { color: #e74c3c; }

        .theme-toggle { background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem; }
        
        /* Fine Badge */
        .fine-badge { background: #e74c3c; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; }
        .no-fine { color: #2ecc71; font-size: 0.85rem; }
    </style>
</head>
<body>

<div class="admin-header">
    <div style="display:flex; align-items:center; gap:10px;">
        <i class="fas fa-book-reader"></i> <strong>Circulation Desk</strong>
    </div>
    <div>
        <button class="theme-toggle" id="themeBtn" style="margin-right:15px;"><i class="fas fa-moon"></i></button>
        <span style="margin-right:15px; font-size:0.9rem; opacity:0.8;">Admin: <?php echo $_SESSION['admin_name']; ?></span>
        <a href="admin_logout.php" style="background:rgba(231,76,60,0.2); padding:5px 10px; border-radius:4px;">Logout</a>
    </div>
</div>

<div class="container">
    <div class="sidebar">
        <a href="admin_entry.php"><i class="fas fa-database"></i> Manage Data</a>
        <a href="admin_circulation.php" style="color:#3498db; font-weight:bold; background: rgba(52, 152, 219, 0.1); border-left: 3px solid #3498db;"><i class="fas fa-exchange-alt"></i> Circulation</a>
    </div>

    <div class="main-content">
        <?php echo $msg; ?>

        <div class="checkout-form">
            <h3 style="margin-top:0; color: var(--secondary);"><i class="fas fa-plus-circle"></i> Issue New Book</h3>
            <p style="font-size:0.9rem; margin-bottom:15px; opacity:0.7;">
                <strong>Rules:</strong> UG = 3 Books, PG/Staff = 4 Books. Loan Period = 14 Days. Fine = ₹1/day.
            </p>
            <form method="POST" style="display:flex; flex-wrap:wrap; align-items:center;">
                <select name="book_id" required style="flex:2; min-width:200px;">
                    <option value="">-- Select Book --</option>
                    <?php
                    $books = $conn->query("SELECT id, title, call_number FROM books WHERE status='available' ORDER BY title ASC");
                    while($b = $books->fetch_assoc()){
                        echo "<option value='{$b['id']}'>{$b['title']} ({$b['call_number']})</option>";
                    }
                    ?>
                </select>
                <input type="text" name="patron_card" placeholder="Enter Admission No" required style="flex:1; min-width:150px;">
                <button type="submit" name="checkout" class="btn-action">Check Out</button>
            </form>
        </div>

        <h3 style="border-bottom: 2px solid var(--secondary); padding-bottom:10px;"><i class="fas fa-list"></i> Active Issues & Fines</h3>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Book Info</th>
                        <th>Patron</th>
                        <th>Dates</th>
                        <th>Fine Calculation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT c.id as checkout_id, c.due_date, b.title, b.id as book_id, 
                            p.full_name, p.category, p.card_number
                            FROM checkouts c
                            JOIN books b ON c.book_id = b.id
                            JOIN patrons p ON c.patron_id = p.id
                            WHERE c.status = 'active'
                            ORDER BY c.due_date ASC";
                    
                    $res = $conn->query($sql);
                    
                    if($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()){
                            // --- FINE CALCULATION ---
                            $due = new DateTime($row['due_date']);
                            $today = new DateTime();
                            $fine = 0;
                            $days_overdue = 0;
                            
                            // Check if Overdue
                            if ($today > $due) {
                                $diff = $today->diff($due);
                                $days_overdue = $diff->days;
                                $fine = $days_overdue * 1; // ₹1 per day
                            }

                            // Styling
                            $row_style = $fine > 0 ? "background: rgba(231, 76, 60, 0.05);" : "";
                            $date_color = $fine > 0 ? "color:#e74c3c; font-weight:bold;" : "";

                            echo "<tr style='$row_style'>
                                <td>
                                    <strong>{$row['title']}</strong><br>
                                    <small style='opacity:0.7'>Book ID: {$row['book_id']}</small>
                                </td>
                                <td>
                                    <strong>{$row['full_name']}</strong><br>
                                    <span style='background:#3498db; color:white; padding:2px 6px; border-radius:4px; font-size:0.7rem;'>{$row['category']}</span>
                                    <small>({$row['card_number']})</small>
                                </td>
                                <td>
                                    Due: <span style='$date_color'>{$row['due_date']}</span>
                                </td>
                                <td>";
                                
                            if($fine > 0) {
                                echo "<span class='fine-badge'>Overdue: $days_overdue Days</span><br>";
                                echo "<strong style='color:#e74c3c; font-size:1.1rem;'>Fine: ₹$fine</strong>";
                            } else {
                                echo "<span class='no-fine'><i class='fas fa-check'></i> On Time</span>";
                            }
                            
                            echo "</td>
                                <td>
                                    <a href='?renew_id={$row['checkout_id']}' class='btn-renew' title='Extend 14 days'>
                                        <i class='fas fa-sync'></i> Renew
                                    </a>
                                    
                                    <a href='?return_id={$row['checkout_id']}&book_id={$row['book_id']}&fine=$fine' 
                                       class='btn-return' 
                                       onclick='return confirm(\"Confirm Return? Fine Amount: ₹$fine\")'>
                                       <i class='fas fa-undo'></i> Return
                                    </a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; padding:20px; opacity:0.6;'>No books are currently checked out.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Theme Toggle Logic
    const themeBtn = document.getElementById('themeBtn');
    const icon = themeBtn.querySelector('i');
    
    // Check saved theme
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.body.setAttribute('data-theme', currentTheme);
    if(currentTheme === 'dark') icon.classList.replace('fa-moon', 'fa-sun');

    themeBtn.addEventListener('click', () => {
        let theme = document.body.getAttribute('data-theme');
        if (theme === 'dark') {
            document.body.setAttribute('data-theme', 'light');
            icon.classList.replace('fa-sun', 'fa-moon');
            localStorage.setItem('theme', 'light');
        } else {
            document.body.setAttribute('data-theme', 'dark');
            icon.classList.replace('fa-moon', 'fa-sun');
            localStorage.setItem('theme', 'dark');
        }
    });
</script>

</body>
</html>