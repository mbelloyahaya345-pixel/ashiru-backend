<?php
/**
 * Emergency Bone Trauma Dispatch API
 * ASHIRU TRADITIONAL BONE SETTER
 * Official Email: mbelloyahay345@gmail.com
 * Emergency Hotline / WhatsApp: +234 813 708 8984
 * Location Magashi No. 168, Kano, Nigeria 🇳🇬
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$db = Database::getConnection();

if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data) {
        $data = $_POST;
    }

    $callerName = trim($data['caller_name'] ?? $data['name'] ?? 'Emergency Caller');
    $callerPhone = trim($data['caller_phone'] ?? $data['phone'] ?? '');
    $injuryType = trim($data['injury_type'] ?? 'Acute Bone Fracture / Joint Dislocation');
    $location = trim($data['location'] ?? 'Location Magashi No. 168, Kano, Nigeria');
    $notes = trim($data['notes'] ?? '');

    if (empty($callerPhone)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Emergency contact phone number is required.']);
        exit;
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO emergency_dispatches (caller_name, caller_phone, injury_type, location, notes, status)
            VALUES (:caller_name, :caller_phone, :injury_type, :location, :notes, 'Dispatched')
        ");
        $stmt->execute([
            ':caller_name'  => $callerName,
            ':caller_phone' => $callerPhone,
            ':injury_type'  => $injuryType,
            ':location'     => $location,
            ':notes'        => $notes
        ]);

        $dispatchId = $db->lastInsertId();

        // Direct emergency WhatsApp dispatch text
        $waMessage = "🚨 *URGENT BONE TRAUMA DISPATCH - ASHIRU TRADITIONAL BONE SETTER*\n"
                   . "Dispatch ID: #{$dispatchId}\n"
                   . "Caller: {$callerName}\n"
                   . "Phone: {$callerPhone}\n"
                   . "Injury: {$injuryType}\n"
                   . "Location: {$location}\n"
                   . "Notes: " . ($notes ?: 'Patient en route to emergency triage');
        $whatsAppUrl = "https://wa.me/2348137088984?text=" . urlencode($waMessage);

        echo json_encode([
            'success' => true,
            'message' => 'Emergency dispatch record logged immediately.',
            'dispatch_id' => $dispatchId,
            'hotline' => '+234 813 708 8984',
            'facility_address' => 'Location Magashi No. 168, Kano, Nigeria 🇳🇬',
            'official_email' => 'mbelloyahay345@gmail.com',
            'whatsapp_url' => $whatsAppUrl
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

if ($method === 'GET') {
    try {
        $stmt = $db->query("SELECT * FROM emergency_dispatches ORDER BY id DESC LIMIT 100");
        $dispatches = $stmt->fetchAll();
        echo json_encode(['success' => true, 'count' => count($dispatches), 'data' => $dispatches]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed. Use GET or POST.']);
