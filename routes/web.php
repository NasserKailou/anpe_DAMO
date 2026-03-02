<?php
/**
 * Routes de l'application e-DAMO
 */
defined('EDAMO') or die('Accès direct interdit');

use App\Helpers\Router;

// ============================================================
// ROUTES PUBLIQUES (Grand public)
// ============================================================

// Page d'accueil publique
Router::get('/', ['PublicController', 'index']);
Router::get('/accueil', ['PublicController', 'index']);

// Tableau de bord public / statistiques
Router::get('/statistiques', ['PublicController', 'statistiques']);
Router::get('/donnees', ['PublicController', 'donnees']);

// Guides et documents
Router::get('/guides', ['PublicController', 'guides']);
Router::get('/guide/:id/telecharger', ['PublicController', 'telechargerGuide']);

// API publique pour les graphiques (AJAX)
Router::get('/api/public/stats', ['ApiController', 'statsPubliques']);
Router::get('/api/public/chart/:type', ['ApiController', 'chartData']);

// ============================================================
// AUTHENTIFICATION
// ============================================================

Router::any('/login', ['AuthController', 'login']);
Router::get('/logout', ['AuthController', 'logout']);
Router::any('/mot-de-passe-oublie', ['AuthController', 'forgotPassword']);
Router::any('/reinitialiser-mot-de-passe/:token', ['AuthController', 'resetPassword']);

// ============================================================
// TABLEAU DE BORD (commun aux rôles authentifiés)
// ============================================================

Router::get('/dashboard', ['DashboardController', 'index'], ['AuthMiddleware']);

// ============================================================
// MODULE AGENT (saisie des déclarations)
// ============================================================

Router::get('/agent/dashboard', ['AgentController', 'dashboard'], ['AuthMiddleware', 'AgentMiddleware']);

// Entreprises
Router::get('/agent/entreprises', ['AgentController', 'entreprises'], ['AuthMiddleware', 'AgentMiddleware']);
Router::get('/agent/entreprise/nouvelle', ['AgentController', 'nouvelleEntreprise'], ['AuthMiddleware', 'AgentMiddleware']);
Router::post('/agent/entreprise/nouvelle', ['AgentController', 'creerEntreprise'], ['AuthMiddleware', 'AgentMiddleware']);
Router::get('/agent/entreprise/:id/modifier', ['AgentController', 'modifierEntreprise'], ['AuthMiddleware', 'AgentMiddleware']);
Router::post('/agent/entreprise/:id/modifier', ['AgentController', 'updateEntreprise'], ['AuthMiddleware', 'AgentMiddleware']);

// Déclarations
Router::get('/agent/declarations', ['DeclarationController', 'index'], ['AuthMiddleware', 'AgentMiddleware']);
Router::get('/agent/declaration/nouvelle', ['DeclarationController', 'nouvelle'], ['AuthMiddleware', 'AgentMiddleware']);
Router::post('/agent/declaration/nouvelle', ['DeclarationController', 'creer'], ['AuthMiddleware', 'AgentMiddleware']);
Router::get('/agent/declaration/:id/saisie', ['DeclarationController', 'saisie'], ['AuthMiddleware', 'AgentMiddleware']);
Router::post('/agent/declaration/:id/sauvegarder', ['DeclarationController', 'sauvegarder'], ['AuthMiddleware', 'AgentMiddleware']);
Router::post('/agent/declaration/:id/soumettre', ['DeclarationController', 'soumettre'], ['AuthMiddleware', 'AgentMiddleware']);
Router::get('/agent/declaration/:id/apercu', ['DeclarationController', 'apercu'], ['AuthMiddleware', 'AgentMiddleware']);
Router::get('/agent/declaration/:id/modifier', ['DeclarationController', 'modifier'], ['AuthMiddleware', 'AgentMiddleware']);
Router::post('/agent/declaration/:id/corriger', ['DeclarationController', 'corriger'], ['AuthMiddleware', 'AgentMiddleware']);

// API AJAX pour la saisie en étapes
Router::post('/api/declaration/:id/etape/:etape', ['ApiController', 'sauvegarderEtape'], ['AuthMiddleware']);
Router::get('/api/declaration/:id/etape/:etape', ['ApiController', 'getEtape'], ['AuthMiddleware']);
Router::get('/api/departements/:region_id', ['ApiController', 'getDepartements']);
Router::get('/api/communes/:dept_id', ['ApiController', 'getCommunes']);
Router::get('/api/entreprise/recherche', ['ApiController', 'rechercheEntreprise'], ['AuthMiddleware']);

// ============================================================
// MODULE ADMINISTRATION
// ============================================================

Router::get('/admin/dashboard', ['AdminController', 'dashboard'], ['AuthMiddleware', 'AdminMiddleware']);

// Gestion des utilisateurs
Router::get('/admin/utilisateurs', ['AdminController', 'utilisateurs'], ['AuthMiddleware', 'AdminMiddleware']);
Router::get('/admin/utilisateur/nouveau', ['AdminController', 'nouvelUtilisateur'], ['AuthMiddleware', 'AdminMiddleware']);
Router::post('/admin/utilisateur/nouveau', ['AdminController', 'creerUtilisateur'], ['AuthMiddleware', 'AdminMiddleware']);
Router::get('/admin/utilisateur/:id/modifier', ['AdminController', 'modifierUtilisateur'], ['AuthMiddleware', 'AdminMiddleware']);
Router::post('/admin/utilisateur/:id/modifier', ['AdminController', 'updateUtilisateur'], ['AuthMiddleware', 'AdminMiddleware']);
Router::post('/admin/utilisateur/:id/toggle', ['AdminController', 'toggleUtilisateur'], ['AuthMiddleware', 'AdminMiddleware']);
Router::post('/admin/utilisateur/:id/supprimer', ['AdminController', 'supprimerUtilisateur'], ['AuthMiddleware', 'AdminMiddleware']);

// Gestion des déclarations (admin)
Router::get('/admin/declarations', ['AdminController', 'declarations'], ['AuthMiddleware', 'AdminMiddleware']);
Router::get('/admin/declaration/:id', ['AdminController', 'voirDeclaration'], ['AuthMiddleware', 'AdminMiddleware']);
Router::post('/admin/declaration/:id/valider', ['AdminController', 'validerDeclaration'], ['AuthMiddleware', 'AdminMiddleware']);
Router::post('/admin/declaration/:id/rejeter', ['AdminController', 'rejeterDeclaration'], ['AuthMiddleware', 'AdminMiddleware']);
Router::get('/admin/declaration/:id/exporter', ['AdminController', 'exporterDeclaration'], ['AuthMiddleware', 'AdminMiddleware']);

// Exports
Router::get('/admin/export/declarations', ['AdminController', 'exportDeclarations'], ['AuthMiddleware', 'AdminMiddleware']);
Router::get('/admin/export/entreprises', ['AdminController', 'exportEntreprises'], ['AuthMiddleware', 'AdminMiddleware']);

// Statistiques admin
Router::get('/admin/statistiques', ['AdminController', 'statistiques'], ['AuthMiddleware', 'AdminMiddleware']);
Router::get('/api/admin/stats', ['ApiController', 'statsAdmin'], ['AuthMiddleware', 'AdminMiddleware']);

// Gestion des campagnes
Router::get('/admin/campagnes', ['AdminController', 'campagnes'], ['AuthMiddleware', 'AdminMiddleware']);
Router::get('/admin/campagne/nouvelle', ['AdminController', 'nouvelleCampagne'], ['AuthMiddleware', 'AdminMiddleware']);
Router::post('/admin/campagne/nouvelle', ['AdminController', 'creerCampagne'], ['AuthMiddleware', 'AdminMiddleware']);
Router::get('/admin/campagne/:id/modifier', ['AdminController', 'modifierCampagne'], ['AuthMiddleware', 'AdminMiddleware']);
Router::post('/admin/campagne/:id/modifier', ['AdminController', 'updateCampagne'], ['AuthMiddleware', 'AdminMiddleware']);

// Gestion des guides
Router::get('/admin/guides', ['AdminController', 'guides'], ['AuthMiddleware', 'AdminMiddleware']);
Router::get('/admin/guide/nouveau', ['AdminController', 'nouveauGuide'], ['AuthMiddleware', 'AdminMiddleware']);
Router::post('/admin/guide/nouveau', ['AdminController', 'uploadGuide'], ['AuthMiddleware', 'AdminMiddleware']);
Router::post('/admin/guide/:id/supprimer', ['AdminController', 'supprimerGuide'], ['AuthMiddleware', 'AdminMiddleware']);

// Gestion des branches d'activité
Router::get('/admin/branches', ['AdminController', 'branches'], ['AuthMiddleware', 'AdminMiddleware']);
Router::post('/admin/branche/sauvegarder', ['AdminController', 'sauvegarderBranche'], ['AuthMiddleware', 'AdminMiddleware']);

// Paramètres système
Router::get('/admin/parametres', ['AdminController', 'parametres'], ['AuthMiddleware', 'AdminMiddleware']);
Router::post('/admin/parametres', ['AdminController', 'updateParametres'], ['AuthMiddleware', 'AdminMiddleware']);

// Logs
Router::get('/admin/logs', ['AdminController', 'logs'], ['AuthMiddleware', 'AdminMiddleware']);

// Profil utilisateur (tous les rôles)
Router::get('/profil', ['ProfileController', 'index'], ['AuthMiddleware']);
Router::post('/profil/modifier', ['ProfileController', 'update'], ['AuthMiddleware']);
Router::post('/profil/mot-de-passe', ['ProfileController', 'changePassword'], ['AuthMiddleware']);

// Dispatcher
Router::dispatch();
