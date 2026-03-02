<?php // Vue : Gestion des utilisateurs (Admin) ?>

<div class="page-header">
    <div>
        <h1><i class="bi bi-people me-2 text-primary"></i>Utilisateurs</h1>
        <p>Total : <strong><?= formatNumber($total) ?></strong> utilisateur(s)</p>
    </div>
    <div class="page-header-right">
        <a href="/admin/utilisateur/nouveau" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus me-1"></i>Nouvel utilisateur
        </a>
    </div>
</div>

<!-- Filtres -->
<div class="filters-bar">
    <form method="GET" action="/admin/utilisateurs">
        <div>
            <input type="text" name="q" value="<?= e($filters['search']) ?>"
                class="form-control form-control-sm" placeholder="Nom, email...">
        </div>
        <div>
            <select name="role" class="form-select form-select-sm">
                <option value="">Tous rôles</option>
                <option value="super_admin" <?= $filters['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                <option value="admin" <?= $filters['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="agent" <?= $filters['role'] === 'agent' ? 'selected' : '' ?>>Agent</option>
            </select>
        </div>
        <div>
            <select name="region" class="form-select form-select-sm">
                <option value="">Toutes régions</option>
                <?php foreach ($regions as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $filters['region'] == $r['id'] ? 'selected' : '' ?>><?= e($r['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filtrer</button>
        <a href="/admin/utilisateurs" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
    </form>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Région</th>
                        <th>Déclarations</th>
                        <th>Statut</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $u): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar" style="width:32px;height:32px;font-size:.75rem">
                                    <?= strtoupper(substr($u['prenom'] ?? 'U', 0, 1) . substr($u['nom'] ?? '', 0, 1)) ?>
                                </div>
                                <div>
                                    <strong><?= e($u['prenom'] . ' ' . $u['nom']) ?></strong>
                                    <?php if ($u['telephone']): ?>
                                    <br><small class="text-muted"><?= e($u['telephone']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><?= e($u['email']) ?></td>
                        <td><span class="badge-role-<?= e($u['role']) ?>"><?= ucfirst(str_replace('_', ' ', $u['role'])) ?></span></td>
                        <td><?= e($u['region_nom'] ?? '-') ?></td>
                        <td class="text-center"><?= $u['nb_declarations'] ?></td>
                        <td>
                            <?php if ($u['actif']): ?>
                            <span class="badge bg-success">Actif</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td><small><?= formatDate($u['created_at']) ?></small></td>
                        <td>
                            <a href="/admin/utilisateur/<?= $u['id'] ?>/modifier" class="btn btn-sm btn-icon btn-outline-primary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($u['role'] !== ROLE_SUPER_ADMIN): ?>
                            <button class="btn btn-sm btn-icon btn-outline-<?= $u['actif'] ? 'warning' : 'success' ?>"
                                title="<?= $u['actif'] ? 'Désactiver' : 'Activer' ?>"
                                onclick="toggleUser(<?= $u['id'] ?>)">
                                <i class="bi bi-<?= $u['actif'] ? 'pause' : 'play' ?>"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($utilisateurs)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-5">Aucun utilisateur trouvé</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($pagination['total_pages'] > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <?php if ($pagination['has_prev']): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?= $pagination['prev_page'] ?>&q=<?= e($filters['search']) ?>">
                &laquo; Précédent
            </a>
        </li>
        <?php endif; ?>
        <li class="page-item disabled">
            <span class="page-link">Page <?= $pagination['current_page'] ?> / <?= $pagination['total_pages'] ?></span>
        </li>
        <?php if ($pagination['has_next']): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?= $pagination['next_page'] ?>&q=<?= e($filters['search']) ?>">
                Suivant &raquo;
            </a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
