<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$incident_id = $_GET['id'] ?? 0;
$user_email = $_SESSION['email'];

$conn = getDBConnection();

// Get incident details (only if it belongs to the user)
$stmt = $conn->prepare("SELECT * FROM incidents WHERE id = ? AND reporter_email = ?");
$stmt->bind_param("is", $incident_id, $user_email);
$stmt->execute();
$result = $stmt->get_result();
$incident = $result->fetch_assoc();

if (!$incident) {
    header("Location: user_dashboard.php");
    exit;
}

// Get assigned user info if available
$assigned_user = null;
if ($incident['assigned_to']) {
    $user_stmt = $conn->prepare("SELECT full_name, email, department FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $incident['assigned_to']);
    $user_stmt->execute();
    $assigned_user = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();
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
                <li><a href="user_dashboard.php">My Dashboard</a></li>
                <li><a href="report_incident.php">Report Incident</a></li>
                <li><a href="user_profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title" style="margin: 0;">Incident Report Details</h1>
            <a href="user_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>

        <!-- Status Banner -->
        <?php if ($incident['status'] == 'Resolved'): ?>
            <div class="alert alert-success" style="margin-bottom: 2rem;">
                <strong>✅ Incident Resolved</strong><br>
                This incident has been successfully resolved on <?php echo date('F d, Y', strtotime($incident['updated_at'])); ?>. Thank you for reporting it!
            </div>
        <?php elseif ($incident['status'] == 'In Progress'): ?>
            <div class="alert" style="background: rgba(251, 191, 36, 0.1); border-color: var(--warning); color: var(--warning); margin-bottom: 2rem;">
                <strong>🔄 Investigation in Progress</strong><br>
                Our security team is actively working on this incident. We'll notify you once it's resolved.
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
                        <h3 style="color: var(--highlight-blue); margin-bottom: 0.5rem; font-size: 0.9rem;">Incident ID</h3>
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
                    <p style="font-size: 1.2rem;"><?php echo htmlspecialchars($incident['title']); ?></p>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color: var(--text-light); margin-bottom: 0.5rem;">Category</h3>
                    <p><?php echo htmlspecialchars($incident['category']); ?></p>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color: var(--text-light); margin-bottom: 0.5rem;">Detailed Description</h3>
                    <div style="background: var(--accent-blue); padding: 1.5rem; border-radius: 8px; line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($incident['description'])); ?>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div style="margin-bottom: 2rem;">
                <h2 style="color: var(--highlight-blue); margin-bottom: 1rem;">Timeline</h2>
                
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker" style="background: var(--highlight-blue);"></div>
                        <div class="timeline-content">
                            <h4>Incident Reported</h4>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">
                                <?php echo date('F d, Y g:i A', strtotime($incident['created_at'])); ?>
                            </p>
                            <p>You submitted this incident report to our security team.</p>
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
                            <p>Security team began investigating this incident.</p>
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
                            <p>The incident has been successfully resolved and appropriate measures have been taken.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Assigned Personnel -->
            <?php if ($assigned_user): ?>
            <div style="margin-bottom: 2rem;">
                <h2 style="color: var(--highlight-blue); margin-bottom: 1rem;">Assigned Personnel</h2>
                <div style="background: var(--accent-blue); padding: 1.5rem; border-radius: 8px;">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($assigned_user['full_name']); ?></p>
                    <p><strong>Department:</strong> <?php echo htmlspecialchars($assigned_user['department']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($assigned_user['email']); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Resolution Information -->
            <?php if ($incident['status'] == 'Resolved'): ?>
            <div style="margin-bottom: 2rem;">
                <h2 style="color: var(--highlight-blue); margin-bottom: 1rem;">Resolution Summary</h2>
                <div style="background: rgba(74, 222, 128, 0.1); border: 2px solid var(--success); padding: 1.5rem; border-radius: 8px;">
                    <h3 style="color: var(--success); margin-bottom: 1rem;">Actions Taken</h3>
                    <ul style="line-height: 1.8; padding-left: 1.5rem;">
                        <li>Security vulnerability has been patched</li>
                        <li>Affected systems have been secured</li>
                        <li>Security monitoring has been enhanced</li>
                        <li>Team has been notified of security best practices</li>
                    </ul>
                    <h3 style="color: var(--success); margin-top: 1.5rem; margin-bottom: 1rem;">Preventive Measures</h3>
                    <ul style="line-height: 1.8; padding-left: 1.5rem;">
                        <li>Additional security controls implemented</li>
                        <li>Regular security audits scheduled</li>
                        <li>Staff training on security awareness conducted</li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <!-- Contact Support -->
            <div style="background: var(--accent-blue); padding: 1.5rem; border-radius: 8px;">
                <h3 style="color: var(--highlight-blue); margin-bottom: 1rem;">Need Help?</h3>
                <p style="color: var(--text-muted); margin-bottom: 1rem;">
                    If you have any questions about this incident or need further assistance, please don't hesitate to contact our support team.
                </p>
                <a href="mailto:support@incidentresponse.com" class="btn btn-secondary">Contact Support</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Incident Response System. All rights reserved.</p>
    </footer>
</body>
</html>