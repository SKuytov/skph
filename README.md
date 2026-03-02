# SKuytov Photography - Client Photo Portal

A self-hosted photo gallery portal for photographers. Each client gets a unique access code to view and download their photos from a photoshoot session.

## Features

- **Client Access Portal** - Clients enter their unique code to see only their photos
- **Admin Dashboard** - Manage sessions, upload photos, track views
- **Photo Gallery** - Beautiful responsive grid with lightbox viewer
- **Download** - Individual photo or "Download All" as ZIP
- **Drag & Drop Upload** - Easy photo uploading in admin panel
- **Auto Thumbnails** - Automatic thumbnail generation
- **Session Management** - Enable/disable galleries, toggle downloads
- **Access Logging** - Track when clients view their galleries
- **Mobile Responsive** - Works on all devices

## Requirements

- PHP 7.4+ (PHP 8.x recommended)
- MySQL 5.7+
- GD Library (for thumbnails)
- ZipArchive extension (for download all)

## Installation

1. **Upload files** to your web hosting (superhosting.bg)

2. **Create the database** tables:
   - Open phpMyAdmin
   - Select your database
   - Go to SQL tab
   - Paste contents of `db/schema.sql` and execute

3. **Configure the database** connection:
   - Edit `includes/config.php`
   - Update `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
   - Update `SITE_URL` with your domain

4. **Set directory permissions**:
   ```
   chmod 755 uploads/
   chmod 755 uploads/thumbnails/
   ```

5. **Login to admin**:
   - Go to `yourdomain.com/admin/login.php`
   - Default: `admin` / `admin123`
   - **Change your password immediately!**

## Usage

1. Login to admin panel
2. Create a new photo session (client name, date, etc.)
3. Upload photos to the session
4. Share the auto-generated access code with your client
5. Client visits your site, enters the code, and views/downloads photos

## File Structure

```
├── admin/                  # Admin panel
│   ├── index.php          # Dashboard
│   ├── login.php          # Admin login
│   ├── logout.php         # Admin logout
│   ├── session_create.php # Create session
│   ├── session_edit.php   # Edit session
│   ├── session_photos.php # Upload/manage photos
│   ├── session_delete.php # Delete session
│   └── change_password.php# Change password
├── assets/css/            # Stylesheets
│   ├── style.css          # Client portal styles
│   └── admin.css          # Admin panel styles
├── db/
│   └── schema.sql         # Database schema
├── includes/
│   ├── config.php         # Configuration
│   ├── Database.php       # Database class
│   └── functions.php      # Helper functions
├── uploads/               # Photo storage
│   └── thumbnails/        # Auto-generated thumbs
├── index.php              # Client access page
├── gallery.php            # Photo gallery
├── download.php           # Download handler
├── logout.php             # Client logout
├── .htaccess              # Security rules
└── README.md
```

## Security

- Passwords hashed with bcrypt
- PDO prepared statements (SQL injection safe)
- XSS protection via htmlspecialchars
- Directory listing disabled
- Protected includes/db directories
- Access codes use cryptographic random generation

## License

Private - SKuytov Photography
