<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * VISITOR LOGS MODULE
 * Purpose: Tracks and manages visitor entries/exits for security monitoring
 * Features: Log visitors, search/filter logs, export reports, security tracking
 * HR4 API Integration: Can fetch employee data for visitor host validation
 * Financial API Integration: Can fetch financial data for expense tracking
 */

// Include HR4 API for employee data integration
require_once __DIR__ . '/../integ/hr4_api.php';



// config.php - Database configuration

class Database
{
    private $host = "127.0.0.1";
    private $db_name = "admin_new";
    private $username = "admin_new";
    private $password = "123";
    public $conn;

    // Get database connection
    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

// Helper functions
function executeQuery($sql, $params = [])
{
    $database = new Database();
    $db = $database->getConnection();

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $exception) {
        return false;
    }
}

function fetchAll($sql, $params = [])
{
    $stmt = executeQuery($sql, $params);
    if ($stmt) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

function fetchOne($sql, $params = [])
{
    $stmt = executeQuery($sql, $params);
    if ($stmt) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}

function getLastInsertId()
{
    $database = new Database();
    $db = $database->getConnection();
    return $db->lastInsertId();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ateria Visitor Management</title>
    <link rel="icon" type="image/x-icon" href="../assets/image/logo2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/facilities-reservation.css?v=21">
    <link rel="stylesheet" href="../assets/css/Visitors.css?v=<?php echo time(); ?>">
    <!-- Added styles for Reports read-panel (beautify only) -->
    <style>
        /* Read panel (side details) */
        .read-panel {
            display: none;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-top: 20px;
        }

        .read-panel.header {
            font-weight: 600;
        }

        .read-panel .rp-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .btn-back {
            background: #2d8cf0;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .rp-row {
            margin-bottom: 10px;
        }

        .rp-row .label {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .rp-row .value {
            color: #111827;
            font-size: 15px;
            font-weight: 500;
        }

        /* Imitate nav-link styling for the back button to bypass JS selectors */
        .nav-item-back {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .nav-item-back:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body>
    <style>
        body {
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            overflow: hidden;
            /* Header and sidebar take care of layout */
        }

        /* Top Header */
        .module-header {
            background: #1e293b;
            color: white;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .module-header h2 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
        }

        .desktop-only {
            display: none !important;
        }

        @media (min-width: 1201px) {
            .desktop-only {
                display: flex !important;
            }
            .module-header h2 {
                font-size: 1.5rem;
                overflow: visible;
            }
        }

        .top-nav {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .top-nav a,
        .top-nav span {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .top-nav a:hover {
            color: white;
        }

        .top-nav .nav-pill {
            padding: 8px 18px;
            background: #3b82f6;
            color: white !important;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        }

        /* Module Body Layout */
        .module-body {
            display: flex;
            padding: 30px 40px;
            gap: 30px;
            height: calc(100vh - 65px);
            box-sizing: border-box;
        }

        /* Left Sidebar Nav */
        .module-sidebar {
            width: 280px;
            background: white;
            border-radius: 20px;
            padding: 30px 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 15px;
            height: fit-content;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            text-decoration: none;
            color: #64748b;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .sidebar-item i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-item:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .sidebar-item.active {
            background: #1e293b;
            color: white;
            box-shadow: 0 8px 20px rgba(30, 41, 59, 0.25);
        }

        /* Main Content Card */
        .module-main-content {
            flex: 1;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            overflow-y: auto;
            position: relative;
        }

        .btn-time-in-large {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: #10b981;
            color: white;
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            margin-bottom: 30px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.2);
        }

        .btn-time-in-large:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        .content-header-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .content-header-row i {
            font-size: 1.6rem;
            color: #1e293b;
        }

        .content-header-row h3 {
            margin: 0;
            font-size: 1.5rem;
            color: #1e293b;
            font-weight: 700;
        }

        /* Redesigning the tables to match screenshot */
        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table th {
            text-align: left;
            padding: 15px 20px;
            background: #f8fafc;
            color: #94a3b8;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.1em;
            border-bottom: 1px solid #f1f5f9;
        }

        .custom-table td {
            padding: 20px;
            border-bottom: 1px solid #f8fafc;
            font-size: 0.95rem;
            color: #1e293b;
            font-weight: 500;
        }

        .btn-action-view-small {
            background: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-action-view-small:hover {
            background: #2563eb;
        }

        .btn-action-timeout-small {
            background: #f59e0b;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-action-timeout-small:hover {
            background: #d97706;
        }

        /* Re-style old stats container to not clash */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 1024px) {
            .module-body {
                flex-direction: column;
                overflow-y: auto;
            }

            .module-sidebar {
                width: 100%;
            }
        }
    </style>
    </head>

    <body>
        <!-- Top Header Navigation -->
        <div class="module-header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <img src="../assets/image/logo2.png" alt="Ateria" style="height: 35px; brightness: 1.2;">
                <div style="width: 2px; height: 25px; background: rgba(255,255,255,0.2);"></div>
                <h2
                    style="text-transform: uppercase; letter-spacing: 2px; font-size: 1.1rem; color: #fff; font-weight: 800;">
                    Visitor Management</h2>
            </div>
            <div class="top-nav">
                <!-- Real-time Clock -->
                <div class="desktop-only"
                    style="margin-right: 30px; display: none; align-items: center; gap: 15px; border-right: 1px solid rgba(255,255,255,0.1); padding-right: 25px;">
                    <div id="module-clock" style="color: #fff; font-weight: 800; font-size: 1rem;"></div>
                    <div id="module-date"
                        style="color: rgba(255,255,255,0.5); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                    </div>
                </div>

                <!-- Notification Trigger -->
                <div style="margin-right: 30px; position: relative; cursor: pointer; display: flex; align-items: center;"
                    onclick="alert('All caught up!')">
                    <i class="fas fa-bell" style="color: rgba(255,255,255,0.8); font-size: 1.1rem;"></i>
                    <span
                        style="position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; font-size: 0.65rem; height: 16px; width: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; border: 2px solid #1e293b;">0</span>
                </div>

                <!-- Profile Display -->
                <div style="display: flex; align-items: center; gap: 10px; padding: 5px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; margin-right: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div class="desktop-only" style="display: none; flex-direction: column; text-align: right;">
                        <span style="font-size: 0.65rem; color: rgba(255,255,255,0.5); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;"><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></span>
                        <span style="font-size: 0.75rem; color: #fff; font-weight: 600; letter-spacing: -0.2px;"><?= htmlspecialchars($_SESSION['email'] ?? 'No Email') ?></span>
                    </div>
                    <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; border: 1px solid rgba(255,255,255,0.2);">
                        <i class="fas fa-user-tie" style="font-size: 0.85rem;"></i>
                    </div>
                </div>

                <span onclick="showPage('dashboard')" class="desktop-only nav-item-top" data-page="dashboard" style="display: none;">Dashboard</span>
                <span onclick="showPage('hotel')" class="desktop-only nav-pill" data-page="hotel" style="display: none;">Hotel</span>
                <span onclick="showPage('restaurant')" class="desktop-only nav-item-top" data-page="restaurant" style="display: none;">Restaurant</span>
                <span onclick="showPage('reports')" class="desktop-only nav-item-top" data-page="reports" style="display: none;">Reports</span>
                <a href="dashboard.php" class="nav-item-top">Back</a>
            </div>
        </div>

        <!-- Main Module Layout -->
        <div class="module-body">
            <!-- Sidebar Navigation -->
            <div class="module-sidebar">
                <div class="sidebar-item active" onclick="showPage('dashboard')" data-sidebar="dashboard">
                    <i class="fas fa-chart-line"></i> Dashboard
                </div>
                <div class="sidebar-item" onclick="showPage('hotel')" data-sidebar="hotel">
                    <i class="fas fa-hotel"></i> Hotel Management
                </div>
                <div class="sidebar-item" onclick="showPage('restaurant')" data-sidebar="restaurant">
                    <i class="fas fa-utensils"></i> Restaurant Management
                </div>
                <div class="sidebar-item" onclick="showPage('reports')" data-sidebar="reports">
                    <i class="fas fa-file-invoice"></i> Reports
                </div>
                <div class="sidebar-item" onclick="alert('Settings coming soon!')">
                    <i class="fas fa-cog"></i> Settings
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="module-main-content">

                <!-- Dashboard Page -->
                <div id="dashboard" class="page active">
                    <div class="content-header-row">
                        <i class="fas fa-chart-pie"></i>
                        <h3>Visitor Overview</h3>
                    </div>
                    <div class="stats-container">
                        <div class="stat-card">
                            <i class="fas fa-concierge-bell"></i>
                            <div class="stat-number" id="hotel-today">0</div>
                            <div class="stat-label">Hotel Today</div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-utensils"></i>
                            <div class="stat-number" id="restaurant-today">0</div>
                            <div class="stat-label">Restaurant Today</div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-user-clock"></i>
                            <div class="stat-number" id="hotel-current">0</div>
                            <div class="stat-label">Checked In Hotel</div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-chair"></i>
                            <div class="stat-number" id="restaurant-current">0</div>
                            <div class="stat-label">Checked In Restaurant</div>
                        </div>
                    </div>

                    <div class="card">
                        <h2>Recent Activity</h2>
                        <div id="recent-activity">
                            <!-- Activity populated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Hotel Management Page -->
                <div id="hotel" class="page">
                    <button class="btn-time-in-large" onclick="openEntryModal('hotel')" style="background: #3b82f6;">
                        <i class="fas fa-plus-circle"></i> New Hotel Entry
                    </button>

                    <div class="content-header-row">
                        <i class="fas fa-users-viewfinder"></i>
                        <h3>Current Guests</h3>
                    </div>

                    <div class="tab-content active" id="hotel-visitors-tab">
                        <div class="table-container">
                            <table class="custom-table" id="hotel-current-table">
                                <thead>
                                    <tr>
                                        <th>NAME</th>
                                        <th>ROOM</th>
                                        <th>CHECK-IN</th>
                                        <th>STATUS</th>
                                        <th>ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-content" id="hotel-history-tab">
                        <div class="content-header-row">
                            <h3>Hotel History</h3>
                        </div>
                        <div class="table-container">
                            <table class="custom-table" id="hotel-history-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Room</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Restaurant Management Page -->
                <div id="restaurant" class="page">
                    <button class="btn-time-in-large" onclick="openEntryModal('restaurant')"
                        style="background: #3b82f6;">
                        <i class="fas fa-plus-circle"></i> New Restaurant Entry
                    </button>

                    <div class="content-header-row">
                        <i class="fas fa-utensils"></i>
                        <h3>Current Diners</h3>
                    </div>

                    <div class="tab-content active" id="restaurant-visitors-tab">
                        <div class="table-container">
                            <table class="custom-table" id="restaurant-current-table">
                                <thead>
                                    <tr>
                                        <th>NAME</th>
                                        <th>PARTY SIZE</th>
                                        <th>TABLE</th>
                                        <th>CHECK-IN</th>
                                        <th>ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Reports Page -->
                <div id="reports" class="page">
                    <div class="content-header-row">
                        <i class="fas fa-file-invoice"></i>
                        <h3>Analytics & Reports</h3>
                    </div>
                    <div class="card">
                        <form id="report-form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Report Type</label>
                                    <select id="report-type" name="report-type">
                                        <option value="daily">Daily Report</option>
                                        <option value="weekly">Weekly Report</option>
                                        <option value="monthly">Monthly Report</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Venue</label>
                                    <select id="report-venue" name="report-venue">
                                        <option value="all">All Venues</option>
                                        <option value="hotel">Hotel</option>
                                        <option value="restaurant">Restaurant</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn-submit-premium" style="width: auto; padding: 12px 25px;">
                                <i class="fas fa-download"></i> Generate PDF Report
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card" id="report-results" style="display: none;">
                    <h2>Report Results</h2>
                    <div id="report-data">
                        <!-- Report data will be displayed here -->
                    </div>
                </div>

                <!-- Read / Details panel for selected report item -->
                <aside id="report-read-panel" class="read-panel" aria-hidden="true" style="display:none;">
                    <div class="rp-header">
                        <h3 style="margin:0;">Report Details</h3>
                        <button type="button" class="btn-back" id="rp-back-btn">Back</button>
                    </div>
                    <div id="rp-content">
                        <div class="rp-row">
                            <div class="label">Name</div>
                            <div class="value" id="rp-name">-</div>
                        </div>
                        <div class="rp-row">
                            <div class="label">Venue</div>
                            <div class="value" id="rp-venue">-</div>
                        </div>
                        <div class="rp-row">
                            <div class="label">Check-in</div>
                            <div class="value" id="rp-checkin">-</div>
                        </div>
                        <div class="rp-row">
                            <div class="label">Check-out</div>
                            <div class="value" id="rp-checkout">-</div>
                        </div>
                        <div class="rp-row">
                            <div class="label">Notes</div>
                            <div class="value" id="rp-notes">-</div>
                        </div>
                    </div>
                </aside>
            </div>



            <!-- Maintenance Page -->
            <div id="maintenance" class="page">
                <div class="content-header-row">
                    <i class="fas fa-screwdriver-wrench"></i>
                    <h3>Service & Maintenance</h3>
                </div>

                <div class="card" style="padding: 1.5rem;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--gray-100);">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div
                                style="width: 40px; height: 40px; background: rgba(59, 130, 246, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--secondary);">
                                <i class="fas fa-screwdriver-wrench"></i>
                            </div>
                            <h2 style="margin: 0; font-size: 1.25rem;">Maintenance Logs</h2>
                        </div>
                        <button class="btn-primary-action" style="width: auto; padding: 10px 20px;"
                            onclick="alert('Maintenance feature coming soon!')">
                            <i class="fas fa-plus"></i> Schedule Maintenance
                        </button>
                    </div>

                    <div class="table-container">
                        <table class="table">
                            <thead style="background: var(--gray-50);">
                                <tr>
                                    <th style="width: 120px;">Ticket ID</th>
                                    <th>Facility / Area</th>
                                    <th>Issue Description</th>
                                    <th style="width: 150px;">Reported Date</th>
                                    <th style="width: 120px;">Priority</th>
                                    <th style="width: 130px;">Status</th>
                                    <th style="width: 80px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-weight: 700; color: var(--secondary);">#MT-2024-001</td>
                                    <td style="font-weight: 700; color: #1e293b;">Banquet Hall A</td>
                                    <td style="color: var(--gray-600);">Air conditioning unit leaking water</td>
                                    <td>2024-01-15</td>
                                    <td><span class="status-badge"
                                            style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;"><i
                                                class="fas fa-circle" style="font-size: 6px;"></i> HIGH</span></td>
                                    <td><span class="status-badge badge-in-progress"><i
                                                class="fas fa-spinner fa-spin"></i> IN PROGRESS</span></td>
                                    <td style="text-align: center;"><button class="btn-action-view"
                                            onclick="alert('Viewing #MT-2024-001')"><i class="fas fa-eye"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700; color: var(--secondary);">#MT-2024-002</td>
                                    <td style="font-weight: 700; color: #1e293b;">Meeting Room 2</td>
                                    <td style="color: var(--gray-600);">Projector bulb replacement needed</td>
                                    <td>2024-01-20</td>
                                    <td><span class="status-badge badge-pending"><i class="fas fa-circle"
                                                style="font-size: 6px;"></i> MEDIUM</span></td>
                                    <td><span class="status-badge badge-open"><i class="fas fa-folder-open"></i>
                                            OPEN</span></td>
                                    <td style="text-align: center;"><button class="btn-action-view"
                                            onclick="alert('Viewing #MT-2024-002')"><i class="fas fa-eye"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700; color: var(--secondary);">#MT-2024-003</td>
                                    <td style="font-weight: 700; color: #1e293b;">Pool Side</td>
                                    <td style="color: var(--gray-600);">Loose tiles near the deep end</td>
                                    <td>2024-01-18</td>
                                    <td><span class="status-badge badge-completed"
                                            style="background: #f0fdf4; color: #15803d;"><i class="fas fa-circle"
                                                style="font-size: 6px;"></i> LOW</span></td>
                                    <td><span class="status-badge badge-completed"><i class="fas fa-check-circle"></i>
                                            COMPLETED</span></td>
                                    <td style="text-align: center;"><button class="btn-action-view"
                                            onclick="alert('Viewing #MT-2024-003')"
                                            style="color: #10b981; border-color: #10b981;"><i
                                                class="fas fa-check-double"></i></button></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700; color: var(--secondary);">#MT-2024-004</td>
                                    <td style="font-weight: 700; color: #1e293b;">Executive Lounge</td>
                                    <td style="color: var(--gray-600);">Coffee machine malfunction</td>
                                    <td>2024-01-22</td>
                                    <td><span class="status-badge badge-pending"><i class="fas fa-circle"
                                                style="font-size: 6px;"></i> MEDIUM</span></td>
                                    <td><span class="status-badge badge-pending"><i class="fas fa-clock"></i>
                                            PENDING</span></td>
                                    <td style="text-align: center;"><button class="btn-action-view"
                                            onclick="alert('Viewing #MT-2024-004')"><i class="fas fa-eye"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- Confirmation Modal -->
        <div id="confirmation-modal" class="modal">
            <div class="modal-content" style="max-width: 400px; text-align: center;">
                <div class="modal-header" style="justify-content: center; border-bottom: none;">
                    <h2 style="color: #e74c3c;"><i class="fas fa-exclamation-triangle"></i> Confirm Action</h2>
                </div>
                <div class="modal-body" style="padding: 20px 0;">
                    <p id="confirmation-message">Are you sure you want to proceed?</p>
                </div>
                <div class="modal-footer" style="display: flex; justify-content: center; gap: 10px; padding-top: 10px;">
                    <button id="confirm-btn" class="btn-danger">Yes, Confirm</button>
                    <button id="cancel-btn" class="btn-secondary" onclick="closeConfirmationModal()">Cancel</button>
                </div>
            </div>
        </div>

        <style>
            /* Modal Styles */
            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                animation: fadeIn 0.3s;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            .modal-content {
                background-color: #fefefe;
                margin: 15% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                animation: slideDown 0.3s;
            }

            @keyframes slideDown {
                from {
                    transform: translateY(-50px);
                    opacity: 0;
                }

                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            .btn-danger {
                background-color: #e74c3c;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                transition: background 0.3s;
            }

            .btn-danger:hover {
                background-color: #c0392b;
            }

            .btn-secondary {
                background-color: #95a5a6;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                transition: background 0.3s;
            }

            .btn-secondary:hover {
                background-color: #7f8c8d;
            }
        </style>

        <!-- Entry Modal (Insert/Update) -->
        <div id="entry-modal" class="modal">
            <div class="modal-content" style="max-width: 600px;">
                <div class="modal-header"
                    style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f8fafc; padding-bottom: 15px; margin-bottom: 20px;">
                    <h2 id="entry-modal-title" style="color: #1e293b; margin: 0; font-weight: 800;">Registration</h2>
                    <button onclick="closeEntryModal()"
                        style="background: transparent; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer;"><i
                            class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <!-- Hotel Form -->
                    <form id="modal-hotel-form" style="display: none;">
                        <input type="hidden" name="action" value="insert">
                        <input type="hidden" name="entry_id" value="">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Guest Name</label>
                                <input type="text" name="full_name" required placeholder="John Doe">
                            </div>
                            <div class="form-group">
                                <label>Room / Suite</label>
                                <input type="text" name="room_number" required placeholder="101">
                            </div>
                        </div>
                    </form>

                    <!-- Restaurant Form -->
                    <form id="modal-restaurant-form" style="display: none;">
                        <input type="hidden" name="action" value="insert">
                        <input type="hidden" name="entry_id" value="">
                        <div class="form-grid" style="grid-template-columns: 1fr 1fr 1fr;">
                            <div class="form-group">
                                <label>Diner Name</label>
                                <input type="text" name="visitor-name" required placeholder="Jane Doe">
                            </div>
                            <div class="form-group">
                                <label>Party Size</label>
                                <input type="number" name="party-size" required placeholder="2">
                            </div>
                            <div class="form-group">
                                <label>Table</label>
                                <input type="text" name="table-number" required placeholder="T-12">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer"
                    style="margin-top: 30px; display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" onclick="closeEntryModal()">Cancel</button>
                    <button type="button" class="btn-submit-premium" id="modal-save-btn" onclick="saveModalEntry()"
                        style="width: auto; padding: 12px 30px;">Save Changes</button>
                </div>
            </div>
        </div>

        <!-- Details Modal -->

        <!-- corrected external script filename (fix typo) -->
        <script src="../assets/Javascript/Visitor.js?v=<?php echo time(); ?>"></script>

        <script>
            // --- Entry Modal (Insert/Update) ---
            function openEntryModal(type, data = null) {
                const modal = document.getElementById('entry-modal');
                const hotelForm = document.getElementById('modal-hotel-form');
                const restForm = document.getElementById('modal-restaurant-form');
                const titleEl = document.getElementById('entry-modal-title');
                const saveBtn = document.getElementById('modal-save-btn');

                modal.style.display = 'block';
                hotelForm.style.display = 'none';
                restForm.style.display = 'none';

                if (type === 'hotel') {
                    hotelForm.style.display = 'block';
                    titleEl.innerHTML = '<i class="fas fa-hotel" style="color:#3b82f6;"></i> ' + (data ? 'Update Guest Info' : 'Guest Registration');
                    saveBtn.textContent = data ? 'Save Changes' : 'Complete Registration';
                    if (data) {
                        hotelForm.action.value = 'update';
                        hotelForm.entry_id.value = data.id;
                        hotelForm.full_name.value = data.name;
                        hotelForm.room_number.value = data.room;
                    } else {
                        hotelForm.reset();
                        hotelForm.action.value = 'insert';
                    }
                } else if (type === 'restaurant') {
                    restForm.style.display = 'block';
                    titleEl.innerHTML = '<i class="fas fa-utensils" style="color:#3b82f6;"></i> ' + (data ? 'Update Diner Info' : 'Diner Information');
                    saveBtn.textContent = data ? 'Save Changes' : 'Register Table';
                    if (data) {
                        restForm.action.value = 'update';
                        restForm.entry_id.value = data.id;
                        restForm.elements['visitor-name'].value = data.name;
                        restForm.elements['party-size'].value = data.party;
                        restForm.elements['table-number'].value = data.table;
                    } else {
                        restForm.reset();
                        restForm.action.value = 'insert';
                    }
                }
            }

            function closeEntryModal() {
                document.getElementById('entry-modal').style.display = 'none';
            }

            function saveModalEntry() {
                // Placeholder for AJAX logic or form submission
                const hotelForm = document.getElementById('modal-hotel-form');
                const restForm = document.getElementById('modal-restaurant-form');
                const type = hotelForm.style.display === 'block' ? 'hotel' : 'restaurant';

                // Logic would go here to communicate with Visitor.js or backend
                console.log('Saving ' + type + ' entry...');

                alert('Entry has been processed successfully!');
                closeEntryModal();
                // Refresh tables if Visitor.js function exists
                if (typeof loadCurrentVisitors === 'function') loadCurrentVisitors();
            }

            // Modal Helper Functions

            // --- Details Modal ---
            function showDetailsModal(title, content) {
                document.getElementById('details-modal-title').innerText = title;
                document.getElementById('details-modal-body').innerHTML = content;
                document.getElementById('details-modal').style.display = 'block';
            }

            function closeDetailsModal() {
                document.getElementById('details-modal').style.display = 'none';
            }

            // Modal Helper Functions
            function showConfirmationModal(message, callback) {
                document.getElementById('confirmation-message').innerText = message;
                const modal = document.getElementById('confirmation-modal');
                modal.style.display = 'block';

                const confirmBtn = document.getElementById('confirm-btn');
                // Remove existing event listeners to prevent multiple firings
                const newBtn = confirmBtn.cloneNode(true);
                confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);

                newBtn.onclick = function () {
                    callback();
                    closeConfirmationModal();
                };
            }

            function closeConfirmationModal() {
                document.getElementById('confirmation-modal').style.display = 'none';
            }

            // Close modal when clicking outside
            window.onclick = function (event) {
                const confirmModal = document.getElementById('confirmation-modal');
                const detailsModal = document.getElementById('details-modal');

                if (event.target == confirmModal) {
                    closeConfirmationModal();
                }
                if (event.target == detailsModal) {
                    closeDetailsModal();
                }
            }

            // SHOW/HIDE PAGES from sidebar / top nav
            function showPage(pageId) {
                // hide all pages
                document.querySelectorAll('.page').forEach(function (p) { p.classList.remove('active'); });
                // show requested page
                const page = document.getElementById(pageId);
                if (page) page.classList.add('active');

                // Update top nav items
                document.querySelectorAll('.module-header .top-nav span, .module-header .top-nav a').forEach(function (el) {
                    if (el.getAttribute('data-page') === pageId) {
                        el.className = 'nav-pill';
                    } else if (el.className === 'nav-pill') {
                        el.className = 'nav-item-top';
                    }
                });

                // Update sidebar items
                document.querySelectorAll('.module-sidebar .sidebar-item').forEach(function (item) {
                    if (item.getAttribute('data-sidebar') === pageId) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });
            }

            // Activate inner tab (tabName e.g. "hotel-checkin" => content id "hotel-checkin-tab")
            function activateTab(tabName) {
                document.querySelectorAll('.tabs .tab').forEach(function (t) { t.classList.remove('active'); });
                document.querySelectorAll('.tab-content').forEach(function (tc) { tc.classList.remove('active'); });
                const tab = document.querySelector('.tabs .tab[data-tab="' + tabName + '"]');
                if (tab) tab.classList.add('active');
                const tc = document.getElementById(tabName + '-tab');
                if (tc) tc.classList.add('active');

                // If Visitor.js is loaded, trigger data refresh
                if (typeof loadCurrentVisitors === 'function' && tabName.includes('visitors')) {
                    loadCurrentVisitors();
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Force Hide desktop-only elements on mobile
                function forceHideMobile() {
                    if (window.innerWidth <= 1200) {
                        document.querySelectorAll('.desktop-only').forEach(el => {
                            el.style.setProperty('display', 'none', 'important');
                        });
                    } else {
                        document.querySelectorAll('.desktop-only').forEach(el => {
                            if (el.tagName === 'SPAN' || el.classList.contains('nav-item-top') || el.classList.contains('nav-pill')) {
                                el.style.setProperty('display', 'inline-block', 'important');
                            } else {
                                el.style.setProperty('display', 'flex', 'important');
                            }
                        });
                    }
                }
                window.addEventListener('resize', forceHideMobile);
                forceHideMobile();

                // Update module clock
                function updateModuleClock() {
                    const clockEl = document.getElementById('module-clock');
                    const dateEl = document.getElementById('module-date');
                    if (clockEl && dateEl) {
                        const now = new Date();
                        clockEl.textContent = now.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        dateEl.textContent = now.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                    }
                }
                setInterval(updateModuleClock, 1000);
                updateModuleClock();

                // Sidebar / nav click handling — attach only to elements that have data-page
                document.querySelectorAll('a[data-page]').forEach(function (el) {
                    el.addEventListener('click', function (e) {
                        const requested = this.getAttribute('data-page');
                        if (!requested) return; // do nothing if no data-page
                        e.preventDefault(); // only prevent when we handle SPA navigation

                        // If data-page is compound like "hotel-checkin" show main page 'hotel' and activate tab
                        if (requested.indexOf('-') !== -1) {
                            const parts = requested.split('-');
                            const main = parts[0]; // 'hotel' or 'restaurant'
                            showPage(main);
                            activateTab(requested);
                        } else {
                            // direct page id matches page div ids (dashboard, hotel, restaurant, reports, settings)
                            showPage(requested);
                        }
                    });
                });

                // Tabs click handling inside pages
                document.querySelectorAll('.tabs .tab').forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        activateTab(this.getAttribute('data-tab'));
                    });
                });

                // On load: trigger display based on existing sidebar active, otherwise default to dashboard
                var starter = document.querySelector('.sidebar-link.active') || document.querySelector('.nav-link.active');
                if (starter && starter.getAttribute('data-page')) {
                    var p = starter.getAttribute('data-page');
                    // reuse click logic to ensure inner tabs show
                    starter.click();
                } else {
                    showPage('dashboard');
                }

                // REPORT read-panel helpers (moved inside DOM ready to avoid null errors)
                function showReportRead(data) {
                    var results = document.getElementById('report-results');
                    if (results) results.style.display = 'none';
                    var panel = document.getElementById('report-read-panel');
                    if (!panel) return;
                    panel.style.display = 'block';
                    panel.setAttribute('aria-hidden', 'false');
                    document.getElementById('rp-name').textContent = data.name || '-';
                    document.getElementById('rp-venue').textContent = data.venue || '-';
                    document.getElementById('rp-checkin').textContent = data.checkin || '-';
                    document.getElementById('rp-checkout').textContent = data.checkout || '-';
                    document.getElementById('rp-notes').textContent = data.notes || '-';
                }

                var backBtn = document.getElementById('rp-back-btn');
                if (backBtn) {
                    backBtn.addEventListener('click', function () {
                        var panel = document.getElementById('report-read-panel');
                        if (panel) {
                            panel.style.display = 'none';
                            panel.setAttribute('aria-hidden', 'true');
                        }
                        var results = document.getElementById('report-results');
                        if (results) results.style.display = '';
                    });
                }

                var reportData = document.getElementById('report-data');
                if (reportData) {
                    reportData.addEventListener('click', function (e) {
                        var target = e.target;
                        if (target.classList && target.classList.contains('view-btn')) {
                            var row = target.closest('[data-item]');
                            var payload = row ? JSON.parse(row.getAttribute('data-item')) : {};
                            showReportRead(payload);
                        }
                    });
                }
            });
        </script>
    </body>

</html>