<?php

namespace DevDay\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use DevDay\Config\Mail;

class MailService
{
    private array $config;

    public function __construct()
    {
        $this->config = Mail::getConfig();
    }

    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        ?string $replyToEmail = null,
        ?string $replyToName = null
    ): array {
        // If SMTP credentials are not configured or empty, simulate delivery for local development & testing
        if (empty($this->config['username']) || empty($this->config['host']) || $this->config['host'] === 'smtp.mailtrap.io' && empty($this->config['username'])) {
            error_log("[DevDay MailService Simulated] Report dispatched to: {$toEmail} with subject: '{$subject}'");
            return [
                'success'      => true,
                'is_simulated' => true,
                'user_message' => "Report generated and marked ready. (SMTP credentials are not configured in .env, so delivery was recorded locally).",
            ];
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->config['host'];
            $mail->SMTPAuth   = !empty($this->config['username']);
            $mail->Username   = $this->config['username'];
            $mail->Password   = $this->config['password'];
            $mail->Port       = $this->config['port'];

            if ($this->config['encryption'] === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($this->config['encryption'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPAutoTLS = false;
                $mail->SMTPSecure = false;
            }

            // Timeout
            $mail->Timeout = 10;

            // Recipients
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($toEmail, $toName ?: $toEmail);

            if (!empty($replyToEmail)) {
                $mail->addReplyTo($replyToEmail, $replyToName ?: $replyToEmail);
            }

            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</tr>', '</div>'], "\n", $htmlBody));

            $mail->send();

            return [
                'success'      => true,
                'is_simulated' => false,
                'user_message' => "Report sent successfully to {$toEmail}.",
            ];
        } catch (PHPMailerException $e) {
            $rawError = $mail->ErrorInfo ?: $e->getMessage();
            error_log("[DevDay MailService Error] Failed to send email to {$toEmail}: {$rawError}");

            // Sanitize error to avoid leaking credentials
            $userSafeError = 'Unable to connect to SMTP server. Please verify your SMTP settings in .env.';
            if (str_contains(strtolower($rawError), 'authenticate')) {
                $userSafeError = 'SMTP authentication failed. Please check your username/password in .env.';
            } elseif (str_contains(strtolower($rawError), 'timed out') || str_contains(strtolower($rawError), 'connection refused')) {
                $userSafeError = 'SMTP connection timed out. Check SMTP host and port in .env.';
            }

            return [
                'success'      => false,
                'is_simulated' => false,
                'error'        => $userSafeError,
                'user_message' => "Failed to deliver email: {$userSafeError}",
            ];
        } catch (\Exception $e) {
            error_log("[DevDay MailService System Error] {$e->getMessage()}");
            return [
                'success'      => false,
                'is_simulated' => false,
                'error'        => 'A system error occurred while preparing the email.',
                'user_message' => 'Unable to send report due to a server configuration issue.',
            ];
        }
    }
}
