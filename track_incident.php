<?php
require_once 'config.php';

$incident = null;
$error = '';
$incident_id = $_GET['id'] ?? $_POST['tracking_id'] ?? '';

if (!empty($incident_id)) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM incidents WHERE id = ?");
    $stmt->bind_param("i", $incident_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $incident = $result->fetch_assoc();
    } else {
        $error = "No incident found with Tracking ID: #" . htmlspecialchars($incident_id);
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Incident - IRS</title>
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
                <li><a href="track_incident.php" class="active">Track Report</a></li>               
                <li><a href="login.php">Staff Login</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1 class="section-title">Track Your Incident Report</h1>

        <?php if (!$incident): ?>
            <div class="form-container" style="max-width: 600px; margin: 0 auto;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🔍</div>
                    <p style="color: var(--text-muted);">Enter your Tracking ID to check the status of your incident report</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="track_incident.php">
                    <div class="form-group">
                        <label for="tracking_id">Tracking ID</label>
                        <input type="text" id="tracking_id" name="tracking_id" class="form-control" 
                               required placeholder="Enter your tracking ID (e.g., 123)" 
                               value="<?php echo htmlspecialchars($incident_id); ?>">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Track Incident</button>
                    </div>
                </form>

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--accent-blue); text-align: center;">
                    <p style="color: var(--text-muted); margin-bottom: 1rem;">Don't have a Tracking ID?</p>
                    <a href="public_report.php" class="btn btn-secondary">Report New Incident</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Status Banner -->
            <?php if ($incident['status'] == 'Resolved'): ?>
                <div class="alert alert-success" style="margin-bottom: 2rem;">
                    <strong>✅ Incident Resolved</strong><br>
                    This incident has been successfully resolved. Thank you for reporting it!
                </div>
            <?php elseif ($incident['status'] == 'In Progress'): ?>
                <div class="alert" style="background: rgba(251, 191, 36, 0.1); border-color: var(--warning); color: var(--warning); margin-bottom: 2rem;">
                    <strong>🔄 Investigation in Progress</strong><br>
                    Our security team is actively working on this incident.
                </div>
            <?php else: ?>
                <div class="alert" style="background: rgba(239, 68, 68, 0.1); border-color: var(--danger); color: var(--danger); margin-bottom: 2rem;">
                    <strong>⏳ Awaiting Review</strong><br>
                    Your incident report has been received and is awaiting review by our security team.
                </div>
            <?php endif; ?>

            <div class="form-container">
                <!-- Basic Information -->
                <div style="background: var(--accent-blue); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                        <div>
                            <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem; font-size: 0.9rem;">Tracking ID</h3>
                            <p style="font-size: 1.3rem; font-weight: bold;">#<?php echo htmlspecialchars($incident['id']); ?></p>
                        </div>
                        <div>
                            <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem; font-size: 0.9rem;">Current Status</h3>
                            <span class="badge badge-<?php echo $incident['status'] == 'Open' ? 'open' : ($incident['status'] == 'Resolved' ? 'resolved' : 'progress'); ?>">
                                <?php echo htmlspecialchars($incident['status']); ?>
                            </span>
                        </div>
                        <div>
                            <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem; font-size: 0.9rem;">Severity Level</h3>
                            <span class="badge badge-<?php echo strtolower($incident['severity']); ?>">
                                <?php echo htmlspecialchars($incident['severity']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Incident Details -->
                <div style="margin-bottom: 2rem;">
                    <h2 style="color: var(--highlight-blue); margin-bottom: 1rem;">Incident Information</h2>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <h3 style="color: var(--text-light); margin-bottom: 0.5rem;">Title</h3>
                        <p style="font-size: 1.1rem;"><?php echo htmlspecialchars($incident['title']); ?></p>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <h3 style="color: var(--text-light); margin-bottom: 0.5rem;">Category</h3>
                        <p><?php echo htmlspecialchars($incident['category']); ?></p>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <h3 style="color: var(--text-light); margin-bottom: 0.5rem;">Description</h3>
                        <div style="background: var(--accent-blue); padding: 1.5rem; border-radius: 8px; line-height: 1.8;">
                            <?php echo nl2br(htmlspecialchars($incident['description'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div style="margin-bottom: 2rem;">
                    <h2 style="color: var(--highlight-blue); margin-bottom: 1rem;">Progress Timeline</h2>
                    
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker" style="background: var(--highlight-blue);"></div>
                            <div class="timeline-content">
                                <h4>Incident Reported</h4>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">
                                    <?php echo date('F d, Y g:i A', strtotime($incident['created_at'])); ?>
                                </p>
                                <p>Your incident report was successfully submitted to our security team.</p>
                            </div>
                        </div>

                        <?php if ($incident['status'] != 'Open'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker" style="background: var(--warning);"></div>
                            <div class="timeline-content">
                                <h4>Investigation Started</h4>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">
                                    <?php echo date('F d, Y g:i A', strtotime($incident['updated_at'])); ?>
                                </p>
                                <p>Our security team has begun investigating this incident.</p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($incident['status'] == 'Resolved'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker" style="background: var(--success);"></div>
                            <div class="timeline-content">
                                <h4>Incident Resolved</h4>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">
                                    <?php echo date('F d, Y g:i A', strtotime($incident['updated_at'])); ?>
                                </p>
                                <p>The incident has been successfully resolved and appropriate security measures have been implemented.</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="text-align: center; padding: 2rem; background: var(--accent-blue); border-radius: 8px;">
                    <h3 style="color: var(--highlight-blue); margin-bottom: 1rem;">Need Assistance?</h3>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
                        If you have questions about this incident, please contact our support team.
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="mailto:zamanimuntanga@gmail.com" class="btn btn-secondary">Contact Support</a>
                        <a href="track_incident.php" class="btn btn-primary">Track Another Incident</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2025 ZORA Inc. All rights reserved.</p>
    </footer>
</body>
</html>