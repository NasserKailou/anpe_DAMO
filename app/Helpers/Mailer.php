<?php
/**
 * Mailer — Service d'envoi d'emails via SMTP natif PHP
 * Supporte TLS/STARTTLS, HTML, pièces jointes
 */
namespace App\Helpers;

class Mailer
{
    private string $host;
    private int    $port;
    private string $username;
    private string $password;
    private string $from;
    private string $fromName;
    private string $encryption;

    // File d'attente en mémoire (si MAIL_ENABLED=false, on log seulement)
    private static array $sentLog = [];

    public function __construct()
    {
        $this->host       = MAIL_HOST;
        $this->port       = MAIL_PORT;
        $this->username   = MAIL_USERNAME;
        $this->password   = MAIL_PASSWORD;
        $this->from       = MAIL_FROM;
        $this->fromName   = MAIL_FROM_NAME;
        $this->encryption = MAIL_ENCRYPTION;
    }

    /**
     * Envoyer un email HTML
     * @param string|array $to   Destinataire(s) : 'email' ou ['email' => 'Nom', ...]
     * @param string $subject    Sujet
     * @param string $htmlBody   Corps HTML
     * @param string $textBody   Corps texte brut (optionnel)
     * @param array  $cc         CC (optionnel)
     * @param array  $attachments Pièces jointes [['path'=>'...','name'=>'...']]
     */
    public function send(
        string|array $to,
        string $subject,
        string $htmlBody,
        string $textBody = '',
        array  $cc = [],
        array  $attachments = []
    ): bool {
        // Si mail désactivé → log seulement
        if (!MAIL_ENABLED) {
            $this->logEmail($to, $subject, $htmlBody);
            return true;
        }

        try {
            return $this->sendSmtp($to, $subject, $htmlBody, $textBody, $cc, $attachments);
        } catch (\Exception $e) {
            error_log('[Mailer] Erreur envoi: ' . $e->getMessage());
            $this->logEmail($to, $subject, $htmlBody, 'FAILED: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoi SMTP réel via socket
     */
    private function sendSmtp(
        string|array $to,
        string $subject,
        string $htmlBody,
        string $textBody,
        array  $cc,
        array  $attachments
    ): bool {
        $socket = null;
        try {
            // Connexion au serveur SMTP
            $timeout = 15;
            if ($this->encryption === 'ssl') {
                $socket = fsockopen("ssl://{$this->host}", $this->port, $errno, $errstr, $timeout);
            } else {
                $socket = fsockopen($this->host, $this->port, $errno, $errstr, $timeout);
            }

            if (!$socket) {
                throw new \RuntimeException("Connexion SMTP échouée: $errstr ($errno)");
            }
            stream_set_timeout($socket, $timeout);

            // Lire le message de bienvenue
            $this->readResponse($socket, 220);

            // EHLO
            $this->sendCommand($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250);

            // STARTTLS si nécessaire
            if ($this->encryption === 'tls') {
                $this->sendCommand($socket, 'STARTTLS', 220);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException("Échec activation TLS");
                }
                $this->sendCommand($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250);
            }

            // Authentification
            $this->sendCommand($socket, 'AUTH LOGIN', 334);
            $this->sendCommand($socket, base64_encode($this->username), 334);
            $this->sendCommand($socket, base64_encode($this->password), 235);

            // Expéditeur
            $this->sendCommand($socket, "MAIL FROM:<{$this->from}>", 250);

            // Destinataire(s)
            $recipients = $this->parseRecipients($to);
            foreach ($recipients as $email => $name) {
                $this->sendCommand($socket, "RCPT TO:<$email>", 250);
            }
            foreach ($cc as $email => $name) {
                if (is_int($email)) { $email = $name; $name = ''; }
                $this->sendCommand($socket, "RCPT TO:<$email>", 250);
            }

            // Corps du message
            $this->sendCommand($socket, 'DATA', 354);

            $boundary = '----=_Part_' . md5(uniqid());
            $message  = $this->buildMessage(
                $recipients, $subject, $htmlBody, $textBody,
                $cc, $boundary, $attachments
            );
            fwrite($socket, $message . "\r\n.\r\n");
            $this->readResponse($socket, 250);

            // Fermeture
            $this->sendCommand($socket, 'QUIT', 221);
            fclose($socket);

            $this->logEmail($to, $subject, $htmlBody, 'SENT');
            return true;

        } catch (\Exception $e) {
            if ($socket) { fclose($socket); }
            throw $e;
        }
    }

    /**
     * Construire les headers + corps du message MIME
     */
    private function buildMessage(
        array  $recipients,
        string $subject,
        string $htmlBody,
        string $textBody,
        array  $cc,
        string $boundary,
        array  $attachments
    ): string {
        $fromEncoded = '=?UTF-8?B?' . base64_encode($this->fromName) . '?=';
        $subjectEnc  = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        // Construction des To:
        $toList = [];
        foreach ($recipients as $email => $name) {
            $toList[] = $name ? "\"$name\" <$email>" : "<$email>";
        }

        $headers  = "From: $fromEncoded <{$this->from}>\r\n";
        $headers .= "To: " . implode(', ', $toList) . "\r\n";

        if (!empty($cc)) {
            $ccList = [];
            foreach ($cc as $email => $name) {
                if (is_int($email)) { $email = $name; $name = ''; }
                $ccList[] = $name ? "\"$name\" <$email>" : "<$email>";
            }
            $headers .= "Cc: " . implode(', ', $ccList) . "\r\n";
        }

        $headers .= "Subject: $subjectEnc\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "X-Mailer: e-DAMO Mailer\r\n";

        $hasAttachments = !empty($attachments);
        $outerBoundary  = $hasAttachments ? 'outer_' . $boundary : $boundary;

        if ($hasAttachments) {
            $headers .= "Content-Type: multipart/mixed; boundary=\"$outerBoundary\"\r\n";
        } else {
            $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
        }
        $headers .= "\r\n";

        $body = '';

        if ($hasAttachments) {
            $body .= "--$outerBoundary\r\n";
            $body .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n\r\n";
        }

        // Partie texte
        if (!$textBody) {
            $textBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $htmlBody));
        }
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($textBody)) . "\r\n";

        // Partie HTML
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($htmlBody)) . "\r\n";
        $body .= "--$boundary--\r\n";

        // Pièces jointes
        if ($hasAttachments) {
            foreach ($attachments as $att) {
                if (!file_exists($att['path'])) continue;
                $attData = base64_encode(file_get_contents($att['path']));
                $attName = $att['name'] ?? basename($att['path']);
                $attMime = $att['mime'] ?? 'application/octet-stream';

                $body .= "--$outerBoundary\r\n";
                $body .= "Content-Type: $attMime; name=\"$attName\"\r\n";
                $body .= "Content-Disposition: attachment; filename=\"$attName\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $body .= chunk_split($attData) . "\r\n";
            }
            $body .= "--$outerBoundary--\r\n";
        }

        return $headers . $body;
    }

    private function sendCommand($socket, string $command, int $expectedCode): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->readResponse($socket, $expectedCode);
    }

    private function readResponse($socket, int $expectedCode): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break; // Fin de réponse multi-lignes
        }
        $code = (int)substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new \RuntimeException("SMTP erreur $code (attendu $expectedCode): $response");
        }
        return $response;
    }

    private function parseRecipients(string|array $to): array
    {
        if (is_string($to)) {
            return [$to => ''];
        }
        $result = [];
        foreach ($to as $key => $val) {
            if (is_int($key)) {
                $result[$val] = ''; // ['email1', 'email2']
            } else {
                $result[$key] = $val; // ['email' => 'Nom']
            }
        }
        return $result;
    }

    /**
     * Logger l'email dans la base (table logs_activite) ou fichier
     */
    private function logEmail(string|array $to, string $subject, string $body, string $status = 'QUEUED'): void
    {
        $toStr = is_array($to) ? implode(', ', array_keys($to)) : $to;
        error_log("[Mailer][$status] To: $toStr | Subject: $subject");
        self::$sentLog[] = compact('toStr', 'subject', 'status');

        // Sauvegarder dans la DB si possible (executeRaw utilise des ? purs)
        try {
            $db = \App\Models\Database::getInstance();
            $db->executeRaw(
                "INSERT INTO logs_activite (action, ressource, details, statut, created_at)
                 VALUES (?, ?, ?, ?, NOW())",
                ['email_sent', 'mailer', json_encode([
                    'to'      => $toStr,
                    'subject' => $subject,
                    'status'  => $status,
                ]), $status === 'SENT' ? 'success' : 'info']
            );
        } catch (\Exception $e) {
            // Silencieux
        }
    }

    public static function getSentLog(): array { return self::$sentLog; }
}
