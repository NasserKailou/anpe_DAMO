<?php
/**
 * Contrôleur Dashboard général (redirige selon le rôle)
 */
namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index(): void
    {
        if (isAdmin()) {
            redirect('admin/dashboard');
        } elseif (isAgent()) {
            redirect('agent/dashboard');
        } else {
            redirect('login');
        }
    }
}
