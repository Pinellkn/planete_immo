<?php
require_once '../includes/config.php';
requireAgent();
$db = getDB();
$uid = $_SESSION['user_id'];

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($action === 'supprimer' && $id) {
    $db->prepare("DELETE FROM maisons WHERE id=? AND agent_id=?")->execute([$id, $uid]);
    flashMessage('succes', 'Annonce supprimée.');
    redirect(SITE_URL . '/agent/mes_maisons.php');
}
if ($action === 'toggle' && $id) {
    $m = $db->query("SELECT statut FROM maisons WHERE id=$id AND agent_id=$uid")->fetch();
    if ($m) {
        $ns = $m['statut'] === 'actif' ? 'inactif' : 'actif';
        $db->prepare("UPDATE maisons SET statut=? WHERE id=?")->execute([$ns, $id]);
    }
    redirect(SITE_URL . '/agent/mes_maisons.php');
}

$maisons = $db->prepare("
    SELECT m.*, v.nom as ville_nom, t.libelle as type_libelle,
           (SELECT COUNT(*) FROM demandes_location WHERE maison_id=m.id AND statut='en_attente') as nb_dem
    FROM maisons m JOIN villes v ON m.ville_id=v.id JOIN types_maison t ON m.type_id=t.id
    WHERE m.agent_id=? ORDER BY m.date_creation DESC
");
$maisons->execute([$uid]);
$maisons = $maisons->fetchAll();

$page_title = 'Mes Annonces';
include 'includes/sidebar_agent.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar">
        <div class="topbar-title">Mes Annonces (<?= count($maisons) ?>)</div>
        <div class="topbar-actions">
            <a href="<?= SITE_URL ?>/agent/ajouter_maison.php" class="btn btn-gold btn-sm"><i class="fas fa-plus"></i> Nouvelle annonce</a>
        </div>
    </div>
    <div class="dashboard-content">
        <?php $flash = getFlash(); if ($flash): ?>
        <div style="background:#f0fdf4;border-left:4px solid var(--green);padding:14px 16px;border-radius:10px;margin-bottom:20px;color:#166534;display:flex;gap:10px">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <div class="table-card">
            <table class="data-table">
                <thead><tr>
                    <th>Annonce</th><th>Prix/mois</th><th>Vues</th><th>Demandes</th><th>Statut</th><th>Dispo.</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    <?php if (empty($maisons)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:60px;color:var(--text-muted)">
                        <i class="fas fa-home fa-3x" style="opacity:0.2;margin-bottom:16px;display:block"></i>
                        <p>Vous n'avez pas encore d'annonces.</p>
                        <a href="<?= SITE_URL ?>/agent/ajouter_maison.php" class="btn btn-gold btn-sm" style="margin-top:12px">Ajouter ma première annonce</a>
                    </td></tr>
                    <?php else: ?>
                    <?php foreach ($maisons as $ma): ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($ma['titre']) ?></div>
                            <div style="font-size:12px;color:var(--text-muted)"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($ma['quartier'] ?? '') ?>, <?= htmlspecialchars($ma['ville_nom']) ?></div>
                            <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($ma['type_libelle']) ?> • <?= $ma['nb_chambres'] ?> ch.</div>
                        </td>
                        <td style="font-weight:700;color:var(--gold)"><?= formatPrix($ma['prix_mensuel']) ?></td>
                        <td><?= $ma['vues'] ?> <span style="font-size:11px;color:var(--text-muted)">vues</span></td>
                        <td>
                            <?php if ($ma['nb_dem'] > 0): ?>
                            <a href="<?= SITE_URL ?>/agent/demandes.php?maison=<?= $ma['id'] ?>" style="background:#fffbeb;color:var(--orange);padding:4px 10px;border-radius:50px;font-size:12px;font-weight:600">
                                <?= $ma['nb_dem'] ?> en attente
                            </a>
                            <?php else: ?>
                            <span style="color:var(--text-muted);font-size:12px">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick="openToggleAgentModal(<?= $ma['id'] ?>, '<?= htmlspecialchars($ma['titre'], ENT_QUOTES) ?>', '<?= $ma['statut'] ?>')" style="background:none;border:none;cursor:pointer;padding:0">
                                <span class="status-badge status-<?= $ma['statut'] ?>"><?= $ma['statut'] ?></span>
                            </button>
                        </td>
                        <td><span class="status-badge status-<?= $ma['disponibilite'] ?>"><?= $ma['disponibilite'] ?></span></td>
                        <td>
                            <div style="display:flex;gap:4px">
                                <a href="<?= SITE_URL ?>/detail.php?id=<?= $ma['id'] ?>" target="_blank" style="padding:5px 9px;border-radius:7px;background:#f0fdf4;color:var(--green);font-size:12px" title="Voir"><i class="fas fa-eye"></i></a>
                                <a href="<?= SITE_URL ?>/agent/ajouter_maison.php?id=<?= $ma['id'] ?>" style="padding:5px 9px;border-radius:7px;background:#eff6ff;color:var(--blue);font-size:12px" title="Modifier"><i class="fas fa-edit"></i></a>
                                <button onclick="openDeleteAgentModal(<?= $ma['id'] ?>, '<?= htmlspecialchars($ma['titre'], ENT_QUOTES) ?>')" style="padding:5px 9px;border-radius:7px;background:#fef2f2;color:var(--red);font-size:12px;border:none;cursor:pointer" title="Supprimer"><i class="fas fa-trash"></i></button>
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

<!-- MODAL SUPPRESSION -->
<div id="modal-delete-agent" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)" onclick="document.getElementById('modal-delete-agent').style.display='none'"></div>
    <div style="position:relative;background:white;border-radius:20px;padding:36px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn .2s ease">
        <div style="text-align:center;margin-bottom:24px">
            <div style="width:64px;height:64px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                <i class="fas fa-trash" style="font-size:24px;color:var(--red)"></i>
            </div>
            <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:8px">Supprimer l'annonce</h3>
            <p style="color:var(--text-muted);font-size:14px">Supprimer <strong id="da-name"></strong> ? Cette action est irréversible.</p>
        </div>
        <div style="display:flex;gap:12px">
            <button onclick="document.getElementById('modal-delete-agent').style.display='none'" style="flex:1;padding:13px;border:1.5px solid #E5E7EB;border-radius:12px;background:white;cursor:pointer;font-size:14px;font-weight:600">Annuler</button>
            <a id="da-link" href="#" style="flex:1;padding:13px;border-radius:12px;background:var(--red);color:white;text-align:center;text-decoration:none;font-size:14px;font-weight:600">Supprimer</a>
        </div>
    </div>
</div>

<!-- MODAL TOGGLE STATUT -->
<div id="modal-toggle-agent" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)" onclick="document.getElementById('modal-toggle-agent').style.display='none'"></div>
    <div style="position:relative;background:white;border-radius:20px;padding:36px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn .2s ease">
        <div style="text-align:center;margin-bottom:24px">
            <div style="width:64px;height:64px;border-radius:50%;background:#fffbeb;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                <i class="fas fa-toggle-on" style="font-size:24px;color:var(--gold)"></i>
            </div>
            <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:8px">Modifier la visibilité</h3>
            <p style="color:var(--text-muted);font-size:14px" id="ta-desc"></p>
        </div>
        <div style="display:flex;gap:12px">
            <button onclick="document.getElementById('modal-toggle-agent').style.display='none'" style="flex:1;padding:13px;border:1.5px solid #E5E7EB;border-radius:12px;background:white;cursor:pointer;font-size:14px;font-weight:600">Annuler</button>
            <a id="ta-link" href="#" style="flex:1;padding:13px;border-radius:12px;background:var(--gold);color:white;text-align:center;text-decoration:none;font-size:14px;font-weight:600">Confirmer</a>
        </div>
    </div>
</div>

<style>
@keyframes modalIn { from{opacity:0;transform:scale(.95) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
</style>
<script>
const SITE_URL = '<?= SITE_URL ?>';
function openDeleteAgentModal(id, titre) {
    document.getElementById('da-name').textContent = titre;
    document.getElementById('da-link').href = SITE_URL + '/agent/mes_maisons.php?action=supprimer&id=' + id;
    document.getElementById('modal-delete-agent').style.display = 'flex';
}
function openToggleAgentModal(id, titre, statut) {
    const action = statut === 'actif' ? 'désactiver' : 'activer';
    document.getElementById('ta-desc').innerHTML = 'Vous allez <strong>' + action + '</strong> l\'annonce <strong>' + titre + '</strong>.';
    document.getElementById('ta-link').textContent = action.charAt(0).toUpperCase() + action.slice(1);
    document.getElementById('ta-link').href = SITE_URL + '/agent/mes_maisons.php?action=toggle&id=' + id;
    document.getElementById('modal-toggle-agent').style.display = 'flex';
}
</script>
</div>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
