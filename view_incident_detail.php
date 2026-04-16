<?php
require_once 'config.php';

$incident_id = $_GET['id'] ?? 0;

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM incidents WHERE id = ?");
$stmt->bind_param("i", $incident_id);
$stmt->execute();
$result = $stmt->get_result();
$incident = $result->fetch_assoc();

if (!$incident) {
    header("Location: view_incidents.php");
    exit;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Details - IRS</title>
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
                <li><a href="view_incidents.php">View Incidents</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title" style="margin: 0;">Incident Details</h1>
            <a href="view_incidents.php" class="btn btn-secondary">Back to List</a>
        </div>

        <div class="form-container">
            <div style="display: grid; gap: 1.5rem;">
                <div>
                    <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem;">Incident ID</h3>
                    <p style="font-size: 1.2rem;">#<?php echo htmlspecialchars($incident['id']); ?></p>
                </div>

                <div>
                    <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem;">Title</h3>
                    <p style="font-size: 1.2rem;"><?php echo htmlspecialchars($incident['title']); ?></p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    <div>
                        <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem;">Status</h3>
                        <span class="badge badge-<?php echo $incident['status'] == 'Open' ? 'open' : ($incident['status'] == 'Resolved' ? 'resolved' : 'progress'); ?>">
                            <?php echo htmlspecialchars($incident['status']); ?>
                        </span>
                    </div>

                    <div>
                        <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem;">Severity</h3>
                        <span class="badge badge-<?php echo strtolower($incident['severity']); ?>">
                            <?php echo htmlspecialchars($incident['severity']); ?>
                        </span>
                    </div>

                    <div>
                        <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem;">Category</h3>
                        <p><?php echo htmlspecialchars($incident['category']); ?></p>
                    </div>
                </div>

                <div>
                    <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem;">Description</h3>
                    <p style="line-height: 1.8; background: var(--accent-blue); padding: 1rem; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($incident['description'])); ?>
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    <div>
                        <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem;">Reporter Name</h3>
                        <p><?php echo htmlspecialchars($incident['reporter_name']); ?></p>
                    </div>

                    <div>
                        <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem;">Reporter Email</h3>
                        <p><?php echo htmlspecialchars($incident['reporter_email']); ?></p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    <div>
                        <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem;">Date Reported</h3>
                        <p><?php echo date('F d, Y g:i A', strtotime($incident['created_at'])); ?></p>
                    </div>

                    <div>
                        <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem;">Last Updated</h3>
                        <p><?php echo date('F d, Y g:i A', strtotime($incident['updated_at'])); ?></p>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <a href="update_incident.php?id=<?php echo $incident['id']; ?>" class="btn btn-primary">Update Status</a>
                    <a href="view_incidents.php" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 ZORA Inc. All rights reserved.</p>
    </footer>
</body>
</html>