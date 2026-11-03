<?php
/**
 * AgriConnect — Mail Helper
 * Sends HTML emails via PHP mail(). No external dependency.
 * (For production, swap for SMTP or an API-based mailer.)
 */

require_once __DIR__ . '/config.php';

// ============================================
// CORE SEND
// ============================================

/**
 * Send an HTML email.
 *
 * @param string $to       Recipient email
 * @param string $subject  Subject line
 * @param string $htmlBody HTML body
 * @param string $replyTo  Optional reply-to address
 * @return bool
 */
function send_mail($to, $subject, $htmlBody, $replyTo = null) {
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log("send_mail: invalid recipient $to");
        return false;
    }

    $fromEmail = 'no-reply@mubeetech.com.ng';
    $fromName  = APP_NAME;

    // Headers
    $headers   = [];
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/html; charset=UTF-8";
    $headers[] = "From: {$fromName} <{$fromEmail}>";
    $headers[] = "X-Mailer: PHP/" . phpversion();

    if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers[] = "Reply-To: {$replyTo}";
    }

    $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $html = mail_template($subject, $htmlBody);

    // Send
    $sent = @mail($to, $subject, $html, implode("\r\n", $headers));

    // Log
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    @file_put_contents(
        $logDir . '/mail.log',
        date('Y-m-d H:i:s') . " | TO: $to | SUBJECT: $subject | SENT: " . ($sent ? '1' : '0') . "\n",
        FILE_APPEND
    );

    return $sent;
}

// ============================================
// HTML EMAIL TEMPLATE
// ============================================

/**
 * Wrap email body in a branded HTML template.
 */
function mail_template($title, $bodyHtml) {
    $brand   = '#22723B';
    $accent  = '#F18114';
    $dark    = '#22612E';

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
</head>
<body style="margin:0;padding:0;background:#F8F9FA;font-family:Arial,Helvetica,sans-serif;color:#2E353D;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F8F9FA;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(20,24,29,0.08);">
                    <tr>
                        <td style="background:{$brand};padding:24px;text-align:center;">
                            <div style="font-size:22px;font-weight:800;color:#FFFFFF;letter-spacing:0.5px;">
                                🌱 AgriConnect
                            </div>
                            <div style="font-size:12px;color:#CDE3D2;margin-top:4px;">
                                Powered by MubeeTech
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            {$bodyHtml}
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#F1F3F5;padding:20px;text-align:center;font-size:12px;color:#6C7680;">
                            This message was sent by AgriConnect.<br>
                            If you did not request this email, please ignore it.<br>
                            <a href="https://biz.mubeetech.com.ng" style="color:{$brand};text-decoration:none;font-weight:600;">biz.mubeetech.com.ng</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

// ============================================
// CONVENIENCE SENDERS
// ============================================

function mail_welcome($to, $name) {
    $body = '
        <h2 style="margin:0 0 16px;color:#22612E;">Welcome to AgriConnect</h2>
        <p>Hello ' . htmlspecialchars($name) . ',</p>
        <p>Thanks for joining AgriConnect — the USSD-first marketplace connecting farmers to real market prices and trusted agro-dealers across Northern Nigeria.</p>
        <p>Dial <strong>*384*0032#</strong> from any phone to get started.</p>
        <p>— The AgriConnect Team</p>
    ';
    return send_mail($to, 'Welcome to AgriConnect', $body);
}

function mail_contact_ack($to, $name) {
    $body = '
        <h2 style="margin:0 0 16px;color:#22612E;">We received your message</h2>
        <p>Hi ' . htmlspecialchars($name) . ',</p>
        <p>Thanks for reaching out to AgriConnect. Our team will get back to you within 24 hours.</p>
        <p>— The AgriConnect Team</p>
    ';
    return send_mail($to, 'We received your message', $body);
}

function mail_admin_notification($subject, $message) {
    $adminEmail = 'admin@mubeetech.com.ng';
    $body = '
        <h2 style="margin:0 0 16px;color:#22612E;">' . htmlspecialchars($subject) . '</h2>
        <p>' . nl2br(htmlspecialchars($message)) . '</p>
    ';
    return send_mail($adminEmail, '[AgriConnect] ' . $subject, $body);
}
?>