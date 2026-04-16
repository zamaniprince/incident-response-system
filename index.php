<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZoraBit Incident Response System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>🛡️ZORABIT IRS</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="public_report.php">Report Incident</a></li>
                <li><a href="login.php">Log in</a></li>
        </div>
    </nav>

    <div class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Welcome to ZORA Incident Response System</h1>
            <p class="hero-subtitle">Streamline your security incident reporting and management</p>
            <div class="hero-buttons">
                <a href="public_report.php" class="btn btn-primary">Report New Incident</a>
                <a href="view_incidents.php" class="btn btn-secondary">View All Incidents</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3>Easy Reporting</h3>
                <p>Quickly report security incidents with our intuitive interface</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👁️</div>
                <h3>Real-time Tracking</h3>
                <p>Monitor incident status and resolution progress in real-time</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Analytics Dashboard</h3>
                <p>Get insights with comprehensive incident statistics</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Secure & Reliable</h3>
                <p>Your incident data is stored securely with complete confidentiality</p>
            </div>
        </div>

        <div class="stats-section">
            <h2 class="section-title">System Overview</h2>
            <div class="stats-grid">
                <?php
                $conn = getDBConnection();
                
                
                $total_query = "SELECT COUNT(*) as total FROM incidents";
                $total_result = $conn->query($total_query);
                $total = $total_result->fetch_assoc()['total'];
                
                
                $pending_query = "SELECT COUNT(*) as pending FROM incidents WHERE status = 'Open'";
                $pending_result = $conn->query($pending_query);
                $pending = $pending_result->fetch_assoc()['pending'];
                
                
                $resolved_query = "SELECT COUNT(*) as resolved FROM incidents WHERE status = 'Resolved'";
                $resolved_result = $conn->query($resolved_query);
                $resolved = $resolved_result->fetch_assoc()['resolved'];
                
                $conn->close();
                ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total; ?></div>
                    <div class="stat-label">Total Incidents</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pending; ?></div>
                    <div class="stat-label">Open Cases</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $resolved; ?></div>
                    <div class="stat-label">Resolved Cases</div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 ZORA Inc. All rights reserved.</p>
    </footer>
</body>
</html>