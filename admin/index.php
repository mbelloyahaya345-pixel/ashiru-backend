<?php
/**
 * Clinical Admin & Management Dashboard
 * ASHIRU TRADITIONAL BONE SETTER
 * Alternative Medical Services • Traditional Orthopedic Services • General Farming
 * 
 * Official Email: mbelloyahay345@gmail.com
 * Emergency Hotline / WhatsApp: +234 813 708 8984
 * Location Magashi No. 168, Kano, Nigeria 🇳🇬
 */

require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();

// Active tab: 'appointments', 'emergencies', 'inquiries'
$activeTab    = $_GET['tab'] ?? 'appointments';
$filterSector = $_GET['sector'] ?? 'all';
$filterStatus = $_GET['status'] ?? 'all';
$searchQuery  = trim($_GET['q'] ?? '');

// --- 1. APPOINTMENTS QUERY ---
$sqlApt = "SELECT * FROM appointments WHERE 1=1";
$paramsApt = [];

if ($filterSector !== 'all') {
    $sqlApt .= " AND sector = :sector";
    $paramsApt[':sector'] = $filterSector;
}
if ($filterStatus !== 'all') {
    $sqlApt .= " AND status = :status";
    $paramsApt[':status'] = $filterStatus;
}
if (!empty($searchQuery)) {
    $sqlApt .= " AND (name LIKE :q OR phone LIKE :q OR reference_code LIKE :q OR details LIKE :q)";
    $paramsApt[':q'] = "%{$searchQuery}%";
}
$sqlApt .= " ORDER BY id DESC LIMIT 100";
$stmtApt = $db->prepare($sqlApt);
$stmtApt->execute($paramsApt);
$appointments = $stmtApt->fetchAll();

// --- 2. EMERGENCIES QUERY ---
$sqlEmg = "SELECT * FROM emergency_dispatches WHERE 1=1";
$paramsEmg = [];
if (!empty($searchQuery) && $activeTab === 'emergencies') {
    $sqlEmg .= " AND (caller_name LIKE :q OR caller_phone LIKE :q OR injury_type LIKE :q OR location LIKE :q)";
    $paramsEmg[':q'] = "%{$searchQuery}%";
}
$sqlEmg .= " ORDER BY id DESC LIMIT 100";
$stmtEmg = $db->prepare($sqlEmg);
$stmtEmg->execute($paramsEmg);
$emergencies = $stmtEmg->fetchAll();

// --- 3. CONTACT INQUIRIES QUERY ---
$sqlInq = "SELECT * FROM contact_inquiries WHERE 1=1";
$paramsInq = [];
if (!empty($searchQuery) && $activeTab === 'inquiries') {
    $sqlInq .= " AND (name LIKE :q OR email LIKE :q OR phone LIKE :q OR subject LIKE :q OR message LIKE :q)";
    $paramsInq[':q'] = "%{$searchQuery}%";
}
$sqlInq .= " ORDER BY id DESC LIMIT 100";
$stmtInq = $db->prepare($sqlInq);
$stmtInq->execute($paramsInq);
$inquiries = $stmtInq->fetchAll();

// --- STATISTICS ---
$stats = $db->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN sector = 'orthopedic' THEN 1 ELSE 0 END) as orthopedic,
        SUM(CASE WHEN sector = 'alternative' THEN 1 ELSE 0 END) as alternative,
        SUM(CASE WHEN sector = 'farming' THEN 1 ELSE 0 END) as farming
    FROM appointments
")->fetch();

$emergenciesCount = (int)$db->query("SELECT COUNT(*) FROM emergency_dispatches")->fetchColumn();
$inquiriesCount   = (int)$db->query("SELECT COUNT(*) FROM contact_inquiries")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | ASHIRU TRADITIONAL BONE SETTER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-page: #f8fafc;
            --surface-white: #ffffff;
            --primary-emerald: #0a2e22;
            --emerald-light: #059669;
            --gold: #d4a340;
            --gold-dark: #855806;
            --gold-light: #fef3c7;
            --border: rgba(212, 163, 64, 0.28);
            --border-subtle: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #475569;
            --danger: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            min-height: 100vh;
            padding-bottom: 60px;
        }

        /* Header Bar */
        .header-bar {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: 18px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
        }

        .brand-block {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-avatar {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: var(--primary-emerald);
            color: var(--gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 800;
            border: 2px solid var(--gold);
        }

        .brand-title {
            font-family: 'Cinzel', serif;
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary-emerald);
            letter-spacing: 1px;
        }

        .brand-subtitle {
            font-size: 0.75rem;
            color: var(--gold-dark);
            font-weight: 600;
        }

        .container {
            max-width: 1420px;
            margin: 30px auto;
            padding: 0 24px;
        }

        /* Top Announcement Strip */
        .top-admin-strip {
            background: #fffbeb;
            border: 1px solid rgba(212, 163, 64, 0.35);
            border-radius: 10px;
            padding: 10px 18px;
            font-size: 0.85rem;
            color: #78350f;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        /* Metrics Row */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .metric-card {
            background: var(--surface-white);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
            position: relative;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
            border-color: rgba(212, 163, 64, 0.5);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), transparent);
        }

        .metric-label {
            font-size: 0.78rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
        }

        .metric-val {
            font-size: 2.1rem;
            font-weight: 800;
            color: var(--primary-emerald);
            margin-top: 6px;
        }

        /* Tab Switcher */
        .tabs-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border-subtle);
            padding-bottom: 12px;
        }

        .tab-btn {
            background: var(--surface-white);
            border: 1px solid var(--border-subtle);
            color: var(--text-muted);
            padding: 10px 22px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            color: var(--gold-dark);
            border-color: var(--gold);
        }

        .tab-btn.active {
            background: var(--primary-emerald);
            color: #ffffff;
            border-color: var(--primary-emerald);
            box-shadow: 0 4px 12px rgba(10, 46, 34, 0.25);
        }

        .tab-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 7px;
            border-radius: 9999px;
            font-size: 0.75rem;
        }

        .tab-btn.active .tab-count {
            background: var(--gold);
            color: #000;
        }

        /* Filter Panel */
        .filter-panel {
            background: var(--surface-white);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px 24px;
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: #f1f5f9;
            border: 1px solid var(--border-subtle);
            color: var(--text-muted);
            padding: 7px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .filter-btn:hover, .filter-btn.active {
            background: var(--gold);
            color: #0b1a13;
            border-color: var(--gold);
        }

        .search-input {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: var(--text-dark);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.88rem;
            min-width: 260px;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-input:focus {
            border-color: var(--gold-dark);
        }

        /* Table Card */
        .table-card {
            background: var(--surface-white);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }

        th {
            background: #f8fafc;
            color: var(--primary-emerald);
            padding: 14px 18px;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 2px solid var(--border);
        }

        td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--border-subtle);
            vertical-align: middle;
            color: var(--text-dark);
        }

        tr:hover {
            background: rgba(212, 163, 64, 0.04);
        }

        /* Sector Badges */
        .badge-sector {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .sector-orthopedic {
            background: rgba(212, 163, 64, 0.15);
            color: #855806;
            border: 1px solid rgba(212, 163, 64, 0.4);
        }

        .sector-alternative {
            background: rgba(16, 185, 129, 0.15);
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, 0.4);
        }

        .sector-farming {
            background: rgba(245, 158, 11, 0.15);
            color: #92400e;
            border: 1px solid rgba(245, 158, 11, 0.4);
        }

        /* Status Select */
        .status-select {
            background: #ffffff;
            color: var(--text-dark);
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            outline: none;
            cursor: pointer;
        }

        .status-select:focus {
            border-color: var(--gold-dark);
        }

        /* Action Buttons */
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-wa {
            background: #25d366;
            color: #ffffff;
        }

        .btn-wa:hover {
            background: #1eb956;
        }

        .btn-call {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .btn-call:hover {
            background: #fecaca;
        }

        .btn-email {
            background: #e0f2fe;
            color: #075985;
            border: 1px solid #bae6fd;
        }

        .toast-notify {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #065f46;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <header class="header-bar">
        <div class="brand-block">
            <div class="brand-avatar">🦴</div>
            <div>
                <div class="brand-title">ASHIRU TRADITIONAL BONE SETTER</div>
                <div class="brand-subtitle">Clinical Triage &amp; Management Dashboard &bull; Kano, Nigeria 🇳🇬</div>
            </div>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="https://ashiru-tbs.surge.sh" target="_blank" class="filter-btn" style="background: var(--gold); color: #0b1a13; font-weight: 700;">
                🌐 Open Live Website
            </a>
        </div>
    </header>

    <main class="container">
        <!-- Official Contact Banner -->
        <div class="top-admin-strip">
            <div>
                📞 <strong>Emergency Hotline:</strong> +234 813 708 8984 &nbsp;|&nbsp;
                ✉️ <strong>Official Email:</strong> mbelloyahay345@gmail.com
            </div>
            <div>
                📍 <strong>Facility:</strong> Location Magashi No. 168, Kano, Nigeria
            </div>
        </div>

        <!-- Metrics Row -->
        <section class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Total Consultations</div>
                <div class="metric-val"><?= (int)$stats['total'] ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Pending Triage</div>
                <div class="metric-val" style="color: #d97706;"><?= (int)$stats['pending'] ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Traditional Orthopedics</div>
                <div class="metric-val" style="color: var(--gold-dark);"><?= (int)$stats['orthopedic'] ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Alternative Medicine</div>
                <div class="metric-val" style="color: var(--emerald-light);"><?= (int)$stats['alternative'] ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">General Farming</div>
                <div class="metric-val" style="color: #b45309;"><?= (int)$stats['farming'] ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Emergency Trauma Calls</div>
                <div class="metric-val" style="color: var(--danger);"><?= (int)$emergenciesCount ?></div>
            </div>
        </section>

        <!-- Navigation Tabs -->
        <nav class="tabs-nav">
            <a href="?tab=appointments" class="tab-btn <?= $activeTab === 'appointments' ? 'active' : '' ?>">
                📅 Appointments &amp; Consultations
                <span class="tab-count"><?= (int)$stats['total'] ?></span>
            </a>
            <a href="?tab=emergencies" class="tab-btn <?= $activeTab === 'emergencies' ? 'active' : '' ?>">
                🚨 Emergency Trauma Logs
                <span class="tab-count"><?= $emergenciesCount ?></span>
            </a>
            <a href="?tab=inquiries" class="tab-btn <?= $activeTab === 'inquiries' ? 'active' : '' ?>">
                ✉️ Contact &amp; Farm Inquiries
                <span class="tab-count"><?= $inquiriesCount ?></span>
            </a>
        </nav>

        <?php if ($activeTab === 'appointments'): ?>
            <!-- APPOINTMENTS TAB -->
            <section class="filter-panel">
                <div class="filter-group">
                    <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700;">SECTOR:</span>
                    <a href="?tab=appointments&sector=all&status=<?= htmlspecialchars($filterStatus) ?>" class="filter-btn <?= $filterSector === 'all' ? 'active' : '' ?>">All Sectors</a>
                    <a href="?tab=appointments&sector=orthopedic&status=<?= htmlspecialchars($filterStatus) ?>" class="filter-btn <?= $filterSector === 'orthopedic' ? 'active' : '' ?>">Orthopedics</a>
                    <a href="?tab=appointments&sector=alternative&status=<?= htmlspecialchars($filterStatus) ?>" class="filter-btn <?= $filterSector === 'alternative' ? 'active' : '' ?>">Alternative Medicine</a>
                    <a href="?tab=appointments&sector=farming&status=<?= htmlspecialchars($filterStatus) ?>" class="filter-btn <?= $filterSector === 'farming' ? 'active' : '' ?>">General Farming</a>
                </div>

                <form method="GET" class="filter-group">
                    <input type="hidden" name="tab" value="appointments">
                    <input type="hidden" name="sector" value="<?= htmlspecialchars($filterSector) ?>">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>">
                    <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search patient, phone, ref..." class="search-input">
                    <button type="submit" class="filter-btn" style="cursor: pointer; background: var(--primary-emerald); color: #fff;">Search</button>
                </form>
            </section>

            <section class="table-card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Ref Code</th>
                                <th>Patient / Client</th>
                                <th>Sector</th>
                                <th>Service Category</th>
                                <th>Date &amp; Urgency</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($appointments)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                        No appointment records found matching the current filters.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($appointments as $apt): ?>
                                    <tr>
                                        <td>
                                            <strong style="color: var(--gold-dark); font-family: monospace; font-size: 0.95rem;">
                                                <?= htmlspecialchars($apt['reference_code']) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: var(--text-dark);"><?= htmlspecialchars($apt['name']) ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($apt['phone']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge-sector sector-<?= htmlspecialchars($apt['sector']) ?>">
                                                <?= htmlspecialchars($apt['sector']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-dark);"><?= htmlspecialchars($apt['service']) ?></div>
                                            <div style="font-size: 0.78rem; color: var(--text-muted);">
                                                <?= htmlspecialchars($apt['consult_type']) ?>
                                            </div>
                                            <?php if (!empty($apt['details'])): ?>
                                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px; max-width: 300px;">
                                                    📝 <?= htmlspecialchars($apt['details']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div>📅 <?= htmlspecialchars($apt['preferred_date'] ?: 'Not set') ?></div>
                                            <div style="font-size: 0.75rem; color: #b45309; font-weight: 600;"><?= htmlspecialchars($apt['urgency']) ?></div>
                                        </td>
                                        <td>
                                            <select class="status-select" onchange="updateRecordStatus('appointment', <?= (int)$apt['id'] ?>, this.value)">
                                                <?php foreach (['Pending', 'Confirmed', 'In Treatment', 'Completed', 'Cancelled'] as $st): ?>
                                                    <option value="<?= $st ?>" <?= $apt['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 8px;">
                                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $apt['phone']) ?>?text=<?= urlencode('Hello ' . $apt['name'] . ', this is the clinic desk at ASHIRU TRADITIONAL BONE SETTER regarding your consultation (' . $apt['reference_code'] . ').') ?>" 
                                                   target="_blank" class="btn-action btn-wa" title="Open WhatsApp">
                                                    💬 WhatsApp
                                                </a>
                                                <a href="tel:<?= htmlspecialchars($apt['phone']) ?>" class="btn-action btn-call" title="Call">
                                                    📞 Call
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <?php elseif ($activeTab === 'emergencies'): ?>
            <!-- EMERGENCIES TAB -->
            <section class="filter-panel">
                <div>
                    <h3 style="font-size: 1rem; color: var(--primary-emerald);">🚨 24/7 Bone Trauma Emergency Logs</h3>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">Immediate patient triage &amp; ambulance/stretcher reception desk</p>
                </div>
                <form method="GET" class="filter-group">
                    <input type="hidden" name="tab" value="emergencies">
                    <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search caller, phone, injury..." class="search-input">
                    <button type="submit" class="filter-btn" style="cursor: pointer; background: var(--primary-emerald); color: #fff;">Search</button>
                </form>
            </section>

            <section class="table-card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Dispatch ID</th>
                                <th>Caller Name</th>
                                <th>Phone Number</th>
                                <th>Injury Trauma Type</th>
                                <th>Location / Origin</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($emergencies)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                        No emergency records found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($emergencies as $emg): ?>
                                    <tr>
                                        <td>
                                            <strong style="color: var(--danger); font-family: monospace;">#<?= (int)$emg['id'] ?></strong>
                                            <div style="font-size: 0.72rem; color: var(--text-muted);"><?= htmlspecialchars($emg['created_at']) ?></div>
                                        </td>
                                        <td><strong><?= htmlspecialchars($emg['caller_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($emg['caller_phone']) ?></td>
                                        <td>
                                            <div style="color: #b91c1c; font-weight: 700;"><?= htmlspecialchars($emg['injury_type']) ?></div>
                                            <?php if (!empty($emg['notes'])): ?>
                                                <div style="font-size: 0.75rem; color: var(--text-muted);">Note: <?= htmlspecialchars($emg['notes']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>📍 <?= htmlspecialchars($emg['location']) ?></td>
                                        <td>
                                            <select class="status-select" onchange="updateRecordStatus('emergency', <?= (int)$emg['id'] ?>, this.value)">
                                                <?php foreach (['Dispatched', 'En Route', 'Received in Clinic', 'Treated', 'Resolved'] as $est): ?>
                                                    <option value="<?= $est ?>" <?= ($emg['status'] ?? '') === $est ? 'selected' : '' ?>><?= $est ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 8px;">
                                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $emg['caller_phone']) ?>?text=<?= urlencode('Urgent: Dr. Ashiru Salisu Trauma Triage Desk responding to dispatch #' . $emg['id'] . '.') ?>" 
                                                   target="_blank" class="btn-action btn-wa">
                                                    💬 WhatsApp
                                                </a>
                                                <a href="tel:<?= htmlspecialchars($emg['caller_phone']) ?>" class="btn-action btn-call">
                                                    📞 Call
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <?php elseif ($activeTab === 'inquiries'): ?>
            <!-- INQUIRIES TAB -->
            <section class="filter-panel">
                <div>
                    <h3 style="font-size: 1rem; color: var(--primary-emerald);">✉️ General &amp; General Farming Inquiries</h3>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">Incoming requests across Alternative Medicine, Orthopedics &amp; Sustainable Farming</p>
                </div>
                <form method="GET" class="filter-group">
                    <input type="hidden" name="tab" value="inquiries">
                    <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search name, email, subject..." class="search-input">
                    <button type="submit" class="filter-btn" style="cursor: pointer; background: var(--primary-emerald); color: #fff;">Search</button>
                </form>
            </section>

            <section class="table-card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Sender</th>
                                <th>Sector</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($inquiries)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                        No inquiry records found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($inquiries as $inq): ?>
                                    <tr>
                                        <td>#<?= (int)$inq['id'] ?></td>
                                        <td>
                                            <div style="font-weight: 700;"><?= htmlspecialchars($inq['name']) ?></div>
                                            <?php if (!empty($inq['email'])): ?>
                                                <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($inq['email']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($inq['phone'])): ?>
                                                <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($inq['phone']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge-sector sector-<?= htmlspecialchars($inq['sector']) ?>">
                                                <?= htmlspecialchars($inq['sector']) ?>
                                            </span>
                                        </td>
                                        <td><strong><?= htmlspecialchars($inq['subject']) ?></strong></td>
                                        <td>
                                            <div style="max-width: 360px; font-size: 0.85rem; line-height: 1.4; color: var(--text-dark);">
                                                <?= nl2br(htmlspecialchars($inq['message'])) ?>
                                            </div>
                                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px;">
                                                <?= htmlspecialchars($inq['created_at']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 8px;">
                                                <?php if (!empty($inq['phone'])): ?>
                                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $inq['phone']) ?>" target="_blank" class="btn-action btn-wa">
                                                        💬 WhatsApp
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($inq['email'])): ?>
                                                    <a href="mailto:<?= htmlspecialchars($inq['email']) ?>" class="btn-action btn-email">
                                                        ✉️ Email
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <div id="toast" class="toast-notify">Status updated successfully!</div>

    <script>
        function updateRecordStatus(type, id, newStatus) {
            fetch('update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: type, id: id, status: newStatus })
            })
            .then(res => res.json())
            .then(data => {
                const toast = document.getElementById('toast');
                toast.textContent = data.message || 'Status updated successfully!';
                toast.style.display = 'block';
                setTimeout(() => { toast.style.display = 'none'; }, 3000);
            })
            .catch(err => {
                alert('Failed to update status. Please check your PHP server connection.');
            });
        }
    </script>
</body>
</html>
