<?php
/**
 * AJAX: shopping cart actions (student side)
 * POST action = add | update | remove, book_id, qty
 * Returns JSON with the new cart count and totals.
 */
require_once '../includes/functions.php';
header('Content-Type: application/json');

if (!is_student_logged_in()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Please login first.']);
    exit;
}

$sid    = $_SESSION['student_id'];
$action = $_POST['action'] ?? '';
$bookId = (int)($_POST['book_id'] ?? 0);
$qty    = (int)($_POST['qty'] ?? 1);

/** Send the JSON answer including fresh cart totals */
function respond($con, $sid, $status, $message, $bookId = 0)
{
    $stmt = $con->prepare('SELECT c.BookId, c.Quantity, b.BookPrice FROM tblcart c JOIN tblbooks b ON b.id = c.BookId WHERE c.StudentId = ?');
    $stmt->bind_param('s', $sid);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $count = 0; $total = 0; $line = 0;
    foreach ($rows as $r) {
        $count += (int)$r['Quantity'];
        $total += $r['Quantity'] * $r['BookPrice'];
        if ((int)$r['BookId'] === $bookId) {
            $line = $r['Quantity'] * $r['BookPrice'];
        }
    }
    echo json_encode([
        'status'     => $status,
        'message'    => $message,
        'count'      => $count,
        'line_total' => number_format($line, 2),
        'cart_total' => number_format($total, 2),
    ]);
    exit;
}

// Load the book
$stmt = $con->prepare('SELECT id, BookName, SellStock FROM tblbooks WHERE id = ?');
$stmt->bind_param('i', $bookId);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) {
    respond($con, $sid, 'error', 'Book not found.');
}

// Quantity already in the cart
$stmt = $con->prepare('SELECT Quantity FROM tblcart WHERE StudentId = ? AND BookId = ?');
$stmt->bind_param('si', $sid, $bookId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$inCart = $row ? (int)$row['Quantity'] : 0;
$stock  = (int)$book['SellStock'];
$limit  = min($stock, MAX_QTY_PER_BOOK);

switch ($action) {
    case 'add':
        if ($qty < 1) {
            respond($con, $sid, 'error', 'Quantity must be at least 1.', $bookId);
        }
        if ($stock <= 0) {
            respond($con, $sid, 'error', '"' . $book['BookName'] . '" is out of stock.', $bookId);
        }
        $newQty = $inCart + $qty;
        if ($newQty > $limit) {
            $msg = $stock < MAX_QTY_PER_BOOK
                ? 'Only ' . $stock . ' copies available.'
                : 'You can buy at most ' . MAX_QTY_PER_BOOK . ' copies of one book.';
            if ($inCart) { $msg .= ' You already have ' . $inCart . ' in your cart.'; }
            respond($con, $sid, 'error', $msg, $bookId);
        }
        $stmt = $con->prepare('INSERT INTO tblcart (StudentId, BookId, Quantity) VALUES (?, ?, ?)
                               ON DUPLICATE KEY UPDATE Quantity = VALUES(Quantity)');
        $stmt->bind_param('sii', $sid, $bookId, $newQty);
        $stmt->execute();
        $stmt->close();
        respond($con, $sid, 'ok', '"' . $book['BookName'] . '" added to cart (' . $newQty . ' in cart).', $bookId);
        break;

    case 'update':
        if (!$inCart) {
            respond($con, $sid, 'error', 'This book is not in your cart.', $bookId);
        }
        if ($qty < 1 || $qty > $limit) {
            respond($con, $sid, 'error', 'Please choose a quantity between 1 and ' . max($limit, 1) . '.', $bookId);
        }
        $stmt = $con->prepare('UPDATE tblcart SET Quantity = ? WHERE StudentId = ? AND BookId = ?');
        $stmt->bind_param('isi', $qty, $sid, $bookId);
        $stmt->execute();
        $stmt->close();
        respond($con, $sid, 'ok', 'Quantity updated.', $bookId);
        break;

    case 'remove':
        $stmt = $con->prepare('DELETE FROM tblcart WHERE StudentId = ? AND BookId = ?');
        $stmt->bind_param('si', $sid, $bookId);
        $stmt->execute();
        $stmt->close();
        respond($con, $sid, 'ok', '"' . $book['BookName'] . '" removed from cart.', $bookId);
        break;

    default:
        respond($con, $sid, 'error', 'Invalid action.');
}
