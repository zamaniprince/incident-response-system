<?php
require_once 'config.php';

$conn = getDBConnection();


$total_incidents = $conn->query("SELECT COUNT(*) as count FROM incidents")->fetch_assoc()['count'];
$open_incidents = $conn->query("SELECT COUNT(*) as count FROM incidents WHERE status = 'Open'")->fetch_assoc()['count'];
$in_progress = $conn->query("SELECT COUNT(*) as count FROM incidents WHERE status = 'In Progress'")->fetch_assoc()['count'];
$resolved_incidents = $conn->query("SELECT COUNT(*) as count FROM incidents WHERE status = 'Resolved'")->fetch_assoc()['count'];


$severity_stats = [];
$severity_result = $conn->query("SELECT severity, COUNT(*) as count FROM incidents GROUP BY severity");
while ($row = $severity_result->fetch_assoc()) {
    $severity_stats[$row['severity']] = $row['count'];
}


$category_stats = [];
$category_result = $conn->query("SELECT category, COUNT(*) as count FROM incidents GROUP BY category ORDER BY count DESC LIMIT 5");
while ($row = $category_result->fetch_assoc()) {
    $category_stats[$row['category']] = $row['count'];
}


$recent_incidents = $conn->query("SELECT * FROM incidents ORDER BY created_at DESC LIMIT 5");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - IRS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>ZORA Incident Response System</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="public_report.php" class="active">Report Incident</a></li>
                <li><a href="view_incidents.php">View Incidents</a></li>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1 class="section-title">Analytics Dashboard</h1>

        
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

        
        <div style="margin-bottom: 3rem;">
            <h2 class="section-title">Incidents by Severity</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" style="color: #3b82f6;"><?php echo $severity_stats['Low'] ?? 0; ?></div>
                    <div class="stat-label">Low</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: var(--warning);"><?php echo $severity_stats['Medium'] ?? 0; ?></div>
                    <div class="stat-label">Medium</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #f97316;"><?php echo $severity_stats['High'] ?? 0; ?></div>
                    <div class="stat-label">High</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: var(--danger);"><?php echo $severity_stats['Critical'] ?? 0; ?></div>
                    <div class="stat-label">Critical</div>
                </div>
            </div>
        </div>

        
        <?php if (count($category_stats) > 0): ?>
        <div style="margin-bottom: 3rem;">
            <h2 class="section-title">Top Incident Categories</h2>
            <div class="form-container">
                <?php foreach ($category_stats as $category => $count): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="font-weight: 600;"><?php echo htmlspecialchars($category); ?></span>
                            <span style="color: var(--highlight-blue);"><?php echo $count; ?> incidents</span>
                        </div>
                        <div style="background: var(--accent-blue); height: 10px; border-radius: 5px; overflow: hidden;">
                            <div style="background: var(--highlight-blue); height: 100%; width: <?php echo ($count / $total_incidents) * 100; ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        
        <div>
            <h2 class="section-title">Recent Incidents</h2>
            <div class="table-container">
                <?php if ($recent_incidents && $recent_incidents->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent_incidents->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($row['severity']); ?>">
                                            <?php echo htmlspecialchars($row['severity']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $row['status'] == 'Open' ? 'open' : ($row['status'] == 'Resolved' ? 'resolved' : 'progress'); ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a href="view_incident_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-small">View</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 2rem;">No incidents found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 ZORA Inc. All rights reserved.</p>
    </footer>
</body>
</html>