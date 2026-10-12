<?php
/**
 * Analytics & Statistics API
 * ASHIRU TRADITIONAL BONE SETTER
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();

try {
    // Total counts by sector
    $sectorStats = $db->query("
        SELECT 
            sector,
            COUNT(*) as count
        FROM appointments
        GROUP BY sector
    ")->fetchAll();

    // Total counts by status
    $statusStats = $db->query("
        SELECT 
            status,
            COUNT(*) as count
        FROM appointments
        GROUP BY status
    ")->fetchAll();

    // Urgency breakdown
    $urgencyStats = $db->query("
        SELECT 
            urgency,
            COUNT(*) as count
        FROM appointments
        GROUP BY urgency
    ")->fetchAll();

    // Total counts
    $totals = $db->query("
        SELECT 
            (SELECT COUNT(*) FROM appointments) as total_appointments,
            (SELECT COUNT(*) FROM emergency_dispatches) as total_emergencies,
            (SELECT COUNT(*) FROM contact_inquiries) as total_inquiries
    ")->fetch();

    echo json_encode([
        'success' => true,
        'organization' => 'ASHIRU TRADITIONAL BONE SETTER',
        'sectors' => [
            'Alternative Medical Services',
            'Traditional Orthopedic Services',
            'General Farming'
        ],
        'totals' => $totals,
        'by_sector' => $sectorStats,
        'by_status' => $statusStats,
        'by_urgency' => $urgencyStats
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
