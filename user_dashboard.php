<?php
require_once 'config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_email = $_SESSION['email'];
$conn = getDBConnection();


$stmt = $conn->prepare("SELECT * FROM incidents WHERE reporter_email = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$incidents = $stmt->get_result();


$stats_stmt = $conn->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved
    FROM incidents WHERE reporter_email = ?");
$stats_stmt->bind_param("s", $user_email);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

$stmt->close();
$stats_stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - IRS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>🛡️ Incident Response System</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="user_dashboard.php" class="active">My Dashboard</a></li>
                <li><a href="report_incident.php">Report Incident</a></li>
                <li><a href="user_profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! 👋</h1>
            <p style="color: var(--text-muted);">Here's an overview of your incident reports</p>
        </div>

        <!-- User Statistics -->
        <div class="stats-grid" style="margin-bottom: 3rem;">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Reports</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--danger);"><?php echo $stats['open']; ?></div>
                <div class="stat-label">Open</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--warning);"><?php echo $stats['in_progress']; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--success);"><?php echo $stats['resolved']; ?></div>
                <div class="stat-label">Resolved</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="form-container" style="margin-bottom: 3rem;">
            <h2 style="margin-bottom: 1rem; color: var(--highlight-blue);">Quick Actions</h2>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="report_incident.php" class="btn btn-primary">➕ Report New Incident</a>
                <a href="user_profile.php" class="btn btn-secondary">👤 View Profile</a>
            </div>
        </div>

        
        <h2 class="section-title">My Incident Reports</h2>

        <?php if ($incidents && $incidents->num_rows > 0): ?>
            <div style="display: grid; gap: 1.5rem;">
                <?php while ($incident = $incidents->fetch_assoc()): ?>
                    <div class="incident-card">
                        <div class="incident-card-header">
                            <div>
                                <h3 style="color: var(--text-light); margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($incident['title']); ?>
                                </h3>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">
                                    Reported on <?php echo date('F d, Y g:i A', strtotime($incident['created_at'])); ?>
                                </p>
                            </div>
                            <div style="text-align: right;">
                                <span class="badge badge-<?php echo $incident['status'] == 'Open' ? 'open' : ($incident['status'] == 'Resolved' ? 'resolved' : 'progress'); ?>">
                                    <?php echo htmlspecialchars($incident['status']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="incident-card-body">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Category</p>
                                    <p style="font-weight: 600;"><?php echo htmlspecialchars($incident['category']); ?></p>
                                </div>
                                <div>
                                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Severity</p>
                                    <span class="badge badge-<?php echo strtolower($incident['severity']); ?>">
                                        <?php echo htmlspecialchars($incident['severity']); ?>
                                    </span>
                                </div>
                                <div>
                                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Incident ID</p>
                                    <p style="font-weight: 600;">#<?php echo $incident['id']; ?></p>
                                </div>
                            </div>

                            <div style="background: var(--accent-blue); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Description</p>
                                <p style="line-height: 1.6;">
                                    <?php 
                                    $desc = htmlspecialchars($incident['description']);
                                    echo strlen($desc) > 200 ? substr($desc, 0, 200) . '...' : $desc;
                                    ?>
                                </p>
                            </div>

                            <?php if ($incident['status'] == 'Resolved'): ?>
                                <div class="alert alert-success" style="margin-bottom: 1rem;">
                                    ✅ This incident has been resolved on <?php echo date('F d, Y', strtotime($incident['updated_at'])); ?>
                                </div>
                            <?php elseif ($incident['status'] == 'In Progress'): ?>
                                <div class="alert" style="background: rgba(251, 191, 36, 0.1); border-color: var(--warning); color: var(--warning); margin-bottom: 1rem;">
                                    🔄 Your incident is currently being investigated by our team
                                </div>
                            <?php else: ?>
                                <div class="alert" style="background: rgba(239, 68, 68, 0.1); border-color: var(--danger); color: var(--danger); margin-bottom: 1rem;">
                                    ⏳ Your incident is awaiting review
                                </div>
                            <?php endif; ?>

                            <div style="border-top: 1px solid var(--accent-blue); padding-top: 1rem;">
                                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Last Updated</p>
                                <p style="margin-bottom: 1rem;"><?php echo date('F d, Y g:i A', strtotime($incident['updated_at'])); ?></p>
                                <a href="user_incident_detail.php?id=<?php echo $incident['id']; ?>" class="btn btn-primary">View Full Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="form-container" style="text-align: center; padding: 3rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">📋</div>
                <h3 style="margin-bottom: 1rem;">No Incidents Reported Yet</h3>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">You haven't reported any incidents. If you notice any security issues, report them immediately.</p>
                <a href="report_incident.php" class="btn btn-primary">Report Your First Incident</a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Incident Response System. All rights reserved.</p>
    </footer>
</body>
</html>