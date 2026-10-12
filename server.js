/**
 * ASHIRU TRADITIONAL BONE SETTER
 * Node.js / Express Backend Server
 * 
 * Clinical Practice Sectors:
 *   1. Alternative Medical Services
 *   2. Traditional Orthopedic Services
 *   3. General Farming
 * 
 * Official Contact Details:
 *   - Practice: ASHIRU TRADITIONAL BONE SETTER
 *   - Lead Specialist: Dr. Ashiru Salisu
 *   - Official Email: mbelloyahay345@gmail.com
 *   - Emergency Hotline / WhatsApp: +234 813 708 8984
 *   - Facility: Location Magashi No. 168, Kano, Nigeria 🇳🇬
 *   - Live Website: https://ashiru-tbs.surge.sh
 */

import express from 'express';
import cors from 'cors';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import db from './database.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 8000;

// Enable CORS for all local dev servers and live Surge domain
app.use(cors({
  origin: '*',
  methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
}));

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static admin dashboard from /public/admin
const adminDir = path.join(__dirname, 'public', 'admin');
app.use('/admin', express.static(adminDir));

// Route aliases for backward compatibility with PHP URLs
app.get(['/admin', '/admin/index.php', '/admin/'], (req, res) => {
  res.sendFile(path.join(adminDir, 'index.html'));
});

// Root API Health Check & Directory
app.get('/', (req, res) => {
  res.json({
    status: 'online',
    practice: 'ASHIRU TRADITIONAL BONE SETTER',
    sectors: [
      'Alternative Medical Services',
      'Traditional Orthopedic Services',
      'General Farming'
    ],
    specialist: 'Dr. Ashiru Salisu',
    email: 'mbelloyahay345@gmail.com',
    hotline: '+234 813 708 8984',
    admin_portal: `http://localhost:${PORT}/admin`,
    endpoints: {
      appointments: '/api/appointments',
      emergency: '/api/emergency',
      contact: '/api/contact',
      stats: '/api/stats',
      update_status: '/api/update-status'
    }
  });
});

// ==========================================
// 1. APPOINTMENTS API
// ==========================================

// Helper to determine sector
function detectSector(service = '') {
  const s = service.toLowerCase();
  if (s.includes('alternative') || s.includes('herbal') || s.includes('poultice')) {
    return 'alternative';
  }
  if (s.includes('farming') || s.includes('botanical') || s.includes('cultivation') || s.includes('seed')) {
    return 'farming';
  }
  return 'orthopedic';
}

// POST: Book Appointment Consultation
const handleCreateAppointment = (req, res) => {
  try {
    const data = req.body || {};
    const name = (data.name || '').trim();
    const phone = (data.phone || '').trim();
    const service = (data.service || 'Closed Fracture Reduction (Traditional Orthopedics)').trim();
    const urgency = (data.urgency || 'Standard (Within 24-48 hrs)').trim();
    const preferredDate = (data.date || data.preferred_date || new Date().toISOString().slice(0, 10)).trim();
    const consultType = (data.consultType || data.consult_type || 'In-Clinic Specialist Consultation').trim();
    const details = (data.details || '').trim();

    if (!name) {
      return res.status(422).json({ success: false, error: 'Patient name is required.' });
    }
    if (!phone) {
      return res.status(422).json({ success: false, error: 'Phone or WhatsApp number is required.' });
    }

    const sector = detectSector(service);
    const referenceCode = `ATBS-${new Date().getFullYear()}-${Math.floor(1000 + Math.random() * 9000)}`;

    const insertStmt = db.prepare(`
      INSERT INTO appointments (
        reference_code, name, phone, sector, service, urgency, preferred_date, consult_type, details, status
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
    `);

    const result = insertStmt.run(
      referenceCode, name, phone, sector, service, urgency, preferredDate, consultType, details
    );

    // Format WhatsApp dispatch url
    const clinicWhatsAppNumber = '2348137088984';
    const waText = `*Appointment Booking Confirmed - ASHIRU TRADITIONAL BONE SETTER*
Ref: ${referenceCode}
Patient: ${name}
Phone: ${phone}
Sector: ${sector.charAt(0).toUpperCase() + sector.slice(1)}
Service: ${service}
Urgency: ${urgency}
Date: ${preferredDate}
Mode: ${consultType}
Details: ${details || 'None provided'}`;

    const whatsAppUrl = `https://wa.me/${clinicWhatsAppNumber}?text=${encodeURIComponent(waText)}`;

    res.status(201).json({
      success: true,
      message: 'Appointment request submitted successfully!',
      reference_code: referenceCode,
      id: Number(result.lastInsertRowid),
      whatsapp_url: whatsAppUrl,
      appointment: {
        reference_code: referenceCode,
        name,
        phone,
        sector,
        service,
        urgency,
        preferred_date: preferredDate,
        consult_type: consultType,
        status: 'Pending',
        created_at: new Date().toISOString()
      }
    });
  } catch (err) {
    console.error('Error creating appointment:', err);
    res.status(500).json({ success: false, error: 'Database error: ' + err.message });
  }
};

// GET: List Appointments
const handleListAppointments = (req, res) => {
  try {
    const { sector, status, limit = 100 } = req.query;
    let query = 'SELECT * FROM appointments WHERE 1=1';
    const params = [];

    if (sector && ['orthopedic', 'alternative', 'farming'].includes(sector)) {
      query += ' AND sector = ?';
      params.push(sector);
    }
    if (status && status !== 'all') {
      query += ' AND status = ?';
      params.push(status);
    }

    query += ' ORDER BY id DESC LIMIT ?';
    params.push(Math.min(200, Math.max(1, parseInt(limit, 10))));

    const stmt = db.prepare(query);
    const appointments = stmt.all(...params);

    // Summary stats
    const stats = db.prepare(`
      SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN sector = 'orthopedic' THEN 1 ELSE 0 END) as orthopedic_count,
        SUM(CASE WHEN sector = 'alternative' THEN 1 ELSE 0 END) as alternative_count,
        SUM(CASE WHEN sector = 'farming' THEN 1 ELSE 0 END) as farming_count
      FROM appointments
    `).get();

    res.json({
      success: true,
      count: appointments.length,
      stats: stats || {},
      data: appointments
    });
  } catch (err) {
    console.error('Error fetching appointments:', err);
    res.status(500).json({ success: false, error: 'Query error: ' + err.message });
  }
};

app.post('/api/appointments', handleCreateAppointment);
app.post('/api/appointments.php', handleCreateAppointment);
app.get('/api/appointments', handleListAppointments);
app.get('/api/appointments.php', handleListAppointments);

// ==========================================
// 2. 24/7 EMERGENCY TRAUMA DISPATCH API
// ==========================================

const handleCreateEmergency = (req, res) => {
  try {
    const data = req.body || {};
    const callerName = (data.caller_name || data.name || 'Emergency Caller').trim();
    const callerPhone = (data.caller_phone || data.phone || '').trim();
    const injuryType = (data.injury_type || 'Acute Bone Fracture / Joint Dislocation').trim();
    const location = (data.location || 'Location Magashi No. 168, Kano, Nigeria').trim();
    const notes = (data.notes || '').trim();

    if (!callerPhone) {
      return res.status(422).json({ success: false, error: 'Emergency contact phone number is required.' });
    }

    const insertStmt = db.prepare(`
      INSERT INTO emergency_dispatches (caller_name, caller_phone, injury_type, location, notes, status)
      VALUES (?, ?, ?, ?, ?, 'Dispatched')
    `);
    const result = insertStmt.run(callerName, callerPhone, injuryType, location, notes);
    const dispatchId = Number(result.lastInsertRowid);

    const waText = `🚨 *URGENT BONE TRAUMA DISPATCH - ASHIRU TRADITIONAL BONE SETTER*
Dispatch ID: #${dispatchId}
Caller: ${callerName}
Phone: ${callerPhone}
Injury: ${injuryType}
Location: ${location}
Notes: ${notes || 'Patient en route to emergency triage'}`;

    const whatsAppUrl = `https://wa.me/2348137088984?text=${encodeURIComponent(waText)}`;

    res.status(201).json({
      success: true,
      message: 'Emergency dispatch record logged immediately.',
      dispatch_id: dispatchId,
      hotline: '+234 813 708 8984',
      facility_address: 'Location Magashi No. 168, Kano, Nigeria 🇳🇬',
      official_email: 'mbelloyahay345@gmail.com',
      whatsapp_url: whatsAppUrl
    });
  } catch (err) {
    console.error('Error logging emergency dispatch:', err);
    res.status(500).json({ success: false, error: 'Database error: ' + err.message });
  }
};

const handleListEmergencies = (req, res) => {
  try {
    const dispatches = db.prepare('SELECT * FROM emergency_dispatches ORDER BY id DESC LIMIT 100').all();
    res.json({
      success: true,
      count: dispatches.length,
      data: dispatches
    });
  } catch (err) {
    console.error('Error fetching emergencies:', err);
    res.status(500).json({ success: false, error: 'Database error: ' + err.message });
  }
};

app.post('/api/emergency', handleCreateEmergency);
app.post('/api/emergency.php', handleCreateEmergency);
app.get('/api/emergency', handleListEmergencies);
app.get('/api/emergency.php', handleListEmergencies);

// ==========================================
// 3. CONTACT & SECTOR INQUIRIES API
// ==========================================

const handleCreateContact = (req, res) => {
  try {
    const data = req.body || {};
    const name = (data.name || '').trim();
    const email = (data.email || '').trim();
    const phone = (data.phone || '').trim();
    const sector = (data.sector || 'orthopedic').trim();
    const subject = (data.subject || 'General Consultation / Inquiry').trim();
    const message = (data.message || '').trim();

    if (!name || !message) {
      return res.status(422).json({ success: false, error: 'Name and message are required fields.' });
    }

    const insertStmt = db.prepare(`
      INSERT INTO contact_inquiries (name, email, phone, sector, subject, message)
      VALUES (?, ?, ?, ?, ?, ?)
    `);
    const result = insertStmt.run(name, email, phone, sector, subject, message);
    const insertedId = Number(result.lastInsertRowid);

    const waText = `*New Inquiry - ASHIRU TRADITIONAL BONE SETTER*
Inquiry ID: #${insertedId}
Name: ${name}
Phone: ${phone || 'Not provided'}
Email: ${email || 'Not provided'}
Sector: ${sector.charAt(0).toUpperCase() + sector.slice(1)}
Subject: ${subject}
Message: ${message}`;

    const whatsAppUrl = `https://wa.me/2348137088984?text=${encodeURIComponent(waText)}`;

    res.status(201).json({
      success: true,
      message: 'Thank you! Your inquiry has been received. Our clinical assistant will contact you shortly.',
      id: insertedId,
      official_email: 'mbelloyahay345@gmail.com',
      hotline: '+234 813 708 8984',
      facility_address: 'Location Magashi No. 168, Kano, Nigeria 🇳🇬',
      whatsapp_url: whatsAppUrl
    });
  } catch (err) {
    console.error('Error logging inquiry:', err);
    res.status(500).json({ success: false, error: 'Database error: ' + err.message });
  }
};

const handleListContact = (req, res) => {
  try {
    const inquiries = db.prepare('SELECT * FROM contact_inquiries ORDER BY id DESC LIMIT 100').all();
    res.json({
      success: true,
      count: inquiries.length,
      data: inquiries
    });
  } catch (err) {
    console.error('Error fetching inquiries:', err);
    res.status(500).json({ success: false, error: 'Database error: ' + err.message });
  }
};

app.post('/api/contact', handleCreateContact);
app.post('/api/contact.php', handleCreateContact);
app.get('/api/contact', handleListContact);
app.get('/api/contact.php', handleListContact);

// ==========================================
// 4. STATS & ANALYTICS API
// ==========================================

const handleGetStats = (req, res) => {
  try {
    const sectorStats = db.prepare(`
      SELECT sector, COUNT(*) as count
      FROM appointments
      GROUP BY sector
    `).all();

    const statusStats = db.prepare(`
      SELECT status, COUNT(*) as count
      FROM appointments
      GROUP BY status
    `).all();

    const urgencyStats = db.prepare(`
      SELECT urgency, COUNT(*) as count
      FROM appointments
      GROUP BY urgency
    `).all();

    const totals = db.prepare(`
      SELECT 
        (SELECT COUNT(*) FROM appointments) as total_appointments,
        (SELECT COUNT(*) FROM emergency_dispatches) as total_emergencies,
        (SELECT COUNT(*) FROM contact_inquiries) as total_inquiries
    `).get();

    res.json({
      success: true,
      organization: 'ASHIRU TRADITIONAL BONE SETTER',
      sectors: [
        'Alternative Medical Services',
        'Traditional Orthopedic Services',
        'General Farming'
      ],
      totals: totals || {},
      by_sector: sectorStats,
      by_status: statusStats,
      by_urgency: urgencyStats
    });
  } catch (err) {
    console.error('Error generating stats:', err);
    res.status(500).json({ success: false, error: err.message });
  }
};

app.get('/api/stats', handleGetStats);
app.get('/api/stats.php', handleGetStats);

// ==========================================
// 5. STATUS UPDATE API (AJAX)
// ==========================================

const handleUpdateStatus = (req, res) => {
  try {
    const data = req.body || {};
    const id = parseInt(data.id, 10);
    const type = (data.type || 'appointment').trim();
    const status = (data.status || '').trim();

    if (!id || !status) {
      return res.status(422).json({ success: false, error: 'Invalid record ID or status.' });
    }

    if (type === 'emergency') {
      const allowedEmergencyStatuses = ['Dispatched', 'En Route', 'Received in Clinic', 'Treated', 'Resolved'];
      if (!allowedEmergencyStatuses.includes(status)) {
        return res.status(422).json({ success: false, error: 'Invalid emergency status.' });
      }
      db.prepare('UPDATE emergency_dispatches SET status = ? WHERE id = ?').run(status, id);
      return res.json({
        success: true,
        message: `Emergency dispatch #${id} status updated to ${status}.`
      });
    } else {
      const allowedAppointmentStatuses = ['Pending', 'Confirmed', 'In Treatment', 'Completed', 'Cancelled'];
      if (!allowedAppointmentStatuses.includes(status)) {
        return res.status(422).json({ success: false, error: 'Invalid appointment status.' });
      }
      db.prepare('UPDATE appointments SET status = ? WHERE id = ?').run(status, id);
      return res.json({
        success: true,
        message: `Appointment #${id} status updated to ${status}.`
      });
    }
  } catch (err) {
    console.error('Error updating status:', err);
    res.status(500).json({ success: false, error: 'Database error: ' + err.message });
  }
};

app.post('/api/update-status', handleUpdateStatus);
app.post('/admin/update_status.php', handleUpdateStatus);

// Start Server
app.listen(PORT, () => {
  console.log(`=======================================================`);
  console.log(`ASHIRU TRADITIONAL BONE SETTER - Node.js Express Server`);
  console.log(`Alternative Medicine • Traditional Orthopedics • Farming`);
  console.log(`=======================================================`);
  console.log(`Server running at:       http://localhost:${PORT}`);
  console.log(`Admin Dashboard:        http://localhost:${PORT}/admin`);
  console.log(`Appointments API:       http://localhost:${PORT}/api/appointments`);
  console.log(`Emergency Trauma API:   http://localhost:${PORT}/api/emergency`);
  console.log(`Contact / Farming API:  http://localhost:${PORT}/api/contact`);
  console.log(`Practice Analytics API: http://localhost:${PORT}/api/stats`);
  console.log(`Official Hotline:       +234 813 708 8984`);
  console.log(`Official Email:         mbelloyahay345@gmail.com`);
  console.log(`=======================================================`);
});

export default app;
