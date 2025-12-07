<?php
// api_books.php
include 'db_connect.php';

// 1. Get Parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; // Load 6 books at a time
$offset = ($page - 1) * $limit;

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'title';
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$language = isset($_GET['language']) ? $_GET['language'] : 'All';
$status = isset($_GET['status']) ? $_GET['status'] : 'All';

// 2. Build Query
$sql = "SELECT * FROM books WHERE 1=1";
$types = "";
$params = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR author LIKE ?)";
    $searchTerm = "%$search%";
    $types .= "ss";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($language !== 'All') {
    $sql .= " AND language = ?";
    $types .= "s";
    $params[] = $language;
}

if ($status !== 'All') {
    $sql .= " AND status = ?";
    $types .= "s";
    $params[] = $status;
}

// Allowed sort columns
$allowed_sorts = ['title', 'author'];
if (!in_array($sort, $allowed_sorts)) $sort = 'title';

$sql .= " ORDER BY $sort $order LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = $limit;
$params[] = $offset;

// 3. Execute
$stmt = $conn->prepare($sql);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

// 4. Return JSON
header('Content-Type: application/json');
echo json_encode($books);
?>