<?php
/**
 * Update Status API (Appointments, Emergency Dispatches & Contact Inquiries)
 * ASHIRU TRADITIONAL BONE SETTER
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true) ?: $_POST;

$id = (int)($data['id'] ?? 0);
$type = trim($data['type'] ?? 'appointment');
$status = trim($data['status'] ?? '');

$allowedAppointmentStatuses = ['Pending', 'Confirmed', 'In Treatment', 'Completed', 'Cancelled'];
$allowedEmergencyStatuses = ['Dispatched', 'En Route', 'Received in Clinic', 'Treated', 'Resolved'];

if (!$id) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid or missing record ID.']);
    exit;
}

try {
    $db = Database::getConnection();

    if ($type === 'emergency') {
        if (!in_array($status, $allowedEmergencyStatuses)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid emergency status value.']);
            exit;
        }
        $stmt = $db->prepare("UPDATE emergency_dispatches SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);
        echo json_encode([
            'success' => true,
            'message' => "Emergency dispatch #{$id} status updated to {$status}."
        ]);
    } else {
        if (!in_array($status, $allowedAppointmentStatuses)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid appointment status value.']);
            exit;
        }
        $stmt = $db->prepare("UPDATE appointments SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);
        echo json_encode([
            'success' => true,
            'message' => "Appointment #{$id} status updated to {$status}."
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
