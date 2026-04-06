<?php
include 'main.php';

// Check logged in
check_loggedin();

// Error / success messages
$error_msg = '';
$success_msg = '';

// Get current user
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([ $_SESSION['account_id'] ]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if (isset($_POST['username'], $_POST['npassword'], $_POST['cpassword'], $_POST['email'])) {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $npassword = $_POST['npassword'];
    $cpassword = $_POST['cpassword'];

    if (empty($username) || empty($email)) {
        $error_msg = 'The input fields must not be empty!';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Please provide a valid email address!';
    } else if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        $error_msg = 'Username must contain only letters and numbers!';
    } else if (!empty($npassword) && (strlen($npassword) < 5 || strlen($npassword) > 20)) {
        $error_msg = 'Password must be between 5 and 20 characters long!';
    } else if ($npassword !== $cpassword) {
        $error_msg = 'Passwords do not match!';
    }

    if (empty($error_msg)) {

        // Check for duplicate username or email
        $stmt = $pdo->prepare('
            SELECT COUNT(*) 
            FROM users 
            WHERE (username = ? OR email = ?) 
            AND id != ?
        ');
        $stmt->execute([$username, $email, $account['id']]);

        if ($stmt->fetchColumn() > 0) {
            $error_msg = 'Username or email already exists!';
        } else {

            // Handle password update
            $password_hash = !empty($npassword)
                ? password_hash($npassword, PASSWORD_DEFAULT)
                : $account['password_hash'];

            // If email changed → require re-verification
            $is_verified = $account['email'] !== $email ? 0 : $account['is_verified'];
            $verification_token = $account['email'] !== $email
                ? bin2hex(random_bytes(32))
                : $account['verification_token'];
            $token_expires = $account['email'] !== $email
                ? date("Y-m-d H:i:s", time() + 3600)
                : $account['token_expires'];

            // Update user
            $stmt = $pdo->prepare('
                UPDATE users 
                SET username = ?, 
                    email = ?, 
                    password_hash = ?, 
                    is_verified = ?, 
                    verification_token = ?, 
                    token_expires = ?
                WHERE id = ?
            ');
            $stmt->execute([
                $username,
                $email,
                $password_hash,
                $is_verified,
                $verification_token,
                $token_expires,
                $account['id']
            ]);

            $_SESSION['account_name'] = $username;

            if ($account['email'] !== $email) {
                send_activation_email($email, $verification_token);
                unset($_SESSION['account_loggedin']);
                $success_msg = 'Email changed. Please verify your new email address.';
            } else {
                header('Location: profile.php');
                exit;
            }
        }
    }
}
?>

<?=template_header('Profile')?>

<?php if (!isset($_GET['action'])): ?>

<div class="page-title">
    <h2>Profile</h2>
    <p>View your account details below.</p>
</div>

<div class="block">

    <div class="profile-detail">
        <strong>Username</strong>
        <?=htmlspecialchars($account['username'], ENT_QUOTES)?>
    </div>

    <div class="profile-detail">
        <strong>Email</strong>
        <?=htmlspecialchars($account['email'], ENT_QUOTES)?>
    </div>

    <div class="profile-detail">
        <strong>Registered</strong>
        <?=date('Y-m-d H:i', strtotime($account['created_at']))?>
    </div>

    <a class="btn blue" href="?action=edit">Edit Details</a>

</div>

<?php elseif ($_GET['action'] == 'edit'): ?>

<div class="page-title">
    <h2>Edit Profile</h2>
</div>

<div class="block">

<form action="profile.php?action=edit" method="post">

    <label>Username</label>
    <input type="text" name="username"
        value="<?=htmlspecialchars($account['username'], ENT_QUOTES)?>" required>

    <label>New Password</label>
    <input type="password" name="npassword">

    <label>Confirm Password</label>
    <input type="password" name="cpassword">

    <label>Email</label>
    <input type="email" name="email"
        value="<?=htmlspecialchars($account['email'], ENT_QUOTES)?>" required>

    <?php if ($error_msg): ?>
        <div class="msg error"><?=$error_msg?></div>
    <?php elseif ($success_msg): ?>
        <div class="msg success"><?=$success_msg?></div>
    <?php endif; ?>

    <button type="submit">Save Details</button>
    <a href="profile.php">Back</a>

</form>

</div>

<?php endif; ?>

<?=template_footer()?>