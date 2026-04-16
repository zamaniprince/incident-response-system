<?php
require_once 'config.php';

$message = '';
$messageType = '';
$tracking_id = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $severity = $_POST['severity'] ?? '';
    $category = $_POST['category'] ?? '';
    $reporter_name = $_POST['reporter_name'] ?? '';
    $reporter_email = $_POST['reporter_email'] ?? '';
    
    if (!empty($title) && !empty($description) && !empty($severity) && !empty($category)) {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("INSERT INTO incidents (title, description, severity, category, reporter_name, reporter_email, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Open', NOW())");
        $stmt->bind_param("ssssss", $title, $description, $severity, $category, $reporter_name, $reporter_email);
        
        if ($stmt->execute()) {
            $tracking_id = $stmt->insert_id;
            $message = "Incident reported successfully! Your tracking ID is: <strong>#" . $tracking_id . "</strong><br>Save this ID to track your incident status.";
            $messageType = "success";
        } else {
            $message = "Error reporting incident: " . $conn->error;
            $messageType = "error";
        }
        
        $stmt->close();
        $conn->close();
    } else {
        $message = "Please fill in all required fields.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Incident - IRS</title>
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
                <li><a href="track_incident.php">Track Report</a></li>
                <li><a href="login.php">Staff Login</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1 class="section-title">Report Security Incident</h1>
        
        <div class="form-container" style="max-width: 900px; margin: 0 auto 2rem;">
            <div style="background: rgba(74, 222, 128, 0.1); border: 2px solid var(--success); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3 style="color: var(--success); margin-bottom: 1rem;">📋 Before You Report</h3>
                <ul style="line-height: 1.8; color: var(--text-muted);">
                    <li>Provide as much detail as possible to help us investigate</li>
                    <li>Save your <strong>Tracking ID</strong> after submitting</li>
                    <li>You can use your Tracking ID to monitor progress</li>
                    <li>All reports are confidential and handled by our security team</li>
                </ul>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="max-width: 900px; margin: 0 auto 2rem;">
                <?php echo $message; ?>
                <?php if ($tracking_id): ?>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid currentColor;">
                        <a href="track_incident.php?id=<?php echo $tracking_id; ?>" class="btn btn-secondary">Track This Incident</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-container" style="max-width: 900px; margin: 0 auto;">
            <form method="POST" action="public_report.php">
                <div class="form-group">
                    <label for="title">Incident Title *</label>
                    <input type="text" id="title" name="title" class="form-control" required placeholder="Brief description of the incident">
                </div>

                <div class="form-group">
                    <label for="category">Incident Category *</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">Select Category</option>
                        <option value="Security Breach">Security Breach</option>
                        <option value="Data Loss">Data Loss</option>
                        <option value="System Outage">System Outage</option>
                        <option value="Unauthorized Access">Unauthorized Access</option>
                        <option value="Malware">Malware</option>
                        <option value="Phishing">Phishing</option>
                        <option value="DDoS Attack">DDoS Attack</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="severity">Severity Level *</label>
                    <select id="severity" name="severity" class="form-control" required>
                        <option value="">Select Severity</option>
                        <option value="Low">Low - Minor impact, no immediate action required</option>
                        <option value="Medium">Medium - Moderate impact, attention needed</option>
                        <option value="High">High - Significant impact, urgent attention</option>
                        <option value="Critical">Critical - Severe impact, immediate action</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Detailed Description *</label>
                    <textarea id="description" name="description" class="form-control" required placeholder="Provide a detailed description of the incident, including what happened, when it occurred, and any relevant information"></textarea>
                </div>

                <div class="form-group">
                    <label for="reporter_name">Your Name *</label>
                    <input type="text" id="reporter_name" name="reporter_name" class="form-control" required placeholder="Full name">
                </div>

                <div class="form-group">
                    <label for="reporter_email">Your Email *</label>
                    <input type="email" id="reporter_email" name="reporter_email" class="form-control" required placeholder="email@example.com">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Submit Incident Report</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 ZORABIT Inc. All rights reserved.</p>
    </footer>
</body>
</html>