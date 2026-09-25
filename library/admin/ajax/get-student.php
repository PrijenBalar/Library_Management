<?php
/**
 * AJAX: fetch a student by Student ID (used on Issue Book page)
 * Returns JSON.
 */
require_once '../../includes/functions.php';
header('Content-Type: application/json');

if (!is_admin_logged_in()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Not authorised.']);
    exit;
}

$sid = strtoupper(trim($_GET['studentid'] ?? ''));
$stmt = $con->prepare('SELECT StudentId, FullName, EmailId, MobileNumber, Status FROM tblstudents WHERE StudentId = ?');
$stmt->bind_param('s', $sid);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['status' => 'error', 'message' => 'No student found with this Student ID.']);
} elseif ((int)$row['Status'] !== 1) {
    echo json_encode(['status' => 'error', 'message' => 'Student ' . $row['FullName'] . ' is blocked.']);
} else {
    echo json_encode(['status' => 'ok', 'data' => $row]);
}
