# PitWall F1 CMS - Setup Guide

## Overview

This is a custom PHP/MySQL Content Management System (CMS) built for your PitWall F1 website. It allows you to manage articles, race results, and championship standings through an admin panel.

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.2 or higher
- Apache/Nginx web server
- PHP Extensions: PDO, PDO_MySQL

## Installation Steps

### 1. Database Setup

1. Access your MySQL database (via phpMyAdmin, command line, or cPanel)

2. Create a new database:
   ```sql
   CREATE DATABASE pitwall_f1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Import the database schema:
   - Open `database.sql` file
   - Execute all SQL statements in your database
   - This will create all necessary tables and insert sample data

### 2. Configuration

1. Open `includes/config.php`

2. Update the database credentials:
   ```php
   define('DB_HOST', 'localhost');        // Your database host
   define('DB_NAME', 'pitwall_f1');       // Your database name
   define('DB_USER', 'your_username');    // Your database username
   define('DB_PASS', 'your_password');    // Your database password
   ```

3. Update the site URL:
   ```php
   define('SITE_URL', 'https://your-domain.com');
   ```

### 3. File Permissions

Create an `uploads` directory for images:
```bash
mkdir uploads
chmod 755 uploads
```

### 4. Access the Admin Panel

1. Navigate to: `https://your-domain.com/admin/`

2. Default login credentials:
   - **Username:** `admin`
   - **Password:** `admin123`

3. **IMPORTANT:** Change the default password immediately after first login!

## Admin Panel Features

### Dashboard (`/admin/`)
- Overview statistics
- Recent articles
- Upcoming races
- Quick actions

### Articles Management (`/admin/articles.php`)
- Create, edit, and delete articles
- Categories: News, Analyses, Interviews, Technology, Teams
- Rich text editor
- Featured article flag
- Publish/Draft status
- SEO-friendly slugs

### Races Management (`/admin/races.php`)
- Add race details (date, circuit, location)
- Enter race results
- Manage pole positions and fastest laps
- Race highlights

### Standings Management (`/admin/standings.php`)
- Update driver championship standings
- Update constructor championship standings
- Automated points calculation from race results

### Drivers & Teams
- Manage driver information
- Manage constructor (team) information
- Link drivers to teams

## File Structure

```
PitWall_website/
├── admin/              # Admin panel
│   ├── assets/         # Admin CSS/JS
│   ├── includes/       # Admin header/footer
│   ├── index.php       # Dashboard
│   ├── login.php       # Login page
│   ├── articles.php    # Article management
│   ├── races.php       # Race management
│   └── standings.php   # Standings management
├── api/                # API endpoints for frontend
├── includes/           # PHP utilities
│   ├── config.php      # Configuration
│   ├── db.php          # Database connection
│   └── auth.php        # Authentication
├── uploads/            # Uploaded images
├── database.sql        # Database schema
└── [frontend files]    # Your HTML/CSS/JS
```

## Security Best Practices

1. **Change Default Password:**
   - Login as admin
   - Go to Users section
   - Change the default password

2. **Disable Error Display in Production:**
   - In `includes/config.php`, change:
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

3. **Secure File Uploads:**
   - Only allow specific image types
   - Validate file sizes
   - Store uploads outside webroot if possible

4. **Database Security:**
   - Use strong database passwords
   - Limit database user privileges
   - Keep database credentials secure

5. **HTTPS:**
   - Always use HTTPS in production
   - Update `SITE_URL` to use `https://`

## Next Steps

The CMS foundation is complete with:
- ✅ Database schema
- ✅ Authentication system
- ✅ Admin dashboard
- ✅ Login/logout functionality

**To complete the CMS, we still need to build:**
1. Article management interface (CRUD operations)
2. Race management interface
3. Standings management interface
4. API endpoints to serve data to your frontend pages
5. Update frontend HTML pages to fetch data from API

Would you like me to continue building these remaining features?

## Troubleshooting

### Cannot connect to database
- Check database credentials in `includes/config.php`
- Verify database exists and user has permissions
- Check if MySQL service is running

### Cannot login
- Verify `database.sql` was imported correctly
- Check if `users` table exists
- Try resetting password manually in database

### Blank pages
- Check PHP error logs
- Enable error display temporarily
- Verify all required PHP extensions are installed

### Permission denied errors
- Check file permissions on `uploads/` directory
- Verify web server user has write access

## Support

For issues or questions:
1. Check error logs: `/admin/` will display errors if enabled
2. Review database connection in `includes/db.php`
3. Verify PHP version meets requirements

## License

Custom built for PitWall F1 Website.
