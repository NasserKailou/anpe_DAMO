<?php
/**
 * Contrôleur Profil
 */
namespace App\Controllers;

class ProfileController extends BaseController
{
    public function index(): void
    {
        $userDetails = $this->db->fetchOne("SELECT * FROM utilisateurs WHERE id = $1", [$_SESSION['user_id']]);

        $this->render('profil.index', [
            'pageTitle'   => 'Mon profil - ' . APP_NAME,
            'userDetails' => $userDetails,
        ]);
    }

    public function update(): void
    {
        $this->requireCsrf();
        $nom       = sanitize(post('nom', ''));
        $prenom    = sanitize(post('prenom', ''));
        $telephone = sanitize(post('telephone', ''));

        $this->db->execute(
            "UPDATE utilisateurs SET nom=$1, prenom=$2, telephone=$3, updated_at=NOW() WHERE id=$4",
            [$nom, $prenom, $telephone, $_SESSION['user_id']]
        );

        // Mettre à jour la session
        $_SESSION['user']['nom']    = $nom;
        $_SESSION['user']['prenom'] = $prenom;

        redirectWith('profil', 'success', 'Profil mis à jour avec succès.');
    }

    public function changePassword(): void
    {
        $this->requireCsrf();
        $currentPwd = post('current_password', '');
        $newPwd     = post('password', '');
        $confirmPwd = post('password_confirm', '');

        $user = $this->db->fetchOne("SELECT mot_de_passe FROM utilisateurs WHERE id = $1", [$_SESSION['user_id']]);

        if (!\App\Helpers\Security::verifyPassword($currentPwd, $user['mot_de_passe'])) {
            redirectWith('profil', 'error', 'Mot de passe actuel incorrect.');
        }

        if ($newPwd !== $confirmPwd) {
            redirectWith('profil', 'error', 'Les nouveaux mots de passe ne correspondent pas.');
        }

        $errors = \App\Helpers\Security::checkPasswordStrength($newPwd);
        if ($errors) {
            redirectWith('profil', 'error', implode(', ', $errors));
        }

        $hash = \App\Helpers\Security::hashPassword($newPwd);
        $this->db->execute(
            "UPDATE utilisateurs SET mot_de_passe = $1, updated_at = NOW() WHERE id = $2",
            [$hash, $_SESSION['user_id']]
        );

        logActivity('password_changed', 'profil', $_SESSION['user_id']);
        redirectWith('profil', 'success', 'Mot de passe modifié avec succès.');
    }
}
