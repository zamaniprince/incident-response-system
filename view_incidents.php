<?php
require_once 'config.php';

$conn = getDBConnection();

// Filtering
$status_filter = $_GET['status'] ?? '';
$severity_filter = $_GET['severity'] ?? '';

$query = "SELECT * FROM incidents WHERE 1=1";

if (!empty($status_filter)) {
    $query .= " AND status = '" . $conn->real_escape_string($status_filter) . "'";
}

if (!empty($severity_filter)) {
    $query .= " AND severity = '" . $conn->real_escape_string($severity_filter) . "'";
}

$query .= " ORDER BY created_at DESC";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Incidents - IRS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>🛡️ Incident Response System</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="public_report.php">Report Incident</a></li>
                <li><a href="view_incidents.php" class="active">View Incidents</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1 class="section-title">All Incidents</h1>

        <!-- Filters -->
        <div class="form-container" style="margin-bottom: 2rem;">
            <form method="GET" action="view_incidents.php">
                <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="status">Filter by Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="Open" <?php echo $status_filter == 'Open' ? 'selected' : ''; ?>>Open</option>
                            <option value="In Progress" <?php echo $status_filter == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Resolved" <?php echo $status_filter == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="severity">Filter by Severity</label>
                        <select id="severity" name="severity" class="form-control">
                            <option value="">All Severities</option>
                            <option value="Low" <?php echo $severity_filter == 'Low' ? 'selected' : ''; ?>>Low</option>
                            <option value="Medium" <?php echo $severity_filter == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="High" <?php echo $severity_filter == 'High' ? 'selected' : ''; ?>>High</option>
                            <option value="Critical" <?php echo $severity_filter == 'Critical' ? 'selected' : ''; ?>>Critical</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Reporter</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
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
                                <td><?php echo htmlspecialchars($row['reporter_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
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
            <?php else: ?>
                <p style="text-align: center; color: var(--text-muted); padding: 2rem;">No incidents found matching the selected filters.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 ZORA Inc. All rights reserved.</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>