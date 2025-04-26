<?php
// session_start();
require_once __DIR__ . '/config.php';
// require_once __DIR__ . '/auth.php';

// checkAuthentication();

// Validate CSRF token
// if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
//     $_SESSION['error'] = "CSRF token validation failed. Please try again.";
//     header("Location: ../profile.php");
//     exit();
// }

// Fetch user ID from session
$user_id = $_SESSION['user_id'];

// Initialize variables
$errors = [];
$success = false;

// Validate and sanitize input
$first_name = trim(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING));
$last_name = trim(filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
$password = trim($_POST['password']);
$confirm_password = trim($_POST['confirm_password']);

// Validate required fields
if (empty($first_name) || strlen($first_name) > 50) {
    $errors[] = "First name is required and must be less than 50 characters.";
}

if (empty($last_name) || strlen($last_name) > 50) {
    $errors[] = "Last name is required and must be less than 50 characters.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    $errors[] = "Valid email is required and must be less than 100 characters.";
}

if (!empty($phone) && !preg_match('/^[\d\s\-+()]{10,20}$/', $phone)) {
    $errors[] = "Invalid phone number format.";
}

// Validate password if provided
if (!empty($password)) {
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Password and confirmation do not match.";
    }
}

// Check if email already exists (excluding current user)
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $errors[] = "Email address is already in use by another account.";
}
$stmt->close();

// Handle profile image upload
$profile_image = null;
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $max_size = 2 * 1024 * 1024; // 2MB
    $allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    
    // Verify file
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $_FILES['profile_image']['tmp_name']);
    finfo_close($file_info);
    
    // Validate
    if (!array_key_exists($mime_type, $allowed_types)) {
        $errors[] = "Only JPEG and PNG images are allowed.";
    } elseif ($_FILES['profile_image']['size'] > $max_size) {
        $errors[] = "Image size must be less than 2MB.";
    } else {
        $upload_dir = dirname(__DIR__) . '/assets/img/users/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $extension = $allowed_types[$mime_type];
        $filename = 'user_' . $user_id . '_' . uniqid() . '.' . $extension;
        $destination = $upload_dir . $filename;
        
        // Move the file
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
            $profile_image = 'assets/img/users/' . $filename;
            
            // Delete old image if it exists
            $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $old_image = $result->fetch_assoc()['profile_image'];
            $stmt->close();
            
            if (!empty($old_image) && $old_image !== 'assets/img/users/default.png') {
                $old_image_path = dirname(__DIR__) . '/' . $old_image;
                if (file_exists($old_image_path) && is_file($old_image_path)) {
                    unlink($old_image_path);
                }
            }
        } else {
            $errors[] = "Failed to move uploaded file.";
        }
    }
}

// If no errors, proceed with update
if (empty($errors)) {
    // Prepare base query
    $query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?";
    $params = [$first_name, $last_name, $email, $phone];
    $types = "ssss";
    
    // Add password if provided
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $query .= ", password = ?";
        $params[] = $hashed_password;
        $types .= "s";
    }
    
    // Add profile image if uploaded
    if ($profile_image) {
        $query .= ", profile_image = ?";
        $params[] = $profile_image;
        $types .= "s";
    }
    
    // Complete query
    $query .= " WHERE id = ?";
    $params[] = $user_id;
    $types .= "i";
    
    // Prepare and execute statement
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
        $success = true;
        
        // Update session with new profile image if changed
        if ($profile_image) {
            $_SESSION['profile_image'] = $profile_image;
        }
    } else {
        $errors[] = "Database error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle errors or success
if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
}

header("Location: ../profile.php");
exit();
?>