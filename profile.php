<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Detect if profile_image column exists; create it if missing
$hasProfileImageColumn = false;
if ($result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'")) {
    $hasProfileImageColumn = ($result->num_rows > 0);
}
if (!$hasProfileImageColumn) {
    @$conn->query("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) NULL");
    if ($result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'")) {
        $hasProfileImageColumn = ($result->num_rows > 0);
    }
}

$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    $currentProfileImage = $user['profile_image'] ?? null;
    $removeRequested = !empty($_POST['remove_profile_image']);
    $uploadedProfileImagePath = null;

    // Handle image upload (replacement)
    if (isset($_FILES['profile_image']) && isset($_FILES['profile_image']['tmp_name']) && is_uploaded_file($_FILES['profile_image']['tmp_name']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $tmpPath = $_FILES['profile_image']['tmp_name'];
        $originalName = $_FILES['profile_image']['name'];
        $fileSize = (int) $_FILES['profile_image']['size'];

        if ($fileSize <= 0) {
            $errorMessage = 'Uploaded file is empty.';
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $errorMessage = 'File too large. Max 2MB allowed.';
        } else {
            $mimeType = null;
            if (class_exists('finfo')) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = @$finfo->file($tmpPath);
            }
            if (!$mimeType && function_exists('mime_content_type')) {
                $mimeType = @mime_content_type($tmpPath);
            }
            // Fallback: guess by extension
            if (!$mimeType) {
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg'])) { $mimeType = 'image/jpeg'; }
                elseif ($ext === 'png') { $mimeType = 'image/png'; }
                elseif ($ext === 'webp') { $mimeType = 'image/webp'; }
            }

            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            if (!$mimeType || !isset($allowed[$mimeType])) {
                $errorMessage = 'Invalid image type. Allowed: JPG, PNG, WEBP.';
            } else {
                if (!is_dir('uploads')) {
                    @mkdir('uploads', 0755, true);
                }
                if (!is_writable('uploads')) {
                    $errorMessage = 'Upload directory is not writable.';
                } else {
                    $extension = $allowed[$mimeType];
                    $safeBase = 'profile_' . $user_id . '_' . time();
                    $targetRelPath = 'uploads/' . $safeBase . '.' . $extension;
                    if (move_uploaded_file($tmpPath, $targetRelPath)) {
                        $uploadedProfileImagePath = $targetRelPath;
                        // Remove old image if exists
                        if (!empty($currentProfileImage) && is_file($currentProfileImage)) {
                            @unlink($currentProfileImage);
                        }
                        // If upload succeeds, do not process remove checkbox
                        $removeRequested = false;
                    } else {
                        $errorMessage = 'Failed to move uploaded file.';
                    }
                }
            }
        }
    }

    // If remove requested and no new upload, delete existing image
    if ($removeRequested && !$uploadedProfileImagePath && !empty($currentProfileImage) && is_file($currentProfileImage)) {
        @unlink($currentProfileImage);
    }

    if ($username && $email && $phone && $address && !$errorMessage) {
        if ($uploadedProfileImagePath && $hasProfileImageColumn) {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, phone=?, address=?, profile_image=? WHERE id=?");
            $stmt->bind_param("sssssi", $username, $email, $phone, $address, $uploadedProfileImagePath, $user_id);
        } elseif ($removeRequested && $hasProfileImageColumn) {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, phone=?, address=?, profile_image=NULL WHERE id=?");
            $stmt->bind_param("ssssi", $username, $email, $phone, $address, $user_id);
        } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, phone=?, address=? WHERE id=?");
        $stmt->bind_param("ssssi", $username, $email, $phone, $address, $user_id);
        }
        $stmt->execute();
        $stmt->close();
        header("Location: profile.php?success=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile - BikeMart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #181c24; color: #f1f1f1; padding: 40px; }
        .profile-container { max-width: 500px; margin: auto; background: #232836; padding: 35px 30px 30px 30px; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.18); }
        .avatar { width: 90px; height: 90px; border-radius: 50%; background: #31374a; display: flex; align-items: center; justify-content: center; font-size: 48px; color: #6ec1e4; margin: 0 auto 18px auto; }
        .profile-info { text-align: center; margin-bottom: 30px; }
        .profile-info h3 { margin: 0 0 6px 0; font-size: 1.5em; color: #fff; }
        .profile-info p { margin: 0; color: #b0b8c1; font-size: 1em; }
        .form-section { margin-top: 18px; }
        label { font-weight: 600; color: #b0b8c1; }
        input[type="text"], input[type="email"] {
            width: 100%; padding: 12px; margin: 10px 0 22px; border: 1px solid #31374a; border-radius: 6px; background: #181c24; color: #f1f1f1;
            font-size: 1em;
        }
        input[type="text"]:focus, input[type="email"]:focus {
            outline: none; border-color: #6ec1e4; background: #232836;
        }
        button[type="submit"] {
            width: 100%; padding: 13px; background-color: #6ec1e4; color: #181c24; border: none;
            border-radius: 6px; font-size: 17px; font-weight: 600; cursor: pointer; transition: background 0.2s, color 0.2s;
        }
        button[type="submit"]:hover { background-color: #4fa3c7; color: #fff; }
        .success { text-align: center; color: #27ae60; font-weight: bold; margin-bottom: 14px; }
        .error { text-align: center; color: #e57373; font-weight: bold; margin-bottom: 14px; }
        .logout-btn {
            display: block; width: 100%; margin-top: 18px; padding: 11px; background: #f44336; color: #fff; border: none;
            border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none;
            transition: background 0.2s;
        }
        .logout-btn:hover { background: #c0392b; }
        .admin-badge {
            display: inline-block; background: #ffe082; color: #b8860b; font-size: 0.9em; font-weight: 600; padding: 2px 10px; border-radius: 12px; margin-top: 6px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 20px;
            }

            .profile-container {
                padding: 25px 20px;
            }

            .avatar {
                width: 70px;
                height: 70px;
                font-size: 36px;
            }

            .profile-info h3 {
                font-size: 1.3em;
            }

            .profile-info p {
                font-size: 0.9em;
            }

            input[type="text"], 
            input[type="email"] {
                padding: 10px;
                font-size: 0.95em;
            }

            button[type="submit"] {
                padding: 12px;
                font-size: 16px;
            }

            .logout-btn {
                padding: 10px;
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            .profile-container {
                padding: 20px 15px;
            }

            .avatar {
                width: 60px;
                height: 60px;
                font-size: 30px;
            }

            .profile-info h3 {
                font-size: 1.2em;
            }

            .profile-info p {
                font-size: 0.85em;
            }

            input[type="text"], 
            input[type="email"] {
                padding: 8px;
                font-size: 0.9em;
                margin: 8px 0 18px;
            }

            button[type="submit"] {
                padding: 10px;
                font-size: 15px;
            }

            .logout-btn {
                padding: 8px;
                font-size: 14px;
            }

            .admin-badge {
                font-size: 0.8em;
                padding: 1px 8px;
            }
        }

        @media (max-width: 360px) {
            body {
                padding: 10px;
            }

            .profile-container {
                padding: 15px 10px;
            }

            .avatar {
                width: 50px;
                height: 50px;
                font-size: 24px;
            }

            .profile-info h3 {
                font-size: 1.1em;
            }

            .profile-info p {
                font-size: 0.8em;
            }

            input[type="text"], 
            input[type="email"] {
                padding: 6px;
                font-size: 0.85em;
                margin: 6px 0 15px;
            }

            button[type="submit"] {
                padding: 8px;
                font-size: 14px;
            }

            .logout-btn {
                padding: 6px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="avatar">
<?php if (!empty($user['profile_image'])): ?>
            <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
<?php else: ?>
            <i class="fa-solid fa-user"></i>
<?php endif; ?>
        </div>
        <div class="profile-info">
            <h3><?= htmlspecialchars($user['username']) ?></h3>
            <p><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
            <p><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($user['phone'] ?? 'Not set') ?></p>
            <p><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($user['address'] ?? 'Not set') ?></p>
            <?php if (!empty($user['is_admin'])): ?>
                <div class="admin-badge"><i class="fa-solid fa-crown"></i> Admin</div>
            <?php endif; ?>
        </div>
        <?php if (!empty($errorMessage)): ?>
            <div class="error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="success">Profile updated successfully!</div>
        <?php endif; ?>
        <div class="form-section">
            <form method="POST" enctype="multipart/form-data">
                <label for="username">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

                <label for="email">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                <label for="phone">Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>

                <label for="address">Address</label>
                <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>

<?php if (!empty($user['profile_image'])): ?>
                <div style="margin: 10px 0 18px;">
                    <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="remove_profile_image" value="1">
                        <span>Remove current profile image</span>
                    </label>
                </div>
<?php endif; ?>
                <label for="profile_image">Change/Upload Profile Image</label>
                <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp">
                <small style="display:block;color:#b0b8c1;margin:6px 0 18px;">Max 2MB. Allowed: JPG, PNG, WEBP.</small>

                <button type="submit">Update Profile</button>
            </form>
        </div>
        <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</body>
</html>
