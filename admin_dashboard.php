<?php
require_once 'config.php';

// Check if user is logged in, admin or manager
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Manager')) {
    header("Location: login.php");
    exit;
}

$conn = getDBConnection();

// Get statistics
$total_incidents = $conn->query("SELECT COUNT(*) as count FROM incidents")->fetch_assoc()['count'];
$open_incidents = $conn->query("SELECT COUNT(*) as count FROM incidents WHERE status = 'Open'")->fetch_assoc()['count'];
$in_progress = $conn->query("SELECT COUNT(*) as count FROM incidents WHERE status = 'In Progress'")->fetch_assoc()['count'];
$resolved_incidents = $conn->query("SELECT COUNT(*) as count FROM incidents WHERE status = 'Resolved'")->fetch_assoc()['count'];

// Get all incidents
$incidents = $conn->query("SELECT i.*, u.full_name as assigned_name FROM incidents i 
                           LEFT JOIN users u ON i.assigned_to = u.id 
                           ORDER BY i.created_at DESC LIMIT 10");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - IRS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>🛡️ Incident Response System</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="admin_dashboard.php" class="active">Admin Dashboard</a></li>
                <li><a href="view_incidents.php">All Incidents</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
 
    <div class="container">
        <div class="welcome-section">
            <h1>Admin Dashboard</h1>
            <p style="color: var(--text-muted);">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid" style="margin-bottom: 3rem;">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_incidents; ?></div>
                <div class="stat-label">Total Incidents</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--danger);"><?php echo $open_incidents; ?></div>
                <div class="stat-label">Open</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--warning);"><?php echo $in_progress; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--success);"><?php echo $resolved_incidents; ?></div>
                <div class="stat-label">Resolved</div>
            </div>
        </div>

        <!-- Recent Incidents -->
        <h2 class="section-title">Recent Incidents</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Reporter</th>
                        <th>Category</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $incidents->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['reporter_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($row['severity']); ?>">
                                    <?php echo $row['severity']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $row['status'] == 'Open' ? 'open' : ($row['status'] == 'Resolved' ? 'resolved' : 'progress'); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo $row['assigned_name'] ?? 'Unassigned'; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view_incident_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-small">View</a>
                                    <a href="update_incident.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-small">Update</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 ZORA Inc Incident Response System. All rights reserved.</p>
    </footer>
</body>
</html>