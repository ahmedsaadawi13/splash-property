// FILE: /app/helpers/Mailer.php
<?php

/**
 * Mailer Helper (Stub)
 * Basic email functionality - can be extended with PHPMailer or similar
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class Mailer
{
    private $from;
    private $fromName;
    private $subject;
    private $body;
    private $isHtml = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $this->from = $config['mail']['from'] ?? 'noreply@splashproperty.com';
        $this->fromName = $config['mail']['from_name'] ?? 'SplashProperty';
    }

    /**
     * Set sender
     */
    public function setFrom($email, $name = '')
    {
        $this->from = $email;
        $this->fromName = $name;
        return $this;
    }

    /**
     * Set subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set body
     */
    public function setBody($body, $isHtml = true)
    {
        $this->body = $body;
        $this->isHtml = $isHtml;
        return $this;
    }

    /**
     * Send email
     */
    public function send($to, $toName = '')
    {
        $headers = [];

        if ($this->isHtml) {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        }

        $headers[] = 'From: ' . $this->fromName . ' <' . $this->from . '>';
        $headers[] = 'Reply-To: ' . $this->from;
        $headers[] = 'X-Mailer: SplashProperty/1.0';

        // In production, use a proper email service (SMTP, SendGrid, etc.)
        // For now, this is a stub using PHP mail()
        $result = mail($to, $this->subject, $this->body, implode("\r\n", $headers));

        // Log email attempt
        $this->logEmail($to, $result);

        return $result;
    }

    /**
     * Send template
     */
    public function sendTemplate($to, $template, $data = [])
    {
        $templatePath = __DIR__ . '/../views/emails/' . $template . '.php';

        if (!file_exists($templatePath)) {
            return false;
        }

        extract($data);
        ob_start();
        include $templatePath;
        $body = ob_get_clean();

        $this->setBody($body, true);
        return $this->send($to);
    }

    /**
     * Log email
     */
    private function logEmail($to, $success)
    {
        $logFile = __DIR__ . '/../../storage/logs/emails.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $status = $success ? 'SUCCESS' : 'FAILED';
        $timestamp = date('Y-m-d H:i:s');
        $message = "[{$timestamp}] {$status}: To={$to}, Subject={$this->subject}\n";

        file_put_contents($logFile, $message, FILE_APPEND);
    }

    /**
     * Quick send method
     */
    public static function quickSend($to, $subject, $body)
    {
        $mailer = new self();
        return $mailer->setSubject($subject)->setBody($body)->send($to);
    }
}
