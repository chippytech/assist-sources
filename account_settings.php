<?php

// ------------- 1. Auth Check -------------
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

require 'db.php'; // sets $pdo
$user_id = $_SESSION['user_id'];
$message = "";
$error   = "";

// Fetch current user data
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// ------------- 2. Handle POST Requests -------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $current_password = $_POST['current_password'] ?? '';

    // Verify current password first for any sensitive change
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $db_user = $stmt->fetch();

    if (!password_verify($current_password, $db_user['password_hash'])) {
        $error = "Incorrect current password.";
    } else {

        // --- Change Username ---
        if ($action === 'update_username') {
            $new_username = trim($_POST['new_username']);
            if (strlen($new_username) < 3) {
                $error = "Username too short.";
            } else {
                try {
                    $update = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                    $update->execute([$new_username, $user_id]);
                    $_SESSION['username'] = $new_username; // Update session
                    $message = "Username updated successfully.";
                } catch (PDOException $e) {
                    $error = "Username already exists.";
                }
            }
        }

        // --- Change Email ---
        elseif ($action === 'update_email') {
            $new_email = filter_var($_POST['new_email'], FILTER_SANITIZE_EMAIL);
            if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                $update = $pdo->prepare("UPDATE users SET email = ?, is_verified = 0 WHERE id = ?");
                $update->execute([$new_email, $user_id]);
                $message = "Email updated. Please re-verify your account.";
                // Note: You would usually trigger the verification email function here.
            }
        }

        // --- Change Password ---
        elseif ($action === 'update_password') {
            $new_pass = $_POST['new_password'];
            $conf_pass = $_POST['confirm_password'];

            if (strlen($new_pass) < 8) {
                $error = "New password must be at least 8 characters.";
            } elseif ($new_pass !== $conf_pass) {
                $error = "Passwords do not match.";
            } else {
                $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $update->execute([$hash, $user_id]);
                $message = "Password updated successfully.";
            }
        }

        // --- Delete Account ---
        elseif ($action === 'delete_account') {
            if ($_POST['confirm_delete'] === 'DELETE') {
                // Delete user (Cascading deletes should handle chat history if SQL foreign keys are set)
                $pdo->prepare("DELETE FROM chat_history WHERE username = ?")->execute([$_SESSION['username']]);
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
                
                session_destroy();
                header("Location: login.php?msg=account_deleted");
                exit;
            } else {
                $error = "Please type 'DELETE' to confirm account closure.";
            }
        }
    }
    // Refresh user data after updates
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}

function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .settings-card { max-width: 600px; margin: 2rem auto; }
        .section-title { border-bottom: 2px solid #eee; margin-bottom: 1.5rem; padding-bottom: 0.5rem; }
    </style>
</head>
<body class="bg-light">

<div class="container">
    <div class="settings-card">
        <a href="/" class="btn btn-sm btn-link mb-3 p-0">← Back to Chat</a>
        <h2 class="mb-4">Account Settings</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= h($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>

        <!-- Forms usually share one password verification field for simplicity, 
             but here they are grouped into sections. -->

        <!-- USERNAME & EMAIL SECTION -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="section-title">Identity</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="new_username" class="form-control" value="<?= h($user['username']) ?>">
                        <input type="hidden" name="action" value="update_username">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Password (Required)</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary">Update Username</button>
                </form>

                <hr class="my-4">

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="new_email" class="form-control" value="<?= h($user['email']) ?>">
                        <input type="hidden" name="action" value="update_email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Password (Required)</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary">Update Email</button>
                </form>
            </div>
        </div>

        <!-- PASSWORD SECTION -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="section-title">Change Password</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="update_password">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button class="btn btn-warning w-100">Reset Password</button>
                </form>
            </div>
        </div>

        <!-- DANGER ZONE -->
        <div class="card border-danger shadow-sm">
            <div class="card-body">
                <h5 class="section-title text-danger">Danger Zone</h5>
                <p class="text-muted small">Once you delete your account, there is no going back. All chat history will be wiped.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="delete_account">
                    <div class="mb-3">
                        <label class="form-label">Type <b>DELETE</b> to confirm</label>
                        <input type="text" name="confirm_delete" class="form-control" placeholder="DELETE">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <button class="btn btn-danger w-100">Permanently Delete Account</button>
                </form>
            </div>
        </div>

    </div>
</div>

</body>
</html>