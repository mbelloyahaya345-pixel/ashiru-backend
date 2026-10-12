# ASHIRU TRADITIONAL BONE SETTER — Node.js & Express Backend System

Modern, high-performance Node.js & Express REST backend for **ASHIRU TRADITIONAL BONE SETTER**, covering all 3 clinical practice sectors:
1. **Alternative Medical Services** (Holistic medicine, bone-healing poultices, herbal therapies)
2. **Traditional Orthopedic Services** (Closed fracture reduction, joint dislocations, spinal traction)
3. **General Farming** (Medicinal herb botanical cultivation, sustainable agricultural production)

---

## 🚀 Quick Start (1-Click Run)

To start the Node.js Express backend and Admin Dashboard:

### Option 1: 1-Click Launcher (Windows)
Double-click or run:
```cmd
backend\run_backend.bat
```

### Option 2: From Project Root
```bash
npm run server
```
Or for auto-reloading during development:
```bash
npm run server:dev
```

### Option 3: From backend/ directory
```bash
cd backend
npm start
```

Server starts at **`http://localhost:8000`**.

---

## 🌐 URLs & Endpoints

| Resource | URL | Method | Description |
|---|---|---|---|
| **Admin Dashboard** | `http://localhost:8000/admin` | GET | White Luxury Management Portal |
| **API Health Check** | `http://localhost:8000/` | GET | System status & endpoint catalog |
| **Appointments API** | `http://localhost:8000/api/appointments` | POST / GET | Create consultation & query patient records |
| **Emergency Trauma API** | `http://localhost:8000/api/emergency` | POST / GET | 24/7 urgent bone trauma triage logging |
| **Contact / Farming API** | `http://localhost:8000/api/contact` | POST / GET | General & agricultural partnership inquiries |
| **Practice Analytics** | `http://localhost:8000/api/stats` | GET | Sector, urgency, and status breakdowns |
| **Status Updater** | `http://localhost:8000/api/update-status` | POST | AJAX status updater for records |

*(Note: PHP-style URLs like `/api/appointments.php` and `/admin/index.php` are also aliased for 100% backward compatibility.)*

---

## 📂 Architecture & Directory Structure

```
backend/
├── server.js               # Express 5 REST application & route controller
├── database.js             # Native Node 24 SQLite (node:sqlite) connector & schema manager
├── package.json            # Standalone Node package config
├── public/
│   └── admin/
│       └── index.html      # White Luxury Medical Dashboard (Pure white, Emerald & Gold)
├── database/
│   └── data.sqlite         # High-performance local SQLite database (WAL journal mode)
├── run_backend.bat         # 1-Click Windows batch launcher
└── README.md
```

---

## 🛠️ API Payloads & Examples

### 1. Book Appointment Consultation
- **Endpoint**: `POST /api/appointments`
- **Request Body**:
  ```json
  {
    "name": "Ibrahim Danladi",
    "phone": "+234 813 708 8984",
    "service": "Closed Fracture Reduction (Traditional Orthopedics)",
    "urgency": "High (Within 4-12 hrs)",
    "date": "2026-10-18",
    "consultType": "In-Clinic Specialist Consultation",
    "details": "Left forearm fracture sustained today."
  }
  ```
- **Response**:
  ```json
  {
    "success": true,
    "message": "Appointment request submitted successfully!",
    "reference_code": "ATBS-2026-9913",
    "id": 1,
    "whatsapp_url": "https://wa.me/2348137088984?text=...",
    "appointment": { ... }
  }
  ```

### 2. Log 24/7 Emergency Trauma Dispatch
- **Endpoint**: `POST /api/emergency`
- **Request Body**:
  ```json
  {
    "caller_name": "Musa Lawal",
    "caller_phone": "+234 813 708 8984",
    "injury_type": "Compound ankle dislocation",
    "location": "Location Magashi No. 168, Kano, Nigeria",
    "notes": "Patient arriving via ambulance"
  }
  ```

### 3. Contact & Sector Inquiry
- **Endpoint**: `POST /api/contact`
- **Request Body**:
  ```json
  {
    "name": "Amina Sani",
    "email": "mbelloyahay345@gmail.com",
    "phone": "+234 813 708 8984",
    "sector": "farming",
    "subject": "Organic Medicinal Herb Supply Inquiry",
    "message": "Interested in organic bone-healing botanical supply partnerships."
  }
  ```

---

## 📞 Official Clinical Contact Details
- **Lead Specialist**: Dr. Ashiru Salisu
- **Practice Name**: ASHIRU TRADITIONAL BONE SETTER
- **Official Email**: `mbelloyahay345@gmail.com`
- **Trauma Hotline & WhatsApp**: `+234 813 708 8984`
- **Facility Address**: Location Magashi No. 168, Kano, Nigeria 🇳🇬
- **Live Edge Frontend**: `https://ashiru-tbs.surge.sh`
