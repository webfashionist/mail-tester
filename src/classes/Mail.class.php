<?php

namespace mailTester;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class Mail
 *
 * @package \mailTester
 */
class Mail
{

    const DEFAULT_SMTP_PORT = 587;

    private array $recipients = [];
    private string $senderEmail = "";
    private string $senderName = "";
    private string $smtpHost;
    private int $smtpPort = self::DEFAULT_SMTP_PORT;
    private string $smtpUsername = "";
    private string $smtpPassword = "";
    private bool $smtpAuth = true;
    private ?string $smtpEncryption = null;
    private PHPMailer $phpmailer;

    public function send(string $subject, string $message): object
    {
        if (!$this->initPHPMailer() || !$this->phpmailer()) {
            return (object) ["success" => false, "message" => "PHPMailer not configured."];
        }

        try {
            foreach ($this->recipients as $recipient) {
                // Add a recipient (name is optional)
                $this->phpmailer()->addAddress($recipient->email, $recipient->name ?? '');
            }

            // Content
            $this->phpmailer()->Subject = $subject;
            $this->phpmailer()->Body    = $message;
            $success = $this->phpmailer()->send();
            return (object) ["success" => $success, "message" => !$success ? "Unknown SMTP error." : "", "MIMEMessage" => $success ? $this->phpmailer()->getSentMIMEMessage() : null];
        } catch (Exception $exception) {
            return (object) ["success" => false, "message" => $exception->getMessage()];
        }
    }

    public function setSender(string $email, string $name): self
    {
        $this->senderEmail = $email;
        $this->senderName = $name;
        return $this;
    }

    public function addRecipient(string $email, string $name = ""): self
    {
        $this->recipients[] = (object) [
            "email" => $email,
            "name" => $name
        ];
        return $this;
    }

    public function setSMTPHost(string $host): self
    {
        $this->smtpHost = $host;
        return $this;
    }

    public function setSMTPPort(int $port): self
    {
        $this->smtpPort = $port;
        if ($port === 465) {
            // use SMTPS (ssl instead of tls) as a default
            $this->setSMTPEncryption(in_array($this->smtpEncryption, [
                PHPMailer::ENCRYPTION_STARTTLS,
                PHPMailer::ENCRYPTION_SMTPS,
            ]) ? $this->smtpEncryption : PHPMailer::ENCRYPTION_SMTPS);
            return $this;
        }
        if ($port === 587) {
            // use STARTTLS (tls instead of ssl) as a default
            $this->setSMTPEncryption(in_array($this->smtpEncryption, [
                PHPMailer::ENCRYPTION_STARTTLS,
                PHPMailer::ENCRYPTION_SMTPS,
            ]) ? $this->smtpEncryption : PHPMailer::ENCRYPTION_STARTTLS);
        }
        return $this;
    }

    public function setSMTPUsername(string $username): self
    {
        $this->smtpUsername = $username;
        return $this;
    }

    public function setSMTPPassword(string $password): self
    {
        $this->smtpPassword = $password;
        return $this;
    }

    public function setSMTPAuth(bool $auth): self
    {
        $this->smtpAuth = $auth;
        return $this;
    }

    public function setSMTPEncryption(?string $encryption = null): self
    {
        $this->smtpEncryption = $encryption;
        return $this;
    }

    public function __destruct()
    {
        if ($this->phpmailer() &&
            isset($this->phpmailer()->SMTPKeepAlive) &&
            $this->phpmailer()->SMTPKeepAlive === true) {
            $this->phpmailer()->smtpClose();
        }
    }

    protected function phpmailer(): ?PHPMailer
    {
        return $this->phpmailer;
    }

    private function initPHPMailer(): bool
    {
        $phpmailer = new PHPMailer();
        // Server settings
        $phpmailer->SMTPDebug = 0;                       // SMTP Debug verbosity
        $phpmailer->isSMTP();                            // Send using SMTP
        $phpmailer->Host = $this->smtpHost;              // Set the SMTP server to send through
        $phpmailer->SMTPAuth = $this->smtpAuth;          // Enable SMTP authentication
        $phpmailer->Username = $this->smtpUsername;      // SMTP username
        $phpmailer->Password = $this->smtpPassword;      // SMTP password
        $phpmailer->SMTPSecure = $this->smtpEncryption;  // Enable TLS/SSL encryption
        $phpmailer->Port = $this->smtpPort;              // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        try {
            // Sender
            $phpmailer->setFrom($this->senderEmail, $this->senderName ?? '');
            $phpmailer->addReplyTo($this->senderEmail, $this->senderName ?? '');

            // Charset
            $phpmailer->CharSet = 'UTF-8';
            // $mail->Encoding = 'base64';
            $this->setPHPMailer($phpmailer);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    private function setPHPMailer(PHPMailer $PHPMailer)
    {
        $this->phpmailer = $PHPMailer;
    }

}
