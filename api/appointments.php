<?php
/**
 * Appointments API Endpoint
 * ASHIRU TRADITIONAL BONE SETTER
 * 
 * Supports:
 *   POST - Create new appointment consultation request
 *   GET  - List appointments (with optional sector & status filtering)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$db = Database::getConnection();

if ($method === 'POST') {
    // Read JSON payload or form-data
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data) {
        $data = $_POST;
    }

    $name = trim($data['name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $service = trim($data['service'] ?? 'Closed Fracture Reduction (Traditional Orthopedics)');
    $urgency = trim($data['urgency'] ?? 'Standard (Within 24-48 hrs)');
    $preferredDate = trim($data['date'] ?? $data['preferred_date'] ?? date('Y-m-d'));
    $consultType = trim($data['consultType'] ?? $data['consult_type'] ?? 'In-Clinic Specialist Consultation');
    $details = trim($data['details'] ?? '');

    // Validation
    if (empty($name)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Patient name is required.']);
        exit;
    }

    if (empty($phone)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Phone or WhatsApp number is required.']);
        exit;
    }

    // Determine sector based on service selection
    $sector = 'orthopedic';
    $serviceLower = strtolower($service);
    if (str_contains($serviceLower, 'alternative') || str_contains($serviceLower, 'herbal') || str_contains($serviceLower, 'poultice')) {
        $sector = 'alternative';
    } elseif (str_contains($serviceLower, 'farming') || str_contains($serviceLower, 'botanical') || str_contains($serviceLower, 'cultivation')) {
        $sector = 'farming';
    }

    // Generate unique reference code: ATBS-YYYY-XXXX
    $referenceCode = 'ATBS-' . date('Y') . '-' . mt_rand(1000, 9999);

    try {
        $stmt = $db->prepare("
            INSERT INTO appointments (
                reference_code, name, phone, sector, service, urgency, preferred_date, consult_type, details, status
            ) VALUES (
                :reference_code, :name, :phone, :sector, :service, :urgency, :preferred_date, :consult_type, :details, 'Pending'
            )
        ");

        $stmt->execute([
            ':reference_code' => $referenceCode,
            ':name'           => $name,
            ':phone'          => $phone,
            ':sector'         => $sector,
            ':service'        => $service,
            ':urgency'        => $urgency,
            ':preferred_date' => $preferredDate,
            ':consult_type'   => $consultType,
            ':details'        => $details
        ]);

        $insertedId = $db->lastInsertId();

        // Format WhatsApp dispatch message text
        $clinicWhatsAppNumber = '2348137088984';
        $whatsAppMessage = "*Appointment Booking Confirmed - ASHIRU TRADITIONAL BONE SETTER*\n"
                         . "Ref: {$referenceCode}\n"
                         . "Patient: {$name}\n"
                         . "Phone: {$phone}\n"
                         . "Sector: " . ucfirst($sector) . "\n"
                         . "Service: {$service}\n"
                         . "Urgency: {$urgency}\n"
                         . "Date: {$preferredDate}\n"
                         . "Mode: {$consultType}\n"
                         . "Details: " . ($details ?: 'None provided');

        $whatsAppUrl = "https://wa.me/{$clinicWhatsAppNumber}?text=" . urlencode($whatsAppMessage);

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Appointment request submitted successfully!',
            'reference_code' => $referenceCode,
            'id' => $insertedId,
            'whatsapp_url' => $whatsAppUrl,
            'appointment' => [
                'reference_code' => $referenceCode,
                'name'           => $name,
                'phone'          => $phone,
                'sector'         => $sector,
                'service'        => $service,
                'urgency'        => $urgency,
                'preferred_date' => $preferredDate,
                'consult_type'   => $consultType,
                'status'         => 'Pending',
                'created_at'     => date('Y-m-d H:i:s')
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

if ($method === 'GET') {
    $sector = $_GET['sector'] ?? null;
    $status = $_GET['status'] ?? null;
    $limit  = min(100, max(1, (int)($_GET['limit'] ?? 50)));

    $query = "SELECT * FROM appointments WHERE 1=1";
    $params = [];

    if ($sector && in_array($sector, ['orthopedic', 'alternative', 'farming'])) {
        $query .= " AND sector = :sector";
        $params[':sector'] = $sector;
    }

    if ($status) {
        $query .= " AND status = :status";
        $params[':status'] = $status;
    }

    $query .= " ORDER BY id DESC LIMIT :limit";

    try {
        $stmt = $db->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $appointments = $stmt->fetchAll();

        // Get total summary count
        $countStmt = $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN sector = 'orthopedic' THEN 1 ELSE 0 END) as orthopedic_count,
                SUM(CASE WHEN sector = 'alternative' THEN 1 ELSE 0 END) as alternative_count,
                SUM(CASE WHEN sector = 'farming' THEN 1 ELSE 0 END) as farming_count
            FROM appointments
        ");
        $stats = $countStmt->fetch();

        echo json_encode([
            'success' => true,
            'count' => count($appointments),
            'stats' => $stats,
            'data' => $appointments
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Query error: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed. Use GET or POST.']);
