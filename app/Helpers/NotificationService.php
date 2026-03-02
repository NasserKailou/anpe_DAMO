<?php
/**
 * NotificationService — Templates d'emails pour e-DAMO
 * Envoie les notifications aux agents et admins
 */
namespace App\Helpers;

class NotificationService
{
    private Mailer $mailer;

    public function __construct()
    {
        $this->mailer = new Mailer();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NOTIFICATIONS AGENT
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Email de bienvenue / création de compte agent
     */
    public function welcomeAgent(array $user, string $plainPassword): bool
    {
        $subject = 'Bienvenue sur e-DAMO — Vos identifiants de connexion';
        $html = $this->layout(
            "Bienvenue sur e-DAMO",
            "
            <p>Bonjour <strong>" . htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . "</strong>,</p>
            <p>Votre compte agent a été créé sur la plateforme <strong>e-DAMO</strong> de l'ANPE Niger.</p>
            <table style='width:100%;border-collapse:collapse;margin:20px 0'>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold;width:40%'>Email</td>
                    <td style='padding:8px;border:1px solid #dee2e6'>" . htmlspecialchars($user['email']) . "</td></tr>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold'>Mot de passe</td>
                    <td style='padding:8px;border:1px solid #dee2e6'><code>$plainPassword</code></td></tr>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold'>Région</td>
                    <td style='padding:8px;border:1px solid #dee2e6'>" . htmlspecialchars($user['region_nom'] ?? 'N/A') . "</td></tr>
            </table>
            <p style='color:#dc3545'><strong>⚠️ Changez votre mot de passe dès la première connexion.</strong></p>
            " . $this->btnPrimary('Se connecter maintenant', APP_URL . '/login')
        );
        return $this->mailer->send($user['email'], $subject, $html);
    }

    /**
     * Confirmation de soumission d'une déclaration
     */
    public function declarationSoumise(array $declaration, array $agent): bool
    {
        $subject = "Déclaration #{$declaration['code_questionnaire']} soumise avec succès";
        $html = $this->layout(
            "Déclaration soumise",
            "
            <p>Bonjour <strong>" . htmlspecialchars($agent['prenom'] . ' ' . $agent['nom']) . "</strong>,</p>
            <p>Votre déclaration a été soumise avec succès et est en attente de validation.</p>
            <table style='width:100%;border-collapse:collapse;margin:20px 0'>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold;width:40%'>Code questionnaire</td>
                    <td style='padding:8px;border:1px solid #dee2e6'><strong>{$declaration['code_questionnaire']}</strong></td></tr>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold'>Entreprise</td>
                    <td style='padding:8px;border:1px solid #dee2e6'>" . htmlspecialchars($declaration['raison_sociale'] ?? '') . "</td></tr>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold'>Campagne</td>
                    <td style='padding:8px;border:1px solid #dee2e6'>" . htmlspecialchars($declaration['campagne_libelle'] ?? $declaration['annee'] ?? '') . "</td></tr>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold'>Date de soumission</td>
                    <td style='padding:8px;border:1px solid #dee2e6'>" . date('d/m/Y à H:i') . "</td></tr>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold'>Statut</td>
                    <td style='padding:8px;border:1px solid #dee2e6'><span style='color:#fd7e14;font-weight:bold'>⏳ En attente de validation</span></td></tr>
            </table>
            <p>Vous serez notifié(e) dès que votre déclaration aura été traitée par l'administrateur.</p>
            " . $this->btnPrimary('Voir ma déclaration', APP_URL . "/agent/declaration/{$declaration['id']}/apercu")
        );
        return $this->mailer->send($agent['email'], $subject, $html);
    }

    /**
     * Notification de validation d'une déclaration
     */
    public function declarationValidee(array $declaration, array $agent, string $observations = ''): bool
    {
        $subject = "✅ Déclaration #{$declaration['code_questionnaire']} validée";
        $obsHtml = $observations
            ? "<div style='background:#d1fae5;border-left:4px solid #059669;padding:12px;margin:16px 0'><strong>Observations :</strong><br>" . htmlspecialchars($observations) . "</div>"
            : '';
        $html = $this->layout(
            "Déclaration validée",
            "
            <p>Bonjour <strong>" . htmlspecialchars($agent['prenom'] . ' ' . $agent['nom']) . "</strong>,</p>
            <div style='background:#d1fae5;border:1px solid #059669;border-radius:8px;padding:20px;margin:20px 0;text-align:center'>
                <p style='font-size:2em;margin:0'>✅</p>
                <p style='color:#065f46;font-size:1.1em;font-weight:bold;margin:8px 0'>
                    Votre déclaration a été validée avec succès !
                </p>
            </div>
            <table style='width:100%;border-collapse:collapse;margin:20px 0'>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold;width:40%'>Code</td>
                    <td style='padding:8px;border:1px solid #dee2e6'><strong>{$declaration['code_questionnaire']}</strong></td></tr>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold'>Entreprise</td>
                    <td style='padding:8px;border:1px solid #dee2e6'>" . htmlspecialchars($declaration['raison_sociale'] ?? '') . "</td></tr>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold'>Date de validation</td>
                    <td style='padding:8px;border:1px solid #dee2e6'>" . date('d/m/Y à H:i') . "</td></tr>
            </table>
            $obsHtml
            <p>Merci pour votre contribution à la collecte des données sur la main-d'œuvre au Niger.</p>
            " . $this->btnPrimary("Voir l'aperçu", APP_URL . "/agent/declaration/{$declaration['id']}/apercu")
        );
        return $this->mailer->send($agent['email'], $subject, $html);
    }

    /**
     * Notification de rejet d'une déclaration
     */
    public function declarationRejetee(array $declaration, array $agent, string $motif): bool
    {
        $subject = "❌ Déclaration #{$declaration['code_questionnaire']} — Corrections requises";
        $html = $this->layout(
            "Déclaration rejetée — Corrections requises",
            "
            <p>Bonjour <strong>" . htmlspecialchars($agent['prenom'] . ' ' . $agent['nom']) . "</strong>,</p>
            <div style='background:#fee2e2;border:1px solid #dc2626;border-radius:8px;padding:20px;margin:20px 0'>
                <p style='font-size:1.5em;margin:0 0 8px 0'>❌ Corrections requises</p>
                <p style='color:#7f1d1d;margin:0'>
                    Votre déclaration <strong>#{$declaration['code_questionnaire']}</strong> a été rejetée.
                    Veuillez la corriger et la soumettre à nouveau.
                </p>
            </div>
            <div style='background:#fef3c7;border-left:4px solid #d97706;padding:16px;margin:16px 0'>
                <strong>Motif du rejet :</strong><br>
                <p style='margin:8px 0'>" . htmlspecialchars($motif) . "</p>
            </div>
            <p><strong>Que faire maintenant ?</strong></p>
            <ol>
                <li>Consultez le motif du rejet ci-dessus</li>
                <li>Accédez à votre déclaration en cliquant sur le bouton ci-dessous</li>
                <li>Corrigez les informations demandées</li>
                <li>Soumettez à nouveau votre déclaration</li>
            </ol>
            " . $this->btnDanger('Corriger ma déclaration', APP_URL . "/agent/declaration/{$declaration['id']}/modifier")
        );
        return $this->mailer->send($agent['email'], $subject, $html);
    }

    /**
     * Rappel de délai avant clôture de la campagne
     */
    public function rappelClotureCampagne(array $agent, array $campagne, int $joursRestants): bool
    {
        $subject = "⏰ Rappel : {$joursRestants} jour(s) avant la clôture de la campagne DAMO";
        $html = $this->layout(
            "Rappel — Clôture de campagne",
            "
            <p>Bonjour <strong>" . htmlspecialchars($agent['prenom'] . ' ' . $agent['nom']) . "</strong>,</p>
            <div style='background:#fef3c7;border:1px solid #d97706;border-radius:8px;padding:20px;margin:20px 0;text-align:center'>
                <p style='font-size:2em;margin:0'>⏰</p>
                <p style='color:#92400e;font-size:1.1em;font-weight:bold;margin:8px 0'>
                    Il reste <strong>$joursRestants jour(s)</strong> pour soumettre vos déclarations
                </p>
                <p style='color:#78350f;margin:0'>
                    Campagne : <strong>" . htmlspecialchars($campagne['libelle']) . "</strong><br>
                    Date de clôture : <strong>" . date('d/m/Y', strtotime($campagne['date_fin'])) . "</strong>
                </p>
            </div>
            <p>Assurez-vous que toutes vos déclarations sont soumises avant la date de clôture.</p>
            <p>Les déclarations non soumises à temps ne pourront pas être prises en compte.</p>
            " . $this->btnPrimary('Accéder à mes déclarations', APP_URL . '/agent/declarations')
        );
        return $this->mailer->send($agent['email'], $subject, $html);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NOTIFICATIONS ADMIN
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Notification aux admins : nouvelle déclaration soumise
     */
    public function notifierAdminsNouvelleDeclaration(array $admins, array $declaration): bool
    {
        if (empty($admins)) return true;
        $emails = array_column($admins, 'email');
        $subject = "📋 Nouvelle déclaration soumise — {$declaration['code_questionnaire']}";
        $html = $this->layout(
            "Nouvelle déclaration à valider",
            "
            <p>Une nouvelle déclaration vient d'être soumise et attend votre validation.</p>
            <table style='width:100%;border-collapse:collapse;margin:20px 0'>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold;width:40%'>Code</td>
                    <td style='padding:8px;border:1px solid #dee2e6'><strong>{$declaration['code_questionnaire']}</strong></td></tr>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold'>Entreprise</td>
                    <td style='padding:8px;border:1px solid #dee2e6'>" . htmlspecialchars($declaration['raison_sociale'] ?? '') . "</td></tr>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold'>Région</td>
                    <td style='padding:8px;border:1px solid #dee2e6'>" . htmlspecialchars($declaration['region_nom'] ?? '') . "</td></tr>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold'>Agent</td>
                    <td style='padding:8px;border:1px solid #dee2e6'>" . htmlspecialchars(($declaration['agent_prenom'] ?? '') . ' ' . ($declaration['agent_nom'] ?? '')) . "</td></tr>
                <tr><td style='padding:8px;background:#f8f9fa;font-weight:bold'>Date</td>
                    <td style='padding:8px;border:1px solid #dee2e6'>" . date('d/m/Y à H:i') . "</td></tr>
            </table>
            " . $this->btnPrimary('Valider maintenant', APP_URL . "/admin/declaration/{$declaration['id']}")
        );
        return $this->mailer->send($emails, $subject, $html);
    }

    /**
     * Réinitialisation de mot de passe
     */
    public function resetPassword(string $email, string $token, string $nom): bool
    {
        $resetUrl = APP_URL . "/reinitialiser-mot-de-passe/$token";
        $subject  = 'Réinitialisation de votre mot de passe — e-DAMO';
        $html = $this->layout(
            "Réinitialisation de mot de passe",
            "
            <p>Bonjour <strong>" . htmlspecialchars($nom) . "</strong>,</p>
            <p>Vous avez demandé la réinitialisation de votre mot de passe sur e-DAMO.</p>
            <p>Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe :</p>
            " . $this->btnPrimary('Réinitialiser mon mot de passe', $resetUrl) . "
            <p style='color:#6b7280;font-size:0.9em'>Ce lien est valable pendant <strong>1 heure</strong>.</p>
            <p style='color:#6b7280;font-size:0.9em'>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
            <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0'>
            <p style='color:#6b7280;font-size:0.85em'>Ou copiez ce lien dans votre navigateur :<br>
            <a href='$resetUrl' style='color:#2563eb;word-break:break-all'>$resetUrl</a></p>"
        );
        return $this->mailer->send($email, $subject, $html);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS DE TEMPLATE
    // ─────────────────────────────────────────────────────────────────────────

    private function layout(string $title, string $content): string
    {
        $year    = date('Y');
        $appName = APP_NAME ?? 'e-DAMO';
        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>$title — $appName</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif">
<table width="100%" style="background:#f3f4f6;padding:40px 0">
  <tr><td align="center">
    <table width="600" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,.07)">
      <!-- Header -->
      <tr><td style="background:linear-gradient(135deg,#1d4ed8,#7c3aed);padding:32px;text-align:center">
        <h1 style="color:#fff;margin:0;font-size:24px;font-weight:700">🏛️ $appName</h1>
        <p style="color:#bfdbfe;margin:8px 0 0;font-size:14px">ANPE Niger — Déclaration Annuelle de la Main d'Œuvre</p>
      </td></tr>
      <!-- Corps -->
      <tr><td style="padding:32px 40px;color:#111827;line-height:1.6;font-size:15px">
        <h2 style="color:#1e40af;font-size:18px;margin:0 0 20px 0">$title</h2>
        $content
      </td></tr>
      <!-- Footer -->
      <tr><td style="background:#f9fafb;padding:20px 40px;text-align:center;font-size:12px;color:#6b7280;border-top:1px solid #e5e7eb">
        <p style="margin:0">© $year <strong>$appName</strong> — ANPE Niger</p>
        <p style="margin:4px 0 0">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }

    private function btnPrimary(string $text, string $url): string
    {
        return "<div style='text-align:center;margin:28px 0'>
            <a href='$url' style='background:#1d4ed8;color:#fff;padding:14px 32px;border-radius:8px;
               text-decoration:none;font-weight:600;font-size:15px;display:inline-block'>
            $text
            </a></div>";
    }

    private function btnDanger(string $text, string $url): string
    {
        return "<div style='text-align:center;margin:28px 0'>
            <a href='$url' style='background:#dc2626;color:#fff;padding:14px 32px;border-radius:8px;
               text-decoration:none;font-weight:600;font-size:15px;display:inline-block'>
            $text
            </a></div>";
    }
}
