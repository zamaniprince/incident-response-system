# 🛡️ Incident Response System

A comprehensive web-based incident response and management system built with PHP, MySQL, and modern CSS. This system allows organizations to efficiently report, track, and manage security incidents with role-based access control.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## 📋 Table of Contents
         
- [Features](#features)
- [System Architecture](#system-architecture)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Database Schema](#database-schema)
- [Security Features](#security-features)
- [Screenshots](#screenshots)
- [Contributing](#contributing)
- [License](#license)

## ✨ Features

### Public Features
- 📝 **Anonymous Incident Reporting** - Public users can report security incidents without authentication
- 🔍 **Incident Tracking** - Track incident status using a unique tracking ID
- 🎨 **Modern UI** - Beautiful dark blue themed interface with responsive design

### User Features (Authenticated)
- 👤 **User Dashboard** - Personalized dashboard showing all user's incident reports
- 📊 **Status Monitoring** - Real-time updates on incident investigation progress
- 📈 **Timeline View** - Visual timeline showing incident progression
- 🔔 **Detailed Reports** - View complete incident details, assigned personnel, and resolutions

### Admin/Manager Features
- 👥 **User Management** - Full CRUD operations for user accounts
- 🎭 **Role Management** - Assign roles (Admin, Manager, User)
- 📋 **Incident Management** - View, update, and resolve all incidents
- 📊 **Analytics Dashboard** - Comprehensive statistics and insights
- 🔄 **Status Updates** - Change incident status (Open → In Progress → Resolved)
- 👨‍💼 **Assignment System** - Assign incidents to specific team members

## 🏗️ System Architecture

### Technology Stack
- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 8.0+
- **Database:** MySQL 5.7+
- **Server:** Apache (WAMP/XAMPP/LAMP)

### Project Structure
```
incident_response_system/
│
├── config.php              # Database configuration
├── index.php               # Public landing page
├── styles.css              # Global stylesheet
│
├── Public Pages/
│   ├── public_report.php   # Public incident reporting
│   ├── track_incident.php  # Public incident tracking
│   ├── login.php           # User authentication
│   └── register.php        # User registration
│
├── User Pages/
│   ├── user_dashboard.php        # User's personal dashboard
│   ├── user_incident_detail.php  # Detailed incident view for users
│   └── user_profile.php          # User profile management
│
├── Admin/Manager Pages/
│   ├── admin_dashboard.php       # Admin overview dashboard
│   ├── view_incidents.php        # Manage all incidents
│   ├── view_incident_detail.php  # Full incident details
│   ├── update_incident.php       # Update incident status
│   ├── manage_users.php          # User management
│   └── dashboard.php             # Analytics dashboard
│
├── Auth/
│   └── logout.php          # Session cleanup
│
└── database.sql            # Database setup script
```

## 🚀 Installation

### Prerequisites
- WAMP/XAMPP/LAMP server installed
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache web server

### Step-by-Step Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/zamaniprince/incident-response-system.git
   ```

2. **Move to Web Directory**
   ```bash
   # For WAMP
   mv incident-response-system C:/wamp64/www/

   # For XAMPP
   mv incident-response-system C:/xampp/htdocs/

   # For Linux (LAMP)
   sudo mv incident-response-system /var/www/html/
   ```

3. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `incident_response_db`
   - Import the `database.sql` file

4. **Configure Database Connection**
   - Copy `config.example.php` to `config.php` (or create it)
   - Update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'incident_response_db');
   ```

5. **Set Permissions** (Linux/Mac only)
   ```bash
   chmod 755 incident-response-system
   chmod 644 incident-response-system/*.php
   ```

6. **Access the Application**
   - Open browser and navigate to: `http://localhost/incident-response-system/`

## ⚙️ Configuration

### Database Configuration

Edit `config.php` to match your environment:

```php
<?php
define('DB_HOST', 'localhost');     // Database host
define('DB_USER', 'root');          // Database username
define('DB_PASS', '');              // Database password
define('DB_NAME', 'incident_response_db');  // Database name

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
```


## 📖 Usage

### For Public Users

1. **Report an Incident**
   - Visit the homepage
   - Click "Report an Incident"
   - Fill in the incident details
   - Submit and save your Tracking ID

2. **Track an Incident**
   - Click "Track Your Report"
   - Enter your Tracking ID
   - View incident status and progress

### For Registered Users

1. **Register an Account**
   - Click "Register" from login page
   - Fill in registration details
   - Submit and login

2. **View Your Incidents**
   - Login to your account
   - Access your personal dashboard
   - View all your reported incidents
   - Check status updates and resolutions

### For Admins/Managers

1. **Manage Incidents**
   - Login with admin/manager credentials
   - View all incidents in the system
   - Update incident status
   - Assign incidents to team members

2. **Manage Users**
   - Access "Manage Users" page
   - View all registered users
   - Change user roles
   - Activate/deactivate accounts
   - Delete users

3. **View Analytics**
   - Access the Analytics Dashboard
   - View incident statistics
   - Monitor severity breakdown
   - Track category trends

## 🗄️ Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('Admin', 'Manager', 'User') DEFAULT 'User',
    phone VARCHAR(20),
    department VARCHAR(100),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);
```

### Incidents Table
```sql
CREATE TABLE incidents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    severity ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
    category VARCHAR(100) NOT NULL,
    status ENUM('Open', 'In Progress', 'Resolved') DEFAULT 'Open',
    reporter_name VARCHAR(100) NOT NULL,
    reporter_email VARCHAR(150) NOT NULL,
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);
```

## 🔒 Security Features

- ✅ **Password Hashing** - bcrypt password hashing
- ✅ **SQL Injection Protection** - Prepared statements for all queries
- ✅ **Session Management** - Secure session handling
- ✅ **Role-Based Access Control** - Granular permissions system
- ✅ **XSS Protection** - HTML entity encoding for all user inputs
- ✅ **Authentication Checks** - Page-level authentication guards
- ✅ **Password Strength Validation** - Minimum 8 characters requirement

## 📱 Responsive Design

The system is fully responsive and works seamlessly on:
- 💻 Desktop computers
- 📱 Mobile phones
- 📱 Tablets
- 🖥️ Large displays

## 🎨 Design System

### Color Palette
```css
--primary-blue: #1a2332      /* Main background */
--secondary-blue: #243447    /* Cards and containers */
--accent-blue: #2c4261       /* Borders and accents */
--light-blue: #3a5270        /* Hover states */
--highlight-blue: #4a6fa5    /* Primary buttons */
--success: #4ade80           /* Success states */
--warning: #fbbf24           /* Warning states */
--danger: #ef4444            /* Error/critical states */
```

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

- Zamani Prince - ** - (https://github.com/zamaniprince)

## 🙏 Acknowledgments

- Designed for DBMS project coursework
- Built with modern web development best practices
- Inspired by professional incident response systems


## 🔮 Future Enhancements

- [ ] Email notifications for incident updates
- [ ] File attachment support
- [ ] Advanced search and filtering
- [ ] Export reports to PDF
- [ ] Two-factor authentication
- [ ] API for third-party integrations
- [ ] Real-time notifications
- [ ] Incident escalation workflow

---

**⭐ If you find this project useful, please give it a star!**