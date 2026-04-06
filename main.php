<?php
// Include config (DB credentials + constants)
include_once 'config.php';

// Start session
session_start();

// Namespaces for PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==============================
// DATABASE CONNECTION
// ==============================

try {
    $pdo = new PDO(
        'mysql:host=' . db_host . ';dbname=' . db_name . ';charset=' . db_charset,
        db_user,
        db_pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    exit('Failed to connect to database: ' . $exception->getMessage());
}

// ==============================
// TEMPLATE HEADER
// ==============================

function template_header($title) {

    $current_file_name = basename($_SERVER['PHP_SELF']);

    echo '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,minimum-scale=1">
<title>' . htmlspecialchars($title) . '</title>
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>

<header class="header">
<div class="wrapper">
<h1>Assist Dashboard</h1>

<input type="checkbox" id="menu">
<label for="menu"></label>

<nav class="menu">
<a href="home.php" class="' . ($current_file_name == 'home.php' ? 'active' : '') . '">Home</a>
<a href="profile.php" class="' . ($current_file_name == 'profile.php' ? 'active' : '') . '">Profile</a>
<a href="logout.php" class="alt">Logout</a>
</nav>

</div>
</header>

<div class="content">';
}

// ==============================
// TEMPLATE FOOTER
// ==============================

function template_footer() {
    echo '</div></body></html>';
}

// ==============================
// LOGIN CHECK FUNCTION
// ==============================

function check_loggedin($redirect_file = 'index.php') {

    if (!isset($_SESSION['account_loggedin'])) {
        header('Location: ' . $redirect_file);
        exit;
    }

    return true;
}

// ==============================
// SEND ACTIVATION EMAIL
// ==============================

function send_activation_email($email, $code) {

    if (!mail_enabled) return;

    include_once 'lib/phpmailer/Exception.php';
    include_once 'lib/phpmailer/PHPMailer.php';
    include_once 'lib/phpmailer/SMTP.php';

    $mail = new PHPMailer(true);

    try {

        if (SMTP) {
            $mail->isSMTP();
            $mail->Host = smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = smtp_user;
            $mail->Password = smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = smtp_port;
        }

        $mail->setFrom(mail_from, mail_name);
        $mail->addAddress($email);
        $mail->addReplyTo(mail_from, mail_name);

        $mail->isHTML(true);
        $mail->Subject = 'Account Activation Required';

        $activate_link = base_url . 'activate.php?code=' . $code;

        $email_template = str_replace(
            '%link%',
            $activate_link,
            file_get_contents('activation-email-template.html')
        );

        $body = '<!DOCTYPE html><html><body>' . $email_template . '</body></html>';

        $mail->Body = $body;
        $mail->AltBody = strip_tags($email_template);

        $mail->send();

    } catch (Exception $e) {
        exit('Error sending activation email: ' . $mail->ErrorInfo);
    }
}

// ==============================
// SEND PASSWORD RESET EMAIL
// ==============================

function send_password_reset_email($email, $username, $code) {

    if (!mail_enabled) return;

    include_once 'lib/phpmailer/Exception.php';
    include_once 'lib/phpmailer/PHPMailer.php';
    include_once 'lib/phpmailer/SMTP.php';

    $mail = new PHPMailer(true);

    try {

        if (SMTP) {
            $mail->isSMTP();
            $mail->Host = smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = smtp_user;
            $mail->Password = smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = smtp_port;
        }

        $mail->setFrom(mail_from, mail_name);
        $mail->addAddress($email);
        $mail->addReplyTo(mail_from, mail_name);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset';

        $reset_link = base_url . 'reset-password.php?code=' . $code;

        $email_template = str_replace(
            ['%link%','%username%'],
            [$reset_link, htmlspecialchars($username, ENT_QUOTES)],
            file_get_contents('resetpass-email-template.html')
        );

        $body = '<!DOCTYPE html><html><body>' . $email_template . '</body></html>';

        $mail->Body = $body;
        $mail->AltBody = strip_tags($email_template);

        $mail->send();

    } catch (Exception $e) {
        exit('Error sending reset email: ' . $mail->ErrorInfo);
    }
}
?>