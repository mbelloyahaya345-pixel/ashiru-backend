@echo off
echo =======================================================
echo ASHIRU TRADITIONAL BONE SETTER - Node.js Express Server
echo Alternative Medicine • Traditional Orthopedics • Farming
echo =======================================================
echo.

where node >nul 2>nul
if %errorlevel% neq 0 (
    echo Error: Node.js is not found in your system PATH.
    echo Please install Node.js from https://nodejs.org or check your PATH.
    pause
    exit /b 1
)

echo Node.js Runtime:
node -v
echo.
echo -------------------------------------------------------
echo Starting Express Backend & Admin Portal on Port 8000...
echo API Endpoints:
echo   - Appointments: http://localhost:8000/api/appointments
echo   - Emergency:    http://localhost:8000/api/emergency
echo   - Contact:      http://localhost:8000/api/contact
echo   - Analytics:    http://localhost:8000/api/stats
echo Admin Dashboard:  http://localhost:8000/admin
echo -------------------------------------------------------
echo Press Ctrl+C to stop the server at any time.
echo.

cd /d "%~dp0"
node server.js
pause
