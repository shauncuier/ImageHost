# Simple PHP Image Host

A lightweight, simple image hosting website built with PHP that provides real URLs for uploaded images. Perfect for use with Amazon flat files or any application that needs direct image URLs.

## Features

- **Simple Upload Interface**: Easy drag-and-drop file upload
- **Real URLs**: Generates actual URLs that can be used anywhere
- **Multiple Format Support**: Supports JPEG, PNG, GIF, and WebP images
- **File Size Validation**: Prevents uploads larger than 5MB
- **Image Gallery**: View all uploaded images with their URLs
- **Copy URLs**: One-click URL copying to clipboard
- **Responsive Design**: Works on desktop and mobile devices
- **Security**: Protected against malicious file uploads

## Setup Instructions

### Requirements
- PHP 7.0 or higher
- Web server (Apache/Nginx) with PHP support
- Write permissions on the server

### Installation

1. **Upload Files**: Upload all files to your web server directory
2. **Set Permissions**: Ensure the web server can create the `uploads` directory:
   ```bash
   chmod 755 .
   chmod 777 uploads/ (will be created automatically)
   ```
3. **Configure Web Server**: Ensure your web server supports PHP and .htaccess files
4. **Access**: Open your website in a browser

### Local Development

To run locally with PHP's built-in server:

```bash
php -S localhost:8000
```

Then open `http://localhost:8000` in your browser.

## Usage

### Uploading Images

1. Visit your website
2. Click "Choose File" and select an image
3. Click "Upload Image"
4. Copy the generated URL for use in Amazon flat files or other applications

### URL Format

Uploaded images get URLs in the format:
```
http://yourdomain.com/uploads/uniqueid_timestamp.extension
```

Example:
```
http://yoursite.com/uploads/507f1f77bcf86cd799439011_1640995200.jpg
```

### Amazon Flat File Usage

The generated URLs can be directly used in Amazon flat file templates:
- Product images
- Variant images
- Additional product photos

Simply copy the URL from the website and paste it into your flat file.

## File Structure

```
image-host/
├── index.php          # Main application file
├── .htaccess          # Apache configuration
├── README.md          # This file
└── uploads/           # Directory for uploaded images (auto-created)
    └── [images]       # Uploaded image files
```

## Configuration

### Upload Limits

Default limits (can be modified in .htaccess):
- Maximum file size: 10MB
- Supported formats: JPEG, PNG, GIF, WebP
- PHP execution time: 300 seconds

### Security Features

- File type validation
- File size limits
- Unique filename generation
- Protection against PHP file uploads in uploads directory
- XSS protection with HTML escaping

## Troubleshooting

### Upload Issues

1. **"File too large" error**:
   - Check your server's upload_max_filesize setting
   - Modify .htaccess values if needed

2. **Permission denied**:
   - Ensure uploads directory is writable (755 or 777)
   - Check server file permissions

3. **Images not displaying**:
   - Verify .htaccess is working
   - Check if mod_rewrite is enabled
   - Ensure proper MIME types are set

### URL Issues

1. **URLs not accessible**:
   - Check .htaccess configuration
   - Verify web server supports .htaccess
   - Ensure files are in the correct directory

2. **HTTPS/HTTP mixed content**:
   - Update the URL generation in index.php if needed
   - Consider using protocol-relative URLs

## Customization

### Changing Upload Directory

Modify the `$uploadDir` variable in index.php:
```php
$uploadDir = 'your-custom-directory/';
```

### Modifying File Size Limits

Edit the `$maxFileSize` variable in index.php:
```php
$maxFileSize = 10 * 1024 * 1024; // 10MB
```

### Adding File Types

Update the `$allowedTypes` array in index.php:
```php
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
```

## License

This project is open source. Feel free to modify and distribute as needed.

## Support

For issues or questions, check the troubleshooting section above or review the code comments in index.php.
