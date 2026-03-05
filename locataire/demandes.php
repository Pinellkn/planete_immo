<?php
require_once '../includes/config.php';
requireLogin();
$db = getDB();
$uid = $_SESSION['user_id'];

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
if ($action === 'annuler' && $id) {
    $db->prepare("UPDATE demandes_location SET statut='annulee' WHERE id=? AND locataire_id=? AND statut='en_attente'")->execute([$id, $uid]);
    flashMessage('info', 'Demande annulée.');
    redirect(SITE_URL . '/locataire/demandes.php');
}

$demandes = $db->prepare("
    SELECT d.*, m.titre as maison_titre, m.prix_mensuel, m.photo_principale, v.nom as ville_nom, m.quartier,
           u.prenom as agent_prenom, u.nom as agent_nom, u.telephone as agent_tel
    FROM demandes_location d JOIN maisons m ON d.maison_id=m.id JOIN villes v ON m.ville_id=v.id
    JOIN users u ON d.agent_id=u.id
    WHERE d.locataire_id=? ORDER BY d.date_demande DESC
");
$demandes->execute([$uid]);
$demandes = $demandes->fetchAll();

$page_title = 'Mes Demandes';
include 'includes/sidebar_locataire.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar">
        <div class="topbar-title">Mes Demandes de Location (<?= count($demandes) ?>)</div>
        <div class="topbar-actions">
            <a href="<?= SITE_URL ?>/maisons.php" class="btn btn-gold btn-sm"><i class="fas fa-plus"></i> Nouvelle demande</a>
        </div>
    </div>
    <div class="dashboard-content">
        <?php $flash = getFlash(); if ($flash): ?>
        <div style="background:#eff6ff;border-left:4px solid var(--blue);padding:14px;border-radius:10px;margin-bottom:20px;color:#1d4ed8;display:flex;gap:10px">
            <i class="fas fa-info-circle"></i> <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <?php if (empty($demandes)): ?>
        <div style="text-align:center;padding:80px;background:var(--white);border-radius:20px">
            <i class="fas fa-paper-plane fa-4x" style="color:var(--gold);opacity:0.3;margin-bottom:20px;display:block"></i>
            <h3 style="margin-bottom:10px">Aucune demande</h3>
            <p style="color:var(--text-muted);margin-bottom:20px">Vous n'avez pas encore envoyé de demande de location.</p>
            <a href="<?= SITE_URL ?>/maisons.php" class="btn btn-gold"><i class="fas fa-search"></i> Trouver une maison</a>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:16px">
            <?php foreach ($demandes as $d): ?>
            <div style="background:var(--white);border-radius:16px;padding:24px;border:1px solid #F1F5F9;box-shadow:var(--shadow-sm);display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap">
                <div style="width:100px;height:80px;border-radius:12px;overflow:hidden;background:var(--dark-3);flex-shrink:0">
                    <?php if ($d['photo_principale']): ?>
                    <img src="<?= SITE_URL ?>/uploads/maisons/<?= htmlspecialchars($d['photo_principale']) ?>" alt="" style="width:100%;height:100%;object-fit:cover">
                    <?php else: ?>
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center"><i class="fas fa-home" style="color:rgba(255,255,255,0.2);font-size:28px"></i></div>
                    <?php endif; ?>
                </div>
                <div style="flex:1;min-width:200px">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">
                        <h3 style="font-family:'Playfair Display',serif;font-size:17px">
                            <a href="<?= SITE_URL ?>/detail.php?id=<?= $d['maison_id'] ?>" style="color:var(--dark)"><?= htmlspecialchars($d['maison_titre']) ?></a>
                        </h3>
                        <span class="status-badge status-<?= $d['statut'] ?>"><?= str_replace('_',' ',ucfirst($d['statut'])) ?></span>
                    </div>
                    <div style="font-size:13px;color:var(--text-light);margin-bottom:8px">
                        <i class="fas fa-map-marker-alt" style="color:var(--gold)"></i> <?= htmlspecialchars($d['quartier'] ?? '') ?>, <?= htmlspecialchars($d['ville_nom']) ?>
                    </div>
                    <div style="display:flex;gap:20px;font-size:13px;color:var(--text-muted);flex-wrap:wrap">
                        <span><i class="fas fa-coins" style="color:var(--gold)"></i> <?= formatPrix($d['prix_mensuel']) ?>/mois</span>
                        <span><i class="fas fa-calendar" style="color:var(--gold)"></i> Début : <?= $d['date_debut'] ? date('d/m/Y',strtotime($d['date_debut'])) : 'Non précisé' ?></span>
                        <span><i class="fas fa-clock" style="color:var(--gold)"></i> <?= $d['duree_mois'] ?> mois</span>
                        <span><i class="fas fa-user-tie" style="color:var(--gold)"></i> <?= htmlspecialchars($d['agent_prenom'] . ' ' . $d['agent_nom']) ?></span>
                    </div>
                    <?php if ($d['statut'] === 'acceptee'): ?>
                    <div style="margin-top:10px;background:#f0fdf4;border-radius:10px;padding:10px 14px;font-size:13px;color:#166534">
                        <i class="fas fa-check-circle"></i> Votre demande a été acceptée ! L'agent <?= htmlspecialchars($d['agent_prenom']) ?> vous contactera au <?= htmlspecialchars($d['agent_tel']) ?>.
                    </div>
                    <?php elseif ($d['statut'] === 'refusee'): ?>
                    <div style="margin-top:10px;background:#fef2f2;border-radius:10px;padding:10px 14px;font-size:13px;color:#991b1b">
                        <i class="fas fa-times-circle"></i> Cette demande a été refusée. Vous pouvez en faire une autre pour ce bien ou un autre.
                    </div>
                    <?php endif; ?>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
                    <div style="font-size:11px;color:var(--text-muted)"><?= timeAgo($d['date_demande']) ?></div>
                    <?php if ($d['statut'] === 'en_attente'): ?>
                    <button onclick="openAnnulerModal(<?= $d['id'] ?>, '<?= htmlspecialchars($d['maison_titre'], ENT_QUOTES) ?>')"
                        class="btn btn-sm" style="background:#fef2f2;color:var(--red);border:none;cursor:pointer">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <?php endif; ?>
                    <a href="<?= SITE_URL ?>/detail.php?id=<?= $d['maison_id'] ?>" class="btn btn-sm" style="background:var(--cream);color:var(--text)">
                        <i class="fas fa-eye"></i> Voir bien
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL ANNULATION DEMANDE -->
<div id="modal-annuler" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)" onclick="document.getElementById('modal-annuler').style.display='none'"></div>
    <div style="position:relative;background:white;border-radius:20px;padding:36px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn .2s ease">
        <div style="text-align:center;margin-bottom:24px">
            <div style="width:64px;height:64px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                <i class="fas fa-exclamation-triangle" style="font-size:24px;color:#d97706"></i>
            </div>
            <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:8px">Annuler la demande ?</h3>
            <p style="color:var(--text-muted);font-size:14px">Vous allez annuler votre demande pour <strong id="an-name"></strong>. Vous pourrez en refaire une ultérieurement.</p>
        </div>
        <div style="display:flex;gap:12px">
            <button onclick="document.getElementById('modal-annuler').style.display='none'" style="flex:1;padding:13px;border:1.5px solid #E5E7EB;border-radius:12px;background:white;cursor:pointer;font-size:14px;font-weight:600">Garder la demande</button>
            <a id="an-link" href="#" style="flex:1;padding:13px;border-radius:12px;background:#d97706;color:white;text-align:center;text-decoration:none;font-size:14px;font-weight:600">Oui, annuler</a>
        </div>
    </div>
</div>

<style>
@keyframes modalIn { from{opacity:0;transform:scale(.95) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
</style>
<script>
const SITE_URL = '<?= SITE_URL ?>';
function openAnnulerModal(id, maison) {
    document.getElementById('an-name').textContent = maison;
    document.getElementById('an-link').href = SITE_URL + '/locataire/demandes.php?action=annuler&id=' + id;
    document.getElementById('modal-annuler').style.display = 'flex';
}
</script>
</div>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
