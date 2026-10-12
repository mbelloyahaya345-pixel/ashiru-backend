<?php
/**
 * Contact & Inquiry API
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

    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $sector = trim($data['sector'] ?? 'orthopedic');
    $subject = trim($data['subject'] ?? 'General Consultation / Inquiry');
    $message = trim($data['message'] ?? '');

    if (empty($name) || empty($message)) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error' => 'Name and message are required fields.'
        ]);
        exit;
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO contact_inquiries (name, email, phone, sector, subject, message)
            VALUES (:name, :email, :phone, :sector, :subject, :message)
        ");
        $stmt->execute([
            ':name'    => $name,
            ':email'   => $email,
            ':phone'   => $phone,
            ':sector'  => $sector,
            ':subject' => $subject,
            ':message' => $message
        ]);

        $insertedId = $db->lastInsertId();

        // Format WhatsApp dispatch message text for Dr. Ashiru Salisu (+234 813 708 8984)
        $waMessage = "*New Inquiry - ASHIRU TRADITIONAL BONE SETTER*\n"
                   . "Inquiry ID: #{$insertedId}\n"
                   . "Name: {$name}\n"
                   . "Phone: " . ($phone ?: 'Not provided') . "\n"
                   . "Email: " . ($email ?: 'Not provided') . "\n"
                   . "Sector: " . ucfirst($sector) . "\n"
                   . "Subject: {$subject}\n"
                   . "Message: {$message}";
        $whatsAppUrl = "https://wa.me/2348137088984?text=" . urlencode($waMessage);

        echo json_encode([
            'success' => true,
            'message' => 'Thank you! Your inquiry has been received. Our clinical assistant will contact you shortly.',
            'id' => $insertedId,
            'official_email' => 'mbelloyahay345@gmail.com',
            'hotline' => '+234 813 708 8984',
            'facility_address' => 'Location Magashi No. 168, Kano, Nigeria 🇳🇬',
            'whatsapp_url' => $whatsAppUrl
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

if ($method === 'GET') {
    try {
        $stmt = $db->query("SELECT * FROM contact_inquiries ORDER BY id DESC LIMIT 100");
        $inquiries = $stmt->fetchAll();
        echo json_encode([
            'success' => true,
            'count' => count($inquiries),
            'data' => $inquiries
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed. Use GET or POST.']);
