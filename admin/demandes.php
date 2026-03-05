<?php
require_once '../includes/config.php';
requireAdmin();
$db = getDB();

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($action && $id && in_array($action, ['accepter','refuser','annuler'])) {
    $statut_map = ['accepter'=>'acceptee','refuser'=>'refusee','annuler'=>'annulee'];
    $db->prepare("UPDATE demandes_location SET statut=?, date_reponse=NOW() WHERE id=?")->execute([$statut_map[$action], $id]);
    if ($action === 'accepter') {
        $d = $db->query("SELECT * FROM demandes_location WHERE id=$id")->fetch();
        $m = $db->query("SELECT prix_mensuel, caution FROM maisons WHERE id=".$d['maison_id'])->fetch();
        $debut = $d['date_debut'] ?: date('Y-m-d');
        $fin = date('Y-m-d', strtotime("+{$d['duree_mois']} months", strtotime($debut)));
        $num = 'PLI-' . date('Ym') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
        $db->prepare("INSERT INTO contrats (demande_id,maison_id,locataire_id,agent_id,date_debut,date_fin,loyer_mensuel,caution_versee,numero_contrat) VALUES (?,?,?,?,?,?,?,?,?)")
           ->execute([$id,$d['maison_id'],$d['locataire_id'],$d['agent_id'],$debut,$fin,$m['prix_mensuel'],$m['caution'],$num]);
        $db->prepare("UPDATE maisons SET disponibilite='louee' WHERE id=?")->execute([$d['maison_id']]);
        $db->prepare("INSERT INTO notifications (user_id,titre,message,type) VALUES (?,?,?,'succes')")
           ->execute([$d['locataire_id'],'Demande acceptée !','Contrat N° '.$num.' créé.']);
        flashMessage('succes', 'Acceptée ! Contrat '.$num.' créé.');
    } else {
        flashMessage('info', 'Statut mis à jour.');
    }
    redirect(SITE_URL . '/admin/demandes.php');
}

$filtre = sanitize($_GET['statut'] ?? '');
$w = $filtre ? "WHERE d.statut = '$filtre'" : '';
$demandes = $db->query("
    SELECT d.*, m.titre as maison_titre, m.prix_mensuel,
           u1.prenom as loc_prenom, u1.nom as loc_nom, u1.telephone as loc_tel,
           u2.prenom as agent_prenom, u2.nom as agent_nom
    FROM demandes_location d JOIN maisons m ON d.maison_id=m.id
    JOIN users u1 ON d.locataire_id=u1.id JOIN users u2 ON d.agent_id=u2.id
    $w ORDER BY d.date_demande DESC
")->fetchAll();

$page_title = 'Gestion des Demandes';
include 'includes/sidebar_admin.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar"><div class="topbar-title">Demandes de Location (<?= count($demandes) ?>)</div></div>
    <div class="dashboard-content">
        <?php $flash = getFlash(); if ($flash): ?>
        <div style="background:#f0fdf4;border-left:4px solid var(--green);padding:14px;border-radius:10px;margin-bottom:20px;color:#166534;display:flex;gap:10px">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap">
            <?php foreach ([''=>'Toutes','en_attente'=>'En attente','acceptee'=>'Acceptées','refusee'=>'Refusées','annulee'=>'Annulées'] as $k => $v): ?>
            <a href="?statut=<?= $k ?>" class="btn btn-sm" style="<?= $filtre===$k ? 'background:var(--gold);color:white' : 'background:var(--white);color:var(--text);border:1.5px solid #E5E7EB' ?>"><?= $v ?></a>
            <?php endforeach; ?>
        </div>

        <div class="table-card">
            <table class="data-table">
                <thead><tr><th>Locataire</th><th>Maison</th><th>Agent</th><th>Prix/mois</th><th>Date début</th><th>Durée</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (empty($demandes)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">Aucune demande</td></tr>
                    <?php else: ?>
                    <?php foreach ($demandes as $d): ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($d['loc_prenom'].' '.$d['loc_nom']) ?></div>
                            <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($d['loc_tel']) ?></div>
                        </td>
                        <td style="max-width:140px;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($d['maison_titre']) ?></td>
                        <td style="font-size:13px"><?= htmlspecialchars($d['agent_prenom'].' '.$d['agent_nom']) ?></td>
                        <td style="font-weight:700;color:var(--gold)"><?= formatPrix($d['prix_mensuel']) ?></td>
                        <td style="font-size:13px"><?= $d['date_debut'] ? date('d/m/Y',strtotime($d['date_debut'])) : '-' ?></td>
                        <td style="font-size:13px"><?= $d['duree_mois'] ?> mois</td>
                        <td><span class="status-badge status-<?= $d['statut'] ?>"><?= str_replace('_',' ',ucfirst($d['statut'])) ?></span></td>
                        <td>
                            <?php if ($d['statut'] === 'en_attente'): ?>
                            <div style="display:flex;gap:4px">
                                <button onclick="openActionModal(<?= $d['id'] ?>, 'accepter', '<?= htmlspecialchars($d['loc_prenom'].' '.$d['loc_nom'], ENT_QUOTES) ?>', '<?= htmlspecialchars($d['maison_titre'], ENT_QUOTES) ?>')"
                                    style="padding:5px 10px;border-radius:7px;background:#f0fdf4;color:var(--green);font-size:12px;border:none;cursor:pointer;font-weight:600">
                                    <i class="fas fa-check"></i> Accepter
                                </button>
                                <button onclick="openActionModal(<?= $d['id'] ?>, 'refuser', '<?= htmlspecialchars($d['loc_prenom'].' '.$d['loc_nom'], ENT_QUOTES) ?>', '<?= htmlspecialchars($d['maison_titre'], ENT_QUOTES) ?>')"
                                    style="padding:5px 10px;border-radius:7px;background:#fef2f2;color:var(--red);font-size:12px;border:none;cursor:pointer;font-weight:600">
                                    <i class="fas fa-times"></i> Refuser
                                </button>
                            </div>
                            <?php else: ?>
                            <span style="font-size:12px;color:var(--text-muted)">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL ACTION DEMANDE -->
<div id="modal-action" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)" onclick="closeModal()"></div>
    <div style="position:relative;background:white;border-radius:20px;padding:36px;max-width:460px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn .2s ease">
        <button onclick="closeModal()" style="position:absolute;top:16px;right:16px;background:#f1f5f9;border:none;border-radius:50%;width:32px;height:32px;cursor:pointer;font-size:16px;color:var(--text-muted)">✕</button>
        <div style="text-align:center;margin-bottom:24px">
            <div id="modal-icon" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px"></div>
            <h3 id="modal-title" style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:10px"></h3>
            <p id="modal-desc" style="color:var(--text-muted);font-size:14px;line-height:1.6"></p>
        </div>
        <div style="display:flex;gap:12px">
            <button onclick="closeModal()" style="flex:1;padding:13px;border:1.5px solid #E5E7EB;border-radius:12px;background:white;cursor:pointer;font-size:14px;font-weight:600">Annuler</button>
            <a id="modal-confirm-btn" href="#" id="action-link" style="flex:1;padding:13px;border-radius:12px;color:white;text-align:center;text-decoration:none;font-size:14px;font-weight:600"></a>
        </div>
    </div>
</div>

<style>
@keyframes modalIn { from{opacity:0;transform:scale(.95) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
</style>
<script>
const SITE_URL = '<?= SITE_URL ?>';
function openActionModal(id, action, locataire, maison) {
    const config = {
        accepter: { title:'Accepter la demande', icon:'✅', iconBg:'#f0fdf4', btnColor:'var(--green)', btnText:'Accepter et créer le contrat',
            desc:`Vous allez accepter la demande de <strong>${locataire}</strong> pour <strong>${maison}</strong>.<br><br><span style="color:var(--green);font-weight:600"><i class="fas fa-info-circle"></i> Un contrat sera automatiquement créé.</span>` },
        refuser: { title:'Refuser la demande', icon:'❌', iconBg:'#fef2f2', btnColor:'var(--red)', btnText:'Refuser la demande',
            desc:`Vous allez refuser la demande de <strong>${locataire}</strong> pour <strong>${maison}</strong>.<br><br>Le locataire sera notifié du refus.` },
    };
    const c = config[action];
    document.getElementById('modal-icon').style.background = c.iconBg;
    document.getElementById('modal-icon').textContent = c.icon;
    document.getElementById('modal-title').textContent = c.title;
    document.getElementById('modal-desc').innerHTML = c.desc;
    const btn = document.getElementById('modal-confirm-btn');
    btn.style.background = c.btnColor;
    btn.textContent = c.btnText;
    btn.href = SITE_URL + '/admin/demandes.php?action=' + action + '&id=' + id;
    document.getElementById('modal-action').style.display = 'flex';
}
function closeModal() {
    document.getElementById('modal-action').style.display = 'none';
}
</script>
</div>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
