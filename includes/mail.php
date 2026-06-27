<?php
// includes/mail.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/logger.php';

/**
 * Mail Helper Placeholder
 * Future implementation will integrate PHPMailer or Symphony Mailer here.
 * Currently, it logs the email to the system log in development, or simulates sending.
 *
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $htmlBody Email body in HTML
 * @return bool
 */
function sendMail($to, $subject, $htmlBody) {
    global $mailConfig;

    // Validate basic email structure
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        writeSysLog('error', "Failed to send email. Invalid recipient address: {$to}");
        return false;
    }

    // Determine environment behavior
    if (ENVIRONMENT === 'development') {
        // In dev, we just log the email intent instead of actually hitting an SMTP server
        $logMessage = "Simulated Email Sent To: {$to} | Subject: {$subject}";
        writeSysLog('info', $logMessage);
        return true;
    }

    try {
        // Placeholder logic for future SMTP integration
        /*
        $mailer = new FutureMailerClass();
        $mailer->setHost($mailConfig['host']);
        $mailer->setPort($mailConfig['port']);
        $mailer->setAuth($mailConfig['username'], $mailConfig['password']);
        $mailer->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $mailer->addAddress($to);
        $mailer->setSubject($subject);
        $mailer->setHtmlBody($htmlBody);
        return $mailer->send();
        */

        // Fallback for Phase 11
        writeSysLog('info', "Email module called in production, but no SMTP library is wired up yet. To: {$to}");
        return true;

    } catch (\Exception $e) {
        writeSysLog('error', "Mailer Exception: " . $e->getMessage());
        return false;
    }
}
