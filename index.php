<?php
session_start();

// Simple authentication - change these credentials
$valid_username = 'admin';
$valid_password = 'password@123';

// Check if user is trying to login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $login_error = 'Invalid username or password!';
    }
}

// Check if user is trying to logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle image management actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['logged_in'])) {
    // Delete image
    if (isset($_POST['delete_image'])) {
        $imageToDelete = $_POST['delete_image'];
        $imagePath = $uploadDir . basename($imageToDelete);
        
        if (file_exists($imagePath) && is_file($imagePath)) {
            if (unlink($imagePath)) {
                $uploadMessage = 'Image deleted successfully!';
            } else {
                $uploadMessage = 'Error: Failed to delete image.';
            }
        } else {
            $uploadMessage = 'Error: Image not found.';
        }
        
        // Redirect to prevent resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Rename image
    if (isset($_POST['rename_image']) && isset($_POST['new_name'])) {
        $oldFilename = $_POST['rename_image'];
        $newBaseName = trim($_POST['new_name']);
        
        if (!empty($newBaseName)) {
            $oldPath = $uploadDir . basename($oldFilename);
            $fileExtension = pathinfo($oldFilename, PATHINFO_EXTENSION);
            
            // Clean new filename for SEO
            $cleanBaseName = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $newBaseName);
            $cleanBaseName = preg_replace('/-+/', '-', $cleanBaseName);
            $cleanBaseName = trim($cleanBaseName, '-');
            
            // Check for duplicates and add number suffix if needed
            $newFileName = $cleanBaseName . '.' . $fileExtension;
            $newPath = $uploadDir . $newFileName;
            $counter = 1;
            
            while (file_exists($newPath) && $newPath !== $oldPath) {
                $newFileName = $cleanBaseName . '_' . $counter . '.' . $fileExtension;
                $newPath = $uploadDir . $newFileName;
                $counter++;
            }
            
            if (file_exists($oldPath) && is_file($oldPath)) {
                if (rename($oldPath, $newPath)) {
                    $uploadMessage = 'Image renamed successfully!';
                } else {
                    $uploadMessage = 'Error: Failed to rename image.';
                }
            } else {
                $uploadMessage = 'Error: Original image not found.';
            }
        } else {
            $uploadMessage = 'Error: New name cannot be empty.';
        }
        
        // Redirect to prevent resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Simple Image Host - Login</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .login-container {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                width: 100%;
                max-width: 400px;
                text-align: center;
            }
            
            .login-title {
                color: #2d3748;
                margin-bottom: 30px;
                font-size: 2rem;
                font-weight: 700;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            
            .login-subtitle {
                color: #4a5568;
                margin-bottom: 30px;
                font-size: 1rem;
            }
            
            .login-form {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            
            .form-group {
                text-align: left;
            }
            
            .form-label {
                display: block;
                margin-bottom: 8px;
                color: #4a5568;
                font-weight: 500;
            }
            
            .form-input {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid rgba(102, 126, 234, 0.3);
                border-radius: 8px;
                font-size: 16px;
                transition: all 0.3s ease;
                background: rgba(255, 255, 255, 0.8);
            }
            
            .form-input:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            
            .login-btn {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 14px 32px;
                border: none;
                border-radius: 12px;
                cursor: pointer;
                font-size: 16px;
                font-weight: 600;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            }
            
            .login-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
            }
            
            .error-message {
                background: rgba(245, 101, 101, 0.1);
                color: #c53030;
                padding: 12px;
                border-radius: 8px;
                border: 1px solid rgba(245, 101, 101, 0.3);
                margin-bottom: 20px;
            }
            
            .default-credentials {
                margin-top: 20px;
                padding: 15px;
                background: rgba(102, 126, 234, 0.1);
                border-radius: 8px;
                border: 1px solid rgba(102, 126, 234, 0.3);
            }
            
            .default-credentials h4 {
                color: #4a5568;
                margin-bottom: 10px;
                font-size: 0.9rem;
            }
            
            .credential-item {
                display: flex;
                justify-content: space-between;
                margin-bottom: 5px;
                font-size: 0.9rem;
                color: #4a5568;
            }
            
            @media (max-width: 480px) {
                .login-container {
                    padding: 30px 20px;
                }
                
                .login-title {
                    font-size: 1.5rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1 class="login-title">🔐 Image Host</h1>
            <p class="login-subtitle">Please login to continue</p>
            
            <?php if (isset($login_error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-input" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required autocomplete="current-password">
                </div>
                
                <button type="submit" name="login" class="login-btn">Login</button>
            </form>
            
            <div class="security-note">
                <p style="color: #4a5568; font-size: 0.9rem; font-style: italic;">
                    🔒 Secure access required
                </p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle file upload (both single and bulk)
$uploadMessage = '';
$uploadedImageUrl = '';
$uploadedUrls = [];
$uploadStats = ['success' => 0, 'failed' => 0, 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    $files = $_FILES['images'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    // Handle multiple files
    if (is_array($files['name'])) {
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            // Skip empty uploads
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            
            $fileName = $files['name'][$i];
            $fileTmpName = $files['tmp_name'][$i];
            $fileSize = $files['size'][$i];
            $fileError = $files['error'][$i];
            $fileType = $files['type'][$i];
            
            // Check for upload errors
            if ($fileError !== UPLOAD_ERR_OK) {
                $uploadStats['failed']++;
                $uploadStats['errors'][] = "Error uploading {$fileName}: Upload error code {$fileError}";
                continue;
            }
            
            // Validate file type
            if (!in_array($fileType, $allowedTypes)) {
                $uploadStats['failed']++;
                $uploadStats['errors'][] = "Error uploading {$fileName}: Only JPEG, PNG, GIF, and WebP images are allowed";
                continue;
            }
            
            // Validate file size
            if ($fileSize > $maxFileSize) {
                $uploadStats['failed']++;
                $uploadStats['errors'][] = "Error uploading {$fileName}: File size must be less than 5MB";
                continue;
            }
            
            // Generate SEO-friendly filename
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $baseName = pathinfo($fileName, PATHINFO_FILENAME);
            
            // Clean filename for SEO (remove special characters, keep hyphens and underscores)
            $cleanBaseName = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $baseName);
            $cleanBaseName = preg_replace('/-+/', '-', $cleanBaseName); // Remove multiple consecutive hyphens
            $cleanBaseName = trim($cleanBaseName, '-'); // Remove leading/trailing hyphens
            
            // Check for duplicates and add number suffix if needed
            $newFileName = $cleanBaseName . '.' . $fileExtension;
            $targetPath = $uploadDir . $newFileName;
            $counter = 1;
            
            while (file_exists($targetPath)) {
                $newFileName = $cleanBaseName . '_' . $counter . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFileName;
                $counter++;
            }
            
            // Move uploaded file
            if (move_uploaded_file($fileTmpName, $targetPath)) {
                $uploadStats['success']++;
                $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $targetPath;
                $uploadedUrls[] = $url;
                
                // For single file compatibility
                if ($uploadStats['success'] === 1) {
                    $uploadedImageUrl = $url;
                }
            } else {
                $uploadStats['failed']++;
                $uploadStats['errors'][] = "Error uploading {$fileName}: Failed to move uploaded file";
            }
        }
        
        // Generate summary message
        if ($uploadStats['success'] > 0 && $uploadStats['failed'] === 0) {
            $uploadMessage = "Successfully uploaded {$uploadStats['success']} image(s)!";
        } elseif ($uploadStats['success'] > 0 && $uploadStats['failed'] > 0) {
            $uploadMessage = "Uploaded {$uploadStats['success']} image(s), {$uploadStats['failed']} failed. Check details below.";
        } elseif ($uploadStats['failed'] > 0) {
            $uploadMessage = "Failed to upload {$uploadStats['failed']} image(s). Check details below.";
        }
    } 
    // Handle single file (backward compatibility)
    else {
        $file = $files;
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Validate file type
            if (!in_array($file['type'], $allowedTypes)) {
                $uploadMessage = 'Error: Only JPEG, PNG, GIF, and WebP images are allowed.';
            }
            // Validate file size
            elseif ($file['size'] > $maxFileSize) {
                $uploadMessage = 'Error: File size must be less than 5MB.';
            }
            else {
                // Generate SEO-friendly filename
                $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
                
                // Clean filename for SEO (remove special characters, keep hyphens and underscores)
                $cleanBaseName = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $baseName);
                $cleanBaseName = preg_replace('/-+/', '-', $cleanBaseName); // Remove multiple consecutive hyphens
                $cleanBaseName = trim($cleanBaseName, '-'); // Remove leading/trailing hyphens
                
                // Check for duplicates and add number suffix if needed
                $newFileName = $cleanBaseName . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFileName;
                $counter = 1;
                
                while (file_exists($targetPath)) {
                    $newFileName = $cleanBaseName . '_' . $counter . '.' . $fileExtension;
                    $targetPath = $uploadDir . $newFileName;
                    $counter++;
                }
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $uploadMessage = 'Image uploaded successfully!';
                    $uploadedImageUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $targetPath;
                    $uploadStats['success'] = 1;
                } else {
                    $uploadMessage = 'Error: Failed to upload image.';
                }
            }
        } else {
            $uploadMessage = 'Error: ' . $file['error'];
        }
    }
}

// Get all uploaded images
$uploadedImages = [];
if (file_exists($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && is_file($uploadDir . $file)) {
            $uploadedImages[] = [
                'filename' => $file,
                'url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $uploadDir . $file,
                'path' => $uploadDir . $file
            ];
        }
    }
    // Sort by newest first
    usort($uploadedImages, function($a, $b) {
        return filemtime($b['path']) - filemtime($a['path']);
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Image Host</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        h1 {
            text-align: center;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 40px;
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .upload-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 40px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .upload-mode-toggle {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .mode-btn {
            background: rgba(102, 126, 234, 0.1);
            color: #4a5568;
            border: 2px solid rgba(102, 126, 234, 0.3);
            padding: 10px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .mode-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
        
        .mode-btn:hover:not(.active) {
            background: rgba(102, 126, 234, 0.2);
        }
        
        .upload-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        
        .drag-drop-area {
            width: 100%;
            max-width: 500px;
            height: 200px;
            border: 3px dashed #cbd5e0;
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            position: relative;
            overflow: hidden;
        }
        
        .drag-drop-area:hover,
        .drag-drop-area.drag-over {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .drag-drop-area.drag-over {
            border-color: #48bb78;
            background: linear-gradient(135deg, rgba(72, 187, 120, 0.2) 0%, rgba(56, 178, 172, 0.2) 100%);
        }
        
        .file-input {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .upload-text {
            color: #4a5568;
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .upload-subtext {
            color: #718096;
            font-size: 0.9rem;
        }
        
        .upload-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        
        .upload-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .progress-bar {
            width: 100%;
            max-width: 500px;
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 20px;
            display: none;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .message {
            padding: 16px 24px;
            margin: 20px 0;
            border-radius: 12px;
            text-align: center;
            font-weight: 500;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .success {
            background: linear-gradient(135deg, rgba(72, 187, 120, 0.1) 0%, rgba(56, 178, 172, 0.1) 100%);
            color: #2f855a;
            border: 1px solid rgba(72, 187, 120, 0.3);
        }
        
        .error {
            background: linear-gradient(135deg, rgba(245, 101, 101, 0.1) 0%, rgba(237, 100, 166, 0.1) 100%);
            color: #c53030;
            border: 1px solid rgba(245, 101, 101, 0.3);
        }
        
        .url-display {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            word-break: break-all;
            font-family: 'Monaco', 'Menlo', monospace;
            border: 1px solid rgba(102, 126, 234, 0.3);
        }
        
        .copy-btn {
            background: linear-gradient(135deg, #38b2ac 0%, #319795 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-left: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(56, 178, 172, 0.3);
        }
        
        .copy-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(56, 178, 172, 0.4);
        }
        
        .file-info {
            display: none;
            background: rgba(102, 126, 234, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: left;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        
        .file-info.show {
            display: block;
            animation: slideIn 0.3s ease;
        }
        
        .file-info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            color: #4a5568;
            font-size: 0.9rem;
        }
        
        .selected-files-list {
            width: 100%;
            max-width: 500px;
            max-height: 200px;
            overflow-y: auto;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 8px;
            padding: 10px;
            display: none;
        }
        
        .selected-files-list.show {
            display: block;
        }
        
        .selected-file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            margin-bottom: 5px;
            background: white;
            border-radius: 6px;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        
        .selected-file-info {
            flex: 1;
            text-align: left;
        }
        
        .selected-file-name {
            font-weight: 500;
            color: #4a5568;
            font-size: 0.9rem;
        }
        
        .selected-file-size {
            color: #718096;
            font-size: 0.8rem;
        }
        
        .remove-file-btn {
            background: #f56565;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .bulk-urls-display {
            background: rgba(102, 126, 234, 0.1);
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            border: 1px solid rgba(102, 126, 234, 0.3);
        }
        
        .bulk-urls-display h4 {
            color: #4a5568;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .bulk-url-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .urls-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .url-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin-bottom: 8px;
            background: white;
            border-radius: 6px;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        
        .url-text {
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 0.8rem;
            color: #4a5568;
            word-break: break-all;
            flex: 1;
            margin-right: 10px;
        }
        
        .copy-btn-small {
            background: linear-gradient(135deg, #38b2ac 0%, #319795 100%);
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .error-details {
            background: rgba(245, 101, 101, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid rgba(245, 101, 101, 0.3);
        }
        
        .error-details h4 {
            color: #c53030;
            margin-bottom: 10px;
        }
        
        .error-details ul {
            list-style: none;
            padding: 0;
        }
        
        .error-details li {
            color: #c53030;
            padding: 5px 0;
            border-bottom: 1px solid rgba(245, 101, 101, 0.2);
        }
        
        .error-details li:last-child {
            border-bottom: none;
        }
        
        .images-section h2 {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 30px;
            font-size: 1.8rem;
            font-weight: 600;
            text-align: center;
            position: relative;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }
        
        .images-section h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }
        
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }
        
        .image-item {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .image-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.2);
        }
        
        .image-item img {
            max-width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .image-item:hover img {
            transform: scale(1.02);
        }
        
        .image-url {
            background: rgba(102, 126, 234, 0.1);
            padding: 12px;
            border-radius: 8px;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 11px;
            word-break: break-all;
            margin-bottom: 15px;
            color: #4a5568;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        
        .image-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(102, 126, 234, 0.1);
        }
        
        .image-title {
            font-weight: 600;
            color: #2d3748;
            font-size: 1rem;
            flex: 1;
            text-align: left;
            word-break: break-word;
        }
        
        .image-actions {
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.3);
            padding: 6px 8px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .edit-btn:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateY(-1px);
        }
        
        .delete-btn:hover {
            background: rgba(245, 101, 101, 0.2);
            border-color: rgba(245, 101, 101, 0.4);
            transform: translateY(-1px);
        }
        
        .image-details {
            text-align: center;
        }
        
        .image-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding: 8px 12px;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 6px;
            font-size: 0.85rem;
        }
        
        .image-size {
            color: #4a5568;
            font-weight: 500;
        }
        
        .image-date {
            color: #718096;
        }
        
        .image-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 15px;
        }
        
        .view-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        .view-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .copy-btn {
            font-size: 0.85rem;
            padding: 8px 16px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            margin: 10% auto;
            padding: 30px;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(102, 126, 234, 0.1);
        }
        
        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #718096;
            transition: color 0.3s ease;
        }
        
        .close-btn:hover {
            color: #2d3748;
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .modal-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(102, 126, 234, 0.3);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }
        
        .modal-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .modal-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .modal-btn-secondary {
            background: rgba(102, 126, 234, 0.1);
            color: #4a5568;
            border: 1px solid rgba(102, 126, 234, 0.3);
        }
        
        .modal-btn:hover {
            transform: translateY(-1px);
        }
        
        .confirm-modal .modal-content {
            text-align: center;
        }
        
        .confirm-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .confirm-message {
            color: #4a5568;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .no-images {
            text-align: center;
            color: #718096;
            font-style: italic;
            padding: 60px 20px;
            font-size: 1.1rem;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 10px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .upload-section {
                padding: 25px;
            }
            
            .drag-drop-area {
                height: 150px;
            }
            
            .image-grid {
                grid-template-columns: 1fr;
            }
            
            .bulk-url-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <h1 style="margin: 0;">Simple Image Host</h1>
            <div style="display: flex; align-items: center; gap: 15px;">
                <span style="color: rgba(255, 255, 255, 0.85); font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="?logout=1" style="background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">Logout</a>
            </div>
        </div>
        
        <!-- Upload Section -->
        <div class="upload-section">
            <form method="POST" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                <div class="drag-drop-area" id="dragDropArea">
                    <div class="upload-icon">📁</div>
                    <div class="upload-text" id="uploadText">Drag & Drop your images here</div>
                    <div class="upload-subtext" id="uploadSubtext">or click to browse (JPEG, PNG, GIF, WebP - Max 5MB each)</div>
                    <input type="file" name="images[]" accept="image/*" multiple class="file-input" id="fileInput">
                </div>
                
                <div class="file-info" id="fileInfo">
                    <div class="file-info-item">
                        <span>Files Selected:</span>
                        <span id="fileCount">0</span>
                    </div>
                    <div class="file-info-item">
                        <span>Total Size:</span>
                        <span id="totalSize">0 Bytes</span>
                    </div>
                </div>
                
                <div class="selected-files-list" id="selectedFilesList"></div>
                
                <div class="progress-bar" id="progressBar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                
                <button type="submit" class="upload-btn" id="uploadBtn">Upload Images</button>
            </form>
            
            <?php if ($uploadMessage): ?>
                <div class="message <?php echo strpos($uploadMessage, 'Error') === 0 ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($uploadMessage); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($uploadStats['errors'])): ?>
                <div class="error-details">
                    <h4>Upload Errors:</h4>
                    <ul>
                        <?php foreach ($uploadStats['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($uploadedUrls)): ?>
                <div class="bulk-urls-display">
                    <h4>Uploaded Images URLs:</h4>
                    <div class="bulk-url-actions">
                        <button onclick="copyAllUrls()" class="copy-btn">Copy All URLs</button>
                        <button onclick="downloadUrlsList()" class="copy-btn">Download URLs List</button>
                    </div>
                    <div class="urls-list" id="uploadedUrlsList">
                        <?php foreach ($uploadedUrls as $index => $url): ?>
                            <div class="url-item">
                                <span class="url-text"><?php echo htmlspecialchars($url); ?></span>
                                <button onclick="copyToClipboard('<?php echo htmlspecialchars($url); ?>')" class="copy-btn-small">Copy</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif ($uploadedImageUrl): ?>
                <div class="url-display">
                    <strong>Image URL:</strong><br>
                    <span id="imageUrl"><?php echo htmlspecialchars($uploadedImageUrl); ?></span>
                    <button onclick="copyUrl()" class="copy-btn">Copy URL</button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Images Section -->
        <div class="images-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2 style="margin: 0;">Uploaded Images</h2>
                <div class="sort-controls">
                    <label for="sortBy" style="color: rgba(255, 255, 255, 0.85); font-weight: 500; margin-right: 10px; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);">Sort by:</label>
                    <select id="sortBy" onchange="sortImages()" style="padding: 8px 12px; border: 2px solid rgba(102, 126, 234, 0.3); border-radius: 6px; background: white; color: #4a5568; font-size: 0.9rem;">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="name-asc">Name A-Z</option>
                        <option value="name-desc">Name Z-A</option>
                        <option value="size-large">Largest File</option>
                        <option value="size-small">Smallest File</option>
                    </select>
                </div>
            </div>
            
            <?php if (empty($uploadedImages)): ?>
                <div class="no-images">
                    No images uploaded yet. Upload your first image above!
                </div>
            <?php else: ?>
                <div class="image-grid" id="imageGrid">
                    <?php foreach ($uploadedImages as $image): ?>
                        <div class="image-item" id="image-<?php echo md5($image['filename']); ?>">
                            <div class="image-header">
                                <div class="image-title" id="title-<?php echo md5($image['filename']); ?>">
                                    <?php echo htmlspecialchars(pathinfo($image['filename'], PATHINFO_FILENAME)); ?>
                                </div>
                                <div class="image-actions">
                                    <button onclick="editImageName('<?php echo htmlspecialchars($image['filename']); ?>', '<?php echo md5($image['filename']); ?>')" class="action-btn edit-btn" title="Edit Name">
                                        ✏️
                                    </button>
                                    <button onclick="deleteImage('<?php echo htmlspecialchars($image['filename']); ?>')" class="action-btn delete-btn" title="Delete Image">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                            
                            <img src="<?php echo htmlspecialchars($image['url']); ?>" alt="<?php echo htmlspecialchars($image['filename']); ?>">
                            
                            <div class="image-details">
                                <div class="image-info">
                                    <span class="image-size"><?php echo number_format(filesize($image['path']) / 1024, 2); ?> KB</span>
                                    <span class="image-date"><?php echo date('M j, Y', filemtime($image['path'])); ?></span>
                                </div>
                                
                                <div class="image-url">
                                    <?php echo htmlspecialchars($image['url']); ?>
                                </div>
                                
                                <div class="image-buttons">
                                    <button onclick="copyToClipboard('<?php echo htmlspecialchars($image['url']); ?>')" class="copy-btn">
                                        📋 Copy URL
                                    </button>
                                    <button onclick="openImageInNewTab('<?php echo htmlspecialchars($image['url']); ?>')" class="view-btn">
                                        👁️ View
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // UI Elements
        const fileInput = document.getElementById('fileInput');
        const uploadText = document.getElementById('uploadText');
        const uploadSubtext = document.getElementById('uploadSubtext');
        const uploadBtn = document.getElementById('uploadBtn');
        const fileInfo = document.getElementById('fileInfo');
        const selectedFilesList = document.getElementById('selectedFilesList');
        const dragDropArea = document.getElementById('dragDropArea');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');
        const uploadForm = document.getElementById('uploadForm');
        const fileCount = document.getElementById('fileCount');
        const totalSize = document.getElementById('totalSize');
        
        // Drag and Drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dragDropArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dragDropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dragDropArea.addEventListener(eventName, unhighlight, false);
        });
        
        dragDropArea.addEventListener('drop', handleDrop, false);
        fileInput.addEventListener('change', handleFileSelect, false);
        dragDropArea.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            fileInput.click();
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        function highlight(e) {
            dragDropArea.classList.add('drag-over');
        }
        
        function unhighlight(e) {
            dragDropArea.classList.remove('drag-over');
        }
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect();
            }
        }
        
        function handleFileSelect() {
            const files = Array.from(fileInput.files);
            
            if (files.length === 0) return;
            
            updateFileInfo(files);
            displaySelectedFiles(files);
            updateDragDropArea(files);
        }
        
        function updateFileInfo(files) {
            const totalBytes = files.reduce((sum, file) => sum + file.size, 0);
            fileCount.textContent = files.length;
            totalSize.textContent = formatFileSize(totalBytes);
            fileInfo.classList.add('show');
        }
        
        function displaySelectedFiles(files) {
            selectedFilesList.innerHTML = '';
            
            files.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'selected-file-item';
                fileItem.innerHTML = `
                    <div class="selected-file-info">
                        <div class="selected-file-name">${file.name}</div>
                        <div class="selected-file-size">${formatFileSize(file.size)}</div>
                    </div>
                    <button type="button" class="remove-file-btn" onclick="removeFile(${index})">Remove</button>
                `;
                selectedFilesList.appendChild(fileItem);
            });
            
            selectedFilesList.classList.add('show');
        }
        
        function removeFile(index) {
            const dt = new DataTransfer();
            const files = Array.from(fileInput.files);
            
            files.forEach((file, i) => {
                if (i !== index) {
                    dt.items.add(file);
                }
            });
            
            fileInput.files = dt.files;
            handleFileSelect();
        }
        
        function updateDragDropArea(files) {
            const uploadIcon = dragDropArea.querySelector('.upload-icon');
            const uploadText = dragDropArea.querySelector('.upload-text');
            const uploadSubtext = dragDropArea.querySelector('.upload-subtext');
            
            uploadIcon.textContent = '✅';
            
            if (files.length === 1) {
                uploadText.textContent = 'File Selected!';
                uploadSubtext.textContent = `${files[0].name} (${formatFileSize(files[0].size)})`;
            } else {
                uploadText.textContent = `${files.length} Files Selected!`;
                uploadSubtext.textContent = `Total: ${formatFileSize(files.reduce((sum, file) => sum + file.size, 0))}`;
            }
            
            dragDropArea.style.borderColor = '#48bb78';
            dragDropArea.style.background = 'linear-gradient(135deg, rgba(72, 187, 120, 0.2) 0%, rgba(56, 178, 172, 0.2) 100%)';
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Form submission
        uploadForm.addEventListener('submit', function(e) {
            if (!fileInput.files[0]) {
                e.preventDefault();
                showNotification('Please select at least one file!', 'error');
                return;
            }
            
            const files = Array.from(fileInput.files);
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            for (let file of files) {
                if (file.size > maxSize) {
                    e.preventDefault();
                    showNotification(`File "${file.name}" is too large! Max size is 5MB.`, 'error');
                    return;
                }
                
                if (!allowedTypes.includes(file.type)) {
                    e.preventDefault();
                    showNotification(`File "${file.name}" is not a supported image type!`, 'error');
                    return;
                }
            }
            
            // Show progress bar
            progressBar.style.display = 'block';
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Uploading...';
            simulateProgress();
        });
        
        function simulateProgress() {
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                progressFill.style.width = progress + '%';
                
                if (progress >= 90) {
                    clearInterval(interval);
                }
            }, 200);
        }
        
        // Utility functions
        function showNotification(message, type) {
            const existingNotification = document.querySelector('.temp-notification');
            if (existingNotification) existingNotification.remove();
            
            const notification = document.createElement('div');
            notification.className = `message ${type} temp-notification`;
            notification.textContent = message;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '1000';
            notification.style.minWidth = '300px';
            
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 4000);
        }
        
        function copyToClipboard(text) {
            // First try the modern clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showNotification('URL copied to clipboard!', 'success');
                }).catch((err) => {
                    console.error('Clipboard API failed:', err);
                    fallbackCopyTextToClipboard(text);
                });
            } else {
                // Fallback for older browsers or non-secure contexts
                fallbackCopyTextToClipboard(text);
            }
        }
        
        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            
            // Avoid scrolling to bottom
            textArea.style.top = '0';
            textArea.style.left = '0';
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            textArea.style.pointerEvents = 'none';
            
            document.body.appendChild(textArea);
            
            try {
                textArea.focus();
                textArea.select();
                textArea.setSelectionRange(0, 99999); // For mobile devices
                
                const successful = document.execCommand('copy');
                if (successful) {
                    showNotification('URL copied to clipboard!', 'success');
                } else {
                    showNotification('Failed to copy URL. Please copy manually.', 'error');
                }
            } catch (err) {
                console.error('Fallback copy failed:', err);
                showNotification('Copy failed. Please copy manually: ' + text, 'error');
            }
            
            document.body.removeChild(textArea);
        }
        
        function copyUrl() {
            const urlElement = document.getElementById('imageUrl');
            copyToClipboard(urlElement.textContent);
        }
        
        function copyAllUrls() {
            const urlElements = document.querySelectorAll('.url-text');
            const urls = Array.from(urlElements).map(el => el.textContent).join('\n');
            copyToClipboard(urls);
        }
        
        function downloadUrlsList() {
            const urlElements = document.querySelectorAll('.url-text');
            const urls = Array.from(urlElements).map(el => el.textContent).join('\n');
            
            const blob = new Blob([urls], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'image-urls.txt';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }
        
        function resetForm() {
            fileInput.value = '';
            fileInfo.classList.remove('show');
            selectedFilesList.classList.remove('show');
            progressBar.style.display = 'none';
            uploadBtn.disabled = false;
            resetDragDropArea();
        }
        
        function resetDragDropArea() {
            const uploadIcon = dragDropArea.querySelector('.upload-icon');
            const uploadText = dragDropArea.querySelector('.upload-text');
            const uploadSubtext = dragDropArea.querySelector('.upload-subtext');
            
            uploadIcon.textContent = '📁';
            uploadText.textContent = 'Drag & Drop your images here';
            uploadSubtext.textContent = 'or click to browse (JPEG, PNG, GIF, WebP - Max 5MB each)';
            uploadBtn.textContent = 'Upload Images';
            
            dragDropArea.style.borderColor = '#cbd5e0';
            dragDropArea.style.background = 'linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%)';
        }
        
        // Image management functions
        function editImageName(filename, hashId) {
            const currentName = pathinfo(filename, 'filename');
            showEditModal(currentName, filename);
        }
        
        function deleteImage(filename) {
            showDeleteConfirmModal(filename);
        }
        
        function openImageInNewTab(url) {
            window.open(url, '_blank');
        }
        
        function pathinfo(path, option) {
            const info = {
                dirname: path.replace(/\\/g, '/').replace(/\/[^\/]*$/, '') || '.',
                basename: path.replace(/^.*[\/\\]/, ''),
                extension: path.split('.').pop(),
                filename: path.replace(/^.*[\/\\]/, '').replace(/\.[^/.]+$/, "")
            };
            
            switch(option) {
                case 'dirname': return info.dirname;
                case 'basename': return info.basename;
                case 'extension': return info.extension;
                case 'filename': return info.filename;
                default: return info;
            }
        }
        
        function showEditModal(currentName, filename) {
            const modal = createModal('edit-modal', 'Edit Image Name', `
                <div class="modal-body">
                    <input type="text" class="modal-input" id="newImageName" value="${currentName}" placeholder="Enter new name">
                </div>
                <div class="modal-footer">
                    <button class="modal-btn modal-btn-secondary" onclick="closeModal('edit-modal')">Cancel</button>
                    <button class="modal-btn modal-btn-primary" onclick="submitRename('${filename}')">Save</button>
                </div>
            `);
            
            // Focus and select the input
            setTimeout(() => {
                const input = document.getElementById('newImageName');
                input.focus();
                input.select();
                
                // Handle Enter key
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        submitRename(filename);
                    }
                });
            }, 100);
        }
        
        function showDeleteConfirmModal(filename) {
            const modal = createModal('delete-modal confirm-modal', 'Delete Image', `
                <div class="confirm-icon">🗑️</div>
                <div class="confirm-message">
                    Are you sure you want to delete this image?<br>
                    <strong>${filename}</strong><br>
                    This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button class="modal-btn modal-btn-secondary" onclick="closeModal('delete-modal')">Cancel</button>
                    <button class="modal-btn modal-btn-primary" style="background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);" onclick="submitDelete('${filename}')">Delete</button>
                </div>
            `);
        }
        
        function createModal(className, title, content) {
            // Remove existing modal if any
            const existingModal = document.querySelector('.modal');
            if (existingModal) {
                existingModal.remove();
            }
            
            const modal = document.createElement('div');
            modal.className = `modal ${className}`;
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title">${title}</div>
                        <button class="close-btn" onclick="closeModal('${className.split(' ')[0]}')">&times;</button>
                    </div>
                    ${content}
                </div>
            `;
            
            document.body.appendChild(modal);
            modal.style.display = 'block';
            
            // Close on background click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal(className.split(' ')[0]);
                }
            });
            
            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal(className.split(' ')[0]);
                }
            });
            
            return modal;
        }
        
        function closeModal(modalId) {
            const modal = document.querySelector('.modal');
            if (modal) {
                modal.style.display = 'none';
                modal.remove();
            }
        }
        
        function submitRename(filename) {
            const newName = document.getElementById('newImageName').value.trim();
            if (!newName) {
                showNotification('Please enter a valid name!', 'error');
                return;
            }
            
            // Create a form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const renameInput = document.createElement('input');
            renameInput.type = 'hidden';
            renameInput.name = 'rename_image';
            renameInput.value = filename;
            
            const newNameInput = document.createElement('input');
            newNameInput.type = 'hidden';
            newNameInput.name = 'new_name';
            newNameInput.value = newName;
            
            form.appendChild(renameInput);
            form.appendChild(newNameInput);
            document.body.appendChild(form);
            
            form.submit();
        }
        
        function submitDelete(filename) {
            // Create a form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_image';
            deleteInput.value = filename;
            
            form.appendChild(deleteInput);
            document.body.appendChild(form);
            
            form.submit();
        }
        
        // Sorting functionality
        function sortImages() {
            const sortBy = document.getElementById('sortBy').value;
            const imageGrid = document.getElementById('imageGrid');
            const imageItems = Array.from(imageGrid.children);
            
            imageItems.sort((a, b) => {
                switch(sortBy) {
                    case 'newest':
                        return getImageDate(b) - getImageDate(a);
                    case 'oldest':
                        return getImageDate(a) - getImageDate(b);
                    case 'name-asc':
                        return getImageName(a).localeCompare(getImageName(b));
                    case 'name-desc':
                        return getImageName(b).localeCompare(getImageName(a));
                    case 'size-large':
                        return getImageSize(b) - getImageSize(a);
                    case 'size-small':
                        return getImageSize(a) - getImageSize(b);
                    default:
                        return 0;
                }
            });
            
            // Clear the grid and re-append sorted items
            imageGrid.innerHTML = '';
            imageItems.forEach(item => imageGrid.appendChild(item));
            
            // Add animation effect
            imageItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.3s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 50);
            });
        }
        
        function getImageDate(imageItem) {
            const dateElement = imageItem.querySelector('.image-date');
            if (!dateElement) return 0;
            return new Date(dateElement.textContent).getTime();
        }
        
        function getImageName(imageItem) {
            const nameElement = imageItem.querySelector('.image-title');
            return nameElement ? nameElement.textContent.toLowerCase() : '';
        }
        
        function getImageSize(imageItem) {
            const sizeElement = imageItem.querySelector('.image-size');
            if (!sizeElement) return 0;
            const sizeText = sizeElement.textContent;
            return parseFloat(sizeText.replace(' KB', ''));
        }
        
        // Reset form after successful upload and redirect to prevent resubmit
        window.addEventListener('load', function() {
            const urlDisplay = document.getElementById('imageUrl') || document.getElementById('uploadedUrlsList');
            if (urlDisplay) {
                // Show the results for a moment, then redirect to clear POST data
                setTimeout(() => {
                    window.location.href = window.location.pathname;
                }, 3000); // Show results for 3 seconds before refreshing
            }
        });
    </script>
</body>
</html>
