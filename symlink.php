<?php

/**
 * Secure symlink creator for shared hosting environments
 * Place this file in your public_html directory
 */

// Configuration - Adjust these paths
define('APP_ROOT', dirname(__DIR__)); // Adjust if file is in subdirectory
define('TARGET_DIR', APP_ROOT . '/storage/app/public');
define('LINK_NAME', 'storage');
define('MAX_REDIRECTS', 5);

/**
 * Securely create a symbolic link
 */
function createSecureSymlink($target, $link) {
    // Validate paths are within allowed directories
    $realTarget = realpath($target);
    $realLink = realpath(dirname($link));
    
    if ($realTarget === false) {
        return ['success' => false, 'message' => 'Target directory does not exist'];
    }
    
    if ($realLink === false) {
        return ['success' => false, 'message' => 'Invalid link destination'];
    }
    
    // Security: Prevent path traversal attacks
    $allowedBase = realpath(APP_ROOT);
    if (strpos($realTarget, $allowedBase) !== 0) {
        return ['success' => false, 'message' => 'Target outside allowed directory'];
    }
    
    // Security: Check if target is accessible
    if (!is_readable($realTarget) || !is_dir($realTarget)) {
        return ['success' => false, 'message' => 'Target directory is not accessible'];
    }
    
    // Check if symlink already exists
    if (is_link($link)) {
        // Verify existing symlink points to correct target
        $existingTarget = readlink($link);
        if ($existingTarget === $target) {
            return ['success' => true, 'message' => 'Symlink already exists and is correct'];
        }
        
        // Remove broken or incorrect symlink
        if (!unlink($link)) {
            return ['success' => false, 'message' => 'Could not remove existing symlink'];
        }
    }
    
    // Check if directory exists at link location
    if (is_dir($link) && !is_link($link)) {
        return ['success' => false, 'message' => 'A directory already exists at the link location'];
    }
    
    // Attempt to create symlink
    try {
        if (@symlink($target, $link)) {
            return ['success' => true, 'message' => 'Symlink created successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to create symlink (check permissions)'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Attempt multiple approaches for shared hosting environments
 */
function tryAlternativeMethods($target, $link) {
    $results = [];
    
    // Method 1: Direct symlink (most common)
    $result1 = createSecureSymlink($target, $link);
    if ($result1['success']) {
        return $result1;
    }
    $results[] = $result1;
    
    // Method 2: Try relative path
    $relativeTarget = '../' . str_replace(realpath(dirname($link) . '/../'), '', $target);
    $result2 = createSecureSymlink($relativeTarget, $link);
    if ($result2['success']) {
        return $result2;
    }
    $results[] = $result2;
    
    // Method 3: Try using shell command (if available)
    if (function_exists('exec')) {
        $command = sprintf('ln -sf "%s" "%s" 2>&1', $target, $link);
        exec($command, $output, $returnCode);
        if ($returnCode === 0) {
            return ['success' => true, 'message' => 'Symlink created via shell'];
        }
    }
    
    return ['success' => false, 'message' => 'All methods failed: ' . implode(', ', array_column($results, 'message'))];
}

// Main execution
header('Content-Type: text/plain');

try {
    // Determine paths
    $target = TARGET_DIR;
    $link = $_SERVER['DOCUMENT_ROOT'] . '/' . LINK_NAME;
    
    // Validate we're on a compatible server
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        die("Windows servers are not supported for symlinking");
    }
    
    // Check PHP version and extensions
    if (!function_exists('symlink')) {
        die("symlink() function is not available");
    }
    
    // Check if script is running in web context
    if (php_sapi_name() === 'cli') {
        die("This script should be run via web request");
    }
    
    // Execute with progress reporting
    echo "Attempting to create symbolic link...\n";
    echo "Target: {$target}\n";
    echo "Link: {$link}\n\n";
    
    $result = tryAlternativeMethods($target, $link);
    
    if ($result['success']) {
        echo "✅ " . $result['message'] . "\n";
        echo "Symlink should now be available at: {$link}\n";
        echo "Storage files will be accessible via: /storage/\n";
    } else {
        echo "❌ " . $result['message'] . "\n\n";
        echo "Manual workaround:\n";
        echo "1. Create a folder named 'storage' in your public directory\n";
        echo "2. Copy the contents of 'storage/app/public' into it\n";
        echo "3. When you upload new files, you'll need to sync them manually\n";
        
        // Log error for debugging (without exposing paths)
        error_log("Symlink creation failed: " . $result['message']);
    }
    
} catch (Exception $e) {
    // Generic error message for security
    error_log("Symlink creation error: " . $e->getMessage());
    die("An error occurred while creating the symlink. Please check error logs.");
}
