<?php
require_once '../includes/config.php';
requireAdmin();

$db = getDB();

// Statistiques générales
$stats = $db->query("
    SELECT 
        (SELECT COUNT(*) FROM maisons) as total_maisons,
        (SELECT COUNT(*) FROM maisons WHERE statut='actif' AND disponibilite='disponible') as maisons_dispo,
        (SELECT COUNT(*) FROM users WHERE role='locataire') as locataires,
        (SELECT COUNT(*) FROM users WHERE role='agent') as agents,
        (SELECT COUNT(*) FROM demandes_location WHERE statut='en_attente') as demandes_attente,
        (SELECT COUNT(*) FROM contrats WHERE statut='actif') as contrats_actifs,
        (SELECT COALESCE(SUM(montant), 0) FROM paiements WHERE statut='valide' AND MONTH(date_paiement)=MONTH(NOW())) as revenus_mois,
        (SELECT COUNT(*) FROM users WHERE DATE(date_creation) = CURDATE()) as inscrits_auj
")->fetch();

// Dernières maisons
$dernieres_maisons = $db->query("
    SELECT m.*, v.nom as ville_nom, u.prenom as agent_prenom, u.nom as agent_nom
    FROM maisons m JOIN villes v ON m.ville_id = v.id JOIN users u ON m.agent_id = u.id
    ORDER BY m.date_creation DESC LIMIT 8
")->fetchAll();

// Derniers utilisateurs
$derniers_users = $db->query("
    SELECT * FROM users ORDER BY date_creation DESC LIMIT 6
")->fetchAll();

// Demandes récentes
$demandes = $db->query("
    SELECT d.*, m.titre as maison_titre, 
           u1.prenom as loc_prenom, u1.nom as loc_nom,
           u2.prenom as agent_prenom, u2.nom as agent_nom
    FROM demandes_location d
    JOIN maisons m ON d.maison_id = m.id
    JOIN users u1 ON d.locataire_id = u1.id
    JOIN users u2 ON d.agent_id = u2.id
    ORDER BY d.date_demande DESC LIMIT 5
")->fetchAll();
?>
<?php include 'includes/sidebar_admin.php'; ?>

<div class="dashboard-main">
    <div class="dashboard-topbar">
        <div>
            <div class="topbar-title">Tableau de bord Admin</div>
            <div style="font-size:12px;color:var(--text-muted)"><?= date('l d F Y', strtotime('now')) ?></div>
        </div>
        <div class="topbar-actions">
            <a href="<?= SITE_URL ?>/admin/maisons.php?action=ajouter" class="btn btn-gold btn-sm">
                <i class="fas fa-plus"></i> Nouvelle annonce
            </a>
            <button class="topbar-icon-btn" onclick="location.reload()"><i class="fas fa-sync-alt"></i></button>
            <a href="<?= SITE_URL ?>/index.php" class="topbar-icon-btn" title="Voir le site"><i class="fas fa-external-link-alt"></i></a>
        </div>
    </div>

    <div class="dashboard-content">
        <!-- Stats cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon gold"><i class="fas fa-home"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total annonces</div>
                    <div class="stat-value"><?= $stats['total_maisons'] ?></div>
                    <div class="stat-change up"><i class="fas fa-arrow-up"></i> <?= $stats['maisons_dispo'] ?> disponibles</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Locataires</div>
                    <div class="stat-value"><?= $stats['locataires'] ?></div>
                    <div class="stat-change up"><i class="fas fa-user-plus"></i> +<?= $stats['inscrits_auj'] ?> auj.</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-user-tie"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Agents</div>
                    <div class="stat-value"><?= $stats['agents'] ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Demandes en attente</div>
                    <div class="stat-value"><?= $stats['demandes_attente'] ?></div>
                    <?php if ($stats['demandes_attente'] > 0): ?>
                    <div class="stat-change down"><i class="fas fa-exclamation"></i> À traiter</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-file-contract"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Contrats actifs</div>
                    <div class="stat-value"><?= $stats['contrats_actifs'] ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-coins"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Revenus ce mois</div>
                    <div class="stat-value" style="font-size:18px"><?= number_format($stats['revenus_mois'],0,',',' ') ?></div>
                    <div class="stat-change" style="color:var(--text-muted)">FCFA</div>
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
            <!-- Demandes récentes -->
            <div class="table-card">
                <div class="table-header">
                    <span class="table-title">Demandes récentes</span>
                    <a href="<?= SITE_URL ?>/admin/demandes.php" class="btn btn-gold btn-sm">Voir tout</a>
                </div>
                <?php if (empty($demandes)): ?>
                <div style="padding:32px;text-align:center;color:var(--text-muted)">
                    <i class="fas fa-inbox fa-2x" style="margin-bottom:10px;opacity:0.4"></i>
                    <p>Aucune demande</p>
                </div>
                <?php else: ?>
                <table class="data-table">
                    <thead><tr>
                        <th>Locataire</th>
                        <th>Maison</th>
                        <th>Statut</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($demandes as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['loc_prenom'] . ' ' . $d['loc_nom']) ?></td>
                            <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($d['maison_titre']) ?></td>
                            <td><span class="status-badge status-<?= $d['statut'] ?>"><?= ucfirst(str_replace('_', ' ', $d['statut'])) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Nouveaux utilisateurs -->
            <div class="table-card">
                <div class="table-header">
                    <span class="table-title">Nouveaux membres</span>
                    <a href="<?= SITE_URL ?>/admin/utilisateurs.php" class="btn btn-gold btn-sm">Voir tout</a>
                </div>
                <?php if (empty($derniers_users)): ?>
                <div style="padding:32px;text-align:center;color:var(--text-muted)">Aucun membre</div>
                <?php else: ?>
                <div style="padding:16px">
                    <?php foreach ($derniers_users as $u): ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--cream-2)">
                        <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--gold-light));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:14px;flex-shrink:0">
                            <?= strtoupper(substr($u['prenom'],0,1)) ?>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></div>
                            <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($u['email']) ?></div>
                        </div>
                        <span class="status-badge status-<?= $u['statut'] ?>" style="font-size:10px">
                            <?= $u['role'] ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dernières annonces -->
        <div class="table-card">
            <div class="table-header">
                <span class="table-title">Dernières annonces publiées</span>
                <a href="<?= SITE_URL ?>/admin/maisons.php" class="btn btn-gold btn-sm">Gérer toutes</a>
            </div>
            <table class="data-table">
                <thead><tr>
                    <th>Titre</th>
                    <th>Agent</th>
                    <th>Ville</th>
                    <th>Prix/mois</th>
                    <th>Statut</th>
                    <th>Dispo.</th>
                    <th>Actions</th>
                </tr></thead>
                <tbody>
                    <?php if (empty($dernieres_maisons)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">Aucune annonce</td></tr>
                    <?php else: ?>
                    <?php foreach ($dernieres_maisons as $ma): ?>
                    <tr>
                        <td style="max-width:180px">
                            <a href="<?= SITE_URL ?>/detail.php?id=<?= $ma['id'] ?>" style="color:var(--dark);font-weight:600" target="_blank">
                                <?= htmlspecialchars($ma['titre']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($ma['agent_prenom'] . ' ' . $ma['agent_nom']) ?></td>
                        <td><?= htmlspecialchars($ma['ville_nom']) ?></td>
                        <td style="font-weight:600;color:var(--gold)"><?= formatPrix($ma['prix_mensuel']) ?></td>
                        <td><span class="status-badge status-<?= $ma['statut'] ?>"><?= $ma['statut'] ?></span></td>
                        <td><span class="status-badge status-<?= $ma['disponibilite'] ?>"><?= $ma['disponibilite'] ?></span></td>
                        <td>
                            <div style="display:flex;gap:6px">
                                <a href="<?= SITE_URL ?>/admin/maisons.php?action=modifier&id=<?= $ma['id'] ?>" 
                                   style="padding:4px 10px;border-radius:6px;background:#eff6ff;color:var(--blue);font-size:12px">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="javascript:confirmDelete('<?= SITE_URL ?>/admin/maisons.php?action=supprimer&id=<?= $ma['id'] ?>','Supprimer cette annonce ?')" 
                                   style="padding:4px 10px;border-radius:6px;background:#fef2f2;color:var(--red);font-size:12px">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>const SITE_URL = '<?= SITE_URL ?>';</script>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</div>
