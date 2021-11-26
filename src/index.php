<?php
require_once __DIR__ . "/classes/Autoloader.class.php";
use mailTester\Autoloader;
Autoloader::start();
use mailTester\Mail;
use PHPMailer\PHPMailer\PHPMailer;

$senderName = filter_input(INPUT_POST, "sender_name", FILTER_SANITIZE_STRING);
$senderEmail = filter_input(INPUT_POST, "sender_email", FILTER_SANITIZE_EMAIL);
$smtpHost = filter_input(INPUT_POST, "smtp_host", FILTER_SANITIZE_STRING);
$smtpUsername = filter_input(INPUT_POST, "smtp_username", FILTER_SANITIZE_STRING);
$smtpPassword = filter_input(INPUT_POST, "smtp_password", FILTER_UNSAFE_RAW);
$smtpPort = filter_input(INPUT_POST, "smtp_port", FILTER_SANITIZE_NUMBER_INT);
$smtpAuth = (int)filter_input(INPUT_POST, "smtp_auth", FILTER_SANITIZE_NUMBER_INT) === 1;
$smtpEncryption = filter_input(INPUT_POST, "smtp_encryption", FILTER_SANITIZE_STRING);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>webfashionist/mail-tester</title>

    <link rel="stylesheet" href="stylesheets/style.css">
</head>
<body>

<div class="container-outer">
    <div class="container">
        <h1>mail-tester</h1>

        <form action="" method="post">
            <p class="form-item">
                <label for="sender_name">Sender name:</label>
                <input type="text" value="<?php echo $senderName; ?>" name="sender_name" id="sender_name">
            </p>
            <p class="form-item">
                <label for="sender_email">Sender email:</label>
                <input type="email" value="<?php echo $senderEmail; ?>" name="sender_email" id="sender_email">
            </p>
            <p class="form-item">
                <label for="smtp_host">SMTP host:</label>
                <input type="text" value="<?php echo $smtpHost; ?>" name="smtp_host" id="smtp_host">
            </p>
            <p class="form-item">
                <label for="smtp_port">SMTP port:</label>
                <input type="number" value="<?php echo $smtpPort ?? Mail::DEFAULT_SMTP_PORT; ?>" name="smtp_port" id="smtp_port">
            </p>
            <p class="form-item">
                <label for="smtp_username">SMTP username:</label>
                <input type="text" value="<?php echo $smtpUsername; ?>" name="smtp_username" id="smtp_username">
            </p>
            <p class="form-item">
                <label for="smtp_password">SMTP password:</label>
                <input type="password" value="<?php echo $smtpPassword; ?>" name="smtp_password" id="smtp_password">
            </p>
            <p class="form-item">
                <label for="smtp_auth">Use authentification:</label>
                <select name="smtp_auth" id="smtp_auth">
                    <option value="1"<?php echo $smtpAuth ? ' selected' : ''; ?>>Yes</option>
                    <option value="0"<?php echo !$smtpAuth ? ' selected' : ''; ?>>No</option>
                </select>
            </p>
            <p class="form-item">
                <label for="smtp_encryption">Use encryption:</label>
                <select name="smtp_encryption" id="smtp_encryption">
                    <option value=""<?php echo !$smtpEncryption ? ' selected' : ''; ?>>No</option>
                    <option value="ssl"<?php echo $smtpEncryption === "ssl" ? ' selected' : ''; ?>>SSL/SMTPS</option>
                    <option value="tls"<?php echo $smtpEncryption === "tls" ? ' selected' : ''; ?>>TLS/STARTTLS</option>
                </select>
            </p>
            <p class="form-submit">
                <input type="submit" value="Test" name="run">
            </p>
            <?php
            if ($smtpHost && $smtpPort && $smtpUsername && $smtpPassword && $senderEmail) {
                $Mail = new Mail();
                $Mail->setSender($senderEmail, $senderName)
                    ->addRecipient($senderEmail, $senderEmail)
                    ->setSMTPHost($smtpHost)
                    ->setSMTPPort($smtpPort)
                    ->setSMTPUsername($smtpUsername)
                    ->setSMTPPassword($smtpPassword)
                    ->setSMTPAuth($smtpAuth)
                    ->setSMTPEncryption($smtpEncryption ?: null);
                $response = $Mail->send("SMTP Test", "This is a test mail send through SMTP.");
                if ($response->success) { ?>
                    <div class="success">Email successfully sent!</div>
                <?php } else { ?>
                    <div class="error">Error: <?php echo $response->message; ?></div>
                <?php }
            } ?>
        </form>

    </div>
</div>
</body>
</html>
