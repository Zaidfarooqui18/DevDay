<?php

namespace DevDay\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use DevDay\Config\Mail;

class MailService
{
    private array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? Mail::getConfig();
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
        if (empty($this->config['username']) || empty($this->config['host']) || ($this->config['host'] === 'smtp.mailtrap.io' && empty($this->config['username']))) {
            error_log("[DevDay MailService Simulated] Report dispatched to: {$toEmail} with subject: '{$subject}'");
            return [
                'success'      => true,
                'is_simulated' => true,
                'user_message' => "Report generated and recorded. (SMTP credentials are not configured in .env, so delivery was recorded in simulation mode).",
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
            $mail->Port       = (int)$this->config['port'];

            // Prevent SSL verification errors in local/dev / Windows OpenSSL environments
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];

            $encryption = strtolower((string)($this->config['encryption'] ?? 'tls'));
            if ($encryption === 'tls' || $this->config['port'] == 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl' || $this->config['port'] == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPAutoTLS = false;
                $mail->SMTPSecure = false;
            }

            // Timeout
            $mail->Timeout = 15;

            // Recipients
            $fromEmail = !empty($this->config['from_email']) ? $this->config['from_email'] : $this->config['username'];
            $fromName = !empty($this->config['from_name']) ? $this->config['from_name'] : 'DevDay Work Reports';
            $mail->setFrom($fromEmail, $fromName);
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

            $userSafeError = 'Unable to connect to SMTP server: ' . $rawError;
            if (str_contains(strtolower($rawError), 'authenticate')) {
                $userSafeError = 'SMTP authentication failed. If using Gmail, make sure to generate a 16-character Google App Password (not your normal Gmail account password).';
            } elseif (str_contains(strtolower($rawError), 'timed out') || str_contains(strtolower($rawError), 'connection refused')) {
                $userSafeError = 'SMTP connection timed out. Check SMTP host, port, and firewall/network settings.';
            }

            return [
                'success'      => false,
                'is_simulated' => false,
                'error'        => $userSafeError,
                'raw_error'    => $rawError,
                'user_message' => "Failed to deliver email: {$userSafeError}",
            ];
        } catch (\Throwable $e) {
            error_log("[DevDay MailService System Error] {$e->getMessage()}");
            return [
                'success'      => false,
                'is_simulated' => false,
                'error'        => 'System error: ' . $e->getMessage(),
                'user_message' => 'Unable to send report due to a server configuration issue: ' . $e->getMessage(),
            ];
        }
    }

    public function testConnection(string $testToEmail, ?array $overrideConfig = null): array
    {
        $config = $overrideConfig ? array_merge($this->config, $overrideConfig) : $this->config;

        if (empty($config['host']) || empty($config['username']) || empty($config['password'])) {
            return [
                'success' => false,
                'message' => 'SMTP Host, Username, and Password must be provided to test delivery.',
                'debug_log' => "Configuration Incomplete:\nHost: " . ($config['host'] ?: '(empty)') . "\nUsername: " . ($config['username'] ?: '(empty)') . "\nPassword: " . ($config['password'] ? '***' : '(empty)')
            ];
        }

        $debugOutput = '';
        $mail = new PHPMailer(true);

        try {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function ($str, $level) use (&$debugOutput) {
                $debugOutput .= "[$level] " . trim($str) . "\n";
            };

            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->Port       = (int)$config['port'];

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];

            $encryption = strtolower((string)($config['encryption'] ?? 'tls'));
            if ($encryption === 'tls' || $config['port'] == 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl' || $config['port'] == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPAutoTLS = false;
                $mail->SMTPSecure = false;
            }

            $mail->Timeout = 15;

            $fromEmail = !empty($config['from_email']) ? $config['from_email'] : $config['username'];
            $fromName = !empty($config['from_name']) ? $config['from_name'] : 'DevDay Test Mailer';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($testToEmail);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'DevDay SMTP Test Connection — ' . date('Y-m-d H:i:s');
            $mail->Body    = '<div style="font-family:sans-serif;padding:20px;background:#0d1322;color:#f8fafc;border-radius:12px;">'
                           . '<h2 style="color:#6366f1;margin-top:0;">DevDay SMTP Test Succeeded</h2>'
                           . '<p>Congratulations! Your SMTP settings are properly configured and emails can be dispatched successfully.</p>'
                           . '<p style="color:#94a3b8;font-size:12px;">Sent via DevDay MailService on: ' . date('r') . '</p>'
                           . '</div>';
            $mail->AltBody = "DevDay SMTP Test Succeeded - Congratulations! Your SMTP settings are properly configured. Sent on: " . date('r');

            $mail->send();

            return [
                'success' => true,
                'message' => "Test email successfully delivered to {$testToEmail}!",
                'debug_log' => $debugOutput
            ];
        } catch (PHPMailerException $e) {
            $rawError = $mail->ErrorInfo ?: $e->getMessage();
            $hint = '';
            if (str_contains(strtolower($rawError), 'username and password not accepted') || str_contains(strtolower($rawError), 'badcredentials') || str_contains(strtolower($rawError), '535')) {
                $hint = "\n\nTip for Gmail: Google requires a 16-character 'App Password'. Go to Google Account > Security > 2-Step Verification > App passwords to generate one.";
            } elseif (str_contains(strtolower($rawError), 'timed out')) {
                $hint = "\n\nTip: The connection timed out. Some ISPs block port 25/587. Try port 465 with SSL, or port 587 with TLS.";
            }

            return [
                'success' => false,
                'message' => "SMTP Connection Failed: {$rawError}" . $hint,
                'error' => $rawError,
                'debug_log' => $debugOutput . "\n[Error] " . $rawError
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => "System Exception: " . $e->getMessage(),
                'error' => $e->getMessage(),
                'debug_log' => $debugOutput . "\n[System Exception] " . $e->getMessage()
            ];
        }
    }
}
