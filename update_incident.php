<?php
require_once 'config.php';

$incident_id = $_GET['id'] ?? 0;
$message = '';
$messageType = '';

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


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (!empty($status)) {
        $stmt = $conn->prepare("UPDATE incidents SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $status, $incident_id);
        
        if ($stmt->execute()) {
            $message = "Incident status updated successfully!";
            $messageType = "success";
            
            
            $stmt2 = $conn->prepare("SELECT * FROM incidents WHERE id = ?");
            $stmt2->bind_param("i", $incident_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $incident = $result2->fetch_assoc();
            $stmt2->close();
        } else {
            $message = "Error updating incident: " . $conn->error;
            $messageType = "error";
        }
        
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Incident - IRS</title>
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
                <li><a href="report_incident.php">Report Incident</a></li>
                <li><a href="view_incidents.php">View Incidents</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1 class="section-title">Update Incident Status</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <div style="background: var(--accent-blue); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3 style="color: var(--highlight-blue); margin-bottom: 1rem;">Incident Information</h3>
                <p><strong>ID:</strong> #<?php echo htmlspecialchars($incident['id']); ?></p>
                <p><strong>Title:</strong> <?php echo htmlspecialchars($incident['title']); ?></p>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($incident['category']); ?></p>
                <p><strong>Current Status:</strong> 
                    <span class="badge badge-<?php echo $incident['status'] == 'Open' ? 'open' : ($incident['status'] == 'Resolved' ? 'resolved' : 'progress'); ?>">
                        <?php echo htmlspecialchars($incident['status']); ?>
                    </span>
                </p>
            </div>

            <form method="POST" action="update_incident.php?id=<?php echo $incident_id; ?>">
                <div class="form-group">
                    <label for="status">Update Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Open" <?php echo $incident['status'] == 'Open' ? 'selected' : ''; ?>>Open</option>
                        <option value="In Progress" <?php echo $incident['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Resolved" <?php echo $incident['status'] == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Update Notes (Optional)</label>
                    <textarea id="notes" name="notes" class="form-control" placeholder="Add any notes about this status update..."></textarea>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Status</button>
                    <a href="view_incident_detail.php?id=<?php echo $incident_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 ZORA Inc. All rights reserved.</p>
    </footer>
</body>
</html>