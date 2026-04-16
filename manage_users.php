<?php
require_once 'config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'Admin') {
    header("Location: user_dashboard.php");
    exit;
}

$message = '';
$messageType = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? 0;
    
    $conn = getDBConnection();
    
    if ($action == 'update_role') {
        $new_role = $_POST['role'] ?? '';
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        
        if ($stmt->execute()) {
            $message = "User role updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating role: " . $conn->error;
            $messageType = "error";
        }
        $stmt->close();
    } elseif ($action == 'toggle_status') {
        $new_status = $_POST['status'] ?? '';
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $user_id);
        
        if ($stmt->execute()) {
            $message = "User status updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating status: " . $conn->error;
            $messageType = "error";
        }
        $stmt->close();
    } elseif ($action == 'delete') {
        // Prevent deleting yourself
        if ($user_id == $_SESSION['user_id']) {
            $message = "You cannot delete your own account!";
            $messageType = "error";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $message = "User deleted successfully!";
                $messageType = "success";
            } else {
                $message = "Error deleting user: " . $conn->error;
                $messageType = "error";
            }
            $stmt->close();
        }
    }
    
    $conn->close();
}


$conn = getDBConnection();
$users_query = "SELECT u.*, 
                COUNT(i.id) as incident_count 
                FROM users u 
                LEFT JOIN incidents i ON u.email = i.reporter_email 
                GROUP BY u.id 
                ORDER BY u.created_at DESC";
$users = $conn->query($users_query);


$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$active_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'Active'")->fetch_assoc()['count'];
$admin_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'Admin'")->fetch_assoc()['count'];
$manager_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'Manager'")->fetch_assoc()['count'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - IRS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>🛡️ Incident Response System</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="view_incidents.php">Manage Incidents</a></li>
                <li><a href="dashboard.php">Analytics</a></li>
                <li><a href="manage_users.php" class="active">Manage Users</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1 class="section-title">User Management</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        
        <div class="stats-grid" style="margin-bottom: 3rem;">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--success);"><?php echo $active_users; ?></div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--highlight-blue);"><?php echo $admin_count; ?></div>
                <div class="stat-label">Administrators</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--warning);"><?php echo $manager_count; ?></div>
                <div class="stat-label">Managers</div>
            </div>
        </div>

        
        <div class="table-container">
            <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="color: var(--highlight-blue); margin: 0;">All Registered Users</h2>
                <span style="color: var(--text-muted);">Total: <?php echo $total_users; ?> users</span>
            </div>

            <?php if ($users && $users->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Reports</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role'] == 'Admin' ? 'critical' : ($user['role'] == 'Manager' ? 'high' : 'low'); ?>">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['department'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['status'] == 'Active' ? 'resolved' : 'open'; ?>">
                                        <?php echo htmlspecialchars($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $user['incident_count']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo $user['role']; ?>', '<?php echo $user['status']; ?>')" 
                                                class="btn btn-primary btn-small">Edit</button>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <button onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" 
                                                    class="btn btn-danger btn-small">Delete</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-muted); padding: 2rem;">No users found.</p>
            <?php endif; ?>
        </div>
    </div>

    
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 2000; padding: 2rem;">
        <div style="max-width: 500px; margin: 50px auto; background: var(--secondary-blue); border-radius: 12px; padding: 2rem; border: 1px solid var(--accent-blue);">
            <h2 style="color: var(--highlight-blue); margin-bottom: 1.5rem;">Edit User</h2>
            
            <form method="POST" action="manage_users.php" id="editForm">
                <input type="hidden" name="action" value="update_role">
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="edit_username" class="form-control" readonly style="background: var(--accent-blue);">
                </div>

                <div class="form-group">
                    <label for="edit_role">Role</label>
                    <select id="edit_role" name="role" class="form-control" required>
                        <option value="User">User</option>
                        <option value="Manager">Manager</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Role</button>
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>

            <hr style="border-color: var(--accent-blue); margin: 1.5rem 0;">

            <form method="POST" action="manage_users.php">
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="user_id" id="status_user_id">
                
                <div class="form-group">
                    <label for="edit_status">Account Status</label>
                    <select id="edit_status" name="status" class="form-control" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-warning">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    
    <form method="POST" action="manage_users.php" id="deleteForm" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="user_id" id="delete_user_id">
    </form>

    <footer class="footer">
        <p>&copy; 2025 Incident Response System. All rights reserved.</p>
    </footer>

    <script>
        function openEditModal(userId, username, role, status) {
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('status_user_id').value = userId;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_status').value = status;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function confirmDelete(userId, username) {
            if (confirm('Are you sure you want to delete user "' + username + '"? This action cannot be undone.')) {
                document.getElementById('delete_user_id').value = userId;
                document.getElementById('deleteForm').submit();
            }
        }

        
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>