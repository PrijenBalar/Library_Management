<?php
/**
 * AJAX: fetch a book by ISBN (used on Issue Book page)
 * Returns JSON.
 */
require_once '../../includes/functions.php';
header('Content-Type: application/json');

if (!is_admin_logged_in()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Not authorised.']);
    exit;
}

$isbn = trim($_GET['isbn'] ?? '');
$stmt = $con->prepare('SELECT b.id, b.BookName, b.ISBNNumber, a.AuthorName,
                       (SELECT i.StudentId FROM tblissuedbookdetails i WHERE i.BookId = b.id AND i.ReturnStatus = 0 LIMIT 1) AS issued
                       FROM tblbooks b LEFT JOIN tblauthors a ON a.id = b.AuthorId
                       WHERE b.ISBNNumber = ?');
$stmt->bind_param('s', $isbn);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['status' => 'error', 'message' => 'No book found with this ISBN.']);
} else {
    echo json_encode(['status' => 'ok', 'data' => $row]);
}
