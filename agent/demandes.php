<?php
require_once '../includes/config.php';
requireAgent();
$db = getDB();
$uid = $_SESSION['user_id'];

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($action && $id && in_array($action, ['accepter','refuser'])) {
    $statut = $action === 'accepter' ? 'acceptee' : 'refusee';
    $db->prepare("UPDATE demandes_location SET statut=?, date_reponse=NOW() WHERE id=? AND agent_id=?")->execute([$statut, $id, $uid]);
    
    if ($action === 'accepter') {
        $d = $db->query("SELECT * FROM demandes_location WHERE id=$id")->fetch();
        $m = $db->query("SELECT prix_mensuel, caution FROM maisons WHERE id=".$d['maison_id'])->fetch();
        $debut = $d['date_debut'] ?: date('Y-m-d');
        $fin = date('Y-m-d', strtotime("+{$d['duree_mois']} months", strtotime($debut)));
        $num = 'PLI-' . date('Ym') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
        $db->prepare("INSERT INTO contrats (demande_id,maison_id,locataire_id,agent_id,date_debut,date_fin,loyer_mensuel,caution_versee,numero_contrat) VALUES (?,?,?,?,?,?,?,?,?)")
           ->execute([$id,$d['maison_id'],$d['locataire_id'],$uid,$debut,$fin,$m['prix_mensuel'],$m['caution'],$num]);
        $db->prepare("UPDATE maisons SET disponibilite='louee' WHERE id=?")->execute([$d['maison_id']]);
        $db->prepare("INSERT INTO notifications (user_id,titre,message,type) VALUES (?,?,?,'succes')")
           ->execute([$d['locataire_id'],'Demande acceptée !','Contrat N° '.$num.' créé. Bienvenue !']);
        flashMessage('succes', 'Demande acceptée ! Contrat '.$num.' créé.');
    } else {
        $d = $db->query("SELECT locataire_id FROM demandes_location WHERE id=$id")->fetch();
        $db->prepare("INSERT INTO notifications (user_id,titre,message,type) VALUES (?,?,?,'alerte')")
           ->execute([$d['locataire_id'],'Demande refusée','Votre demande de location a été refusée par l\'agent.']);
        flashMessage('info', 'Demande refusée.');
    }
    redirect(SITE_URL . '/agent/demandes.php');
}

$filtre = sanitize($_GET['statut'] ?? '');
$maison_f = (int)($_GET['maison'] ?? 0);
$w = ["d.agent_id = $uid"];
if ($filtre) $w[] = "d.statut = '$filtre'";
if ($maison_f) $w[] = "d.maison_id = $maison_f";
$where = 'WHERE ' . implode(' AND ', $w);

$demandes = $db->query("
    SELECT d.*, m.titre as maison_titre, m.prix_mensuel,
           u.prenom as loc_prenom, u.nom as loc_nom, u.email as loc_email, u.telephone as loc_tel
    FROM demandes_location d JOIN maisons m ON d.maison_id=m.id JOIN users u ON d.locataire_id=u.id
    $where ORDER BY d.date_demande DESC
")->fetchAll();

$page_title = 'Demandes de Location';
include 'includes/sidebar_agent.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar"><div class="topbar-title">Demandes reçues (<?= count($demandes) ?>)</div></div>
    <div class="dashboard-content">
        <?php $flash = getFlash(); if ($flash): ?>
        <div style="background:#f0fdf4;border-left:4px solid var(--green);padding:14px;border-radius:10px;margin-bottom:20px;color:#166534;display:flex;gap:10px">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap">
            <?php foreach ([''=>'Toutes','en_attente'=>'En attente','acceptee'=>'Acceptées','refusee'=>'Refusées'] as $k => $v): ?>
            <a href="?statut=<?= $k ?>" class="btn btn-sm" style="<?= $filtre===$k ? 'background:var(--gold);color:white' : 'background:var(--white);color:var(--text);border:1.5px solid #E5E7EB' ?>"><?= $v ?></a>
            <?php endforeach; ?>
        </div>

        <div style="display:flex;flex-direction:column;gap:16px">
            <?php if (empty($demandes)): ?>
            <div style="text-align:center;padding:60px;background:var(--white);border-radius:16px;color:var(--text-muted)">
                <i class="fas fa-inbox fa-3x" style="opacity:0.2;margin-bottom:16px;display:block"></i>
                <p>Aucune demande trouvée.</p>
            </div>
            <?php else: ?>
            <?php foreach ($demandes as $d): ?>
            <div style="background:var(--white);border-radius:16px;padding:24px;border:1px solid #F1F5F9;box-shadow:var(--shadow-sm)">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px">
                    <div>
                        <div style="font-family:'Playfair Display',serif;font-size:18px;font-weight:700;margin-bottom:6px">
                            <?= htmlspecialchars($d['loc_prenom'] . ' ' . $d['loc_nom']) ?>
                        </div>
                        <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:13px;color:var(--text-light)">
                            <span><i class="fas fa-phone" style="color:var(--gold)"></i> <?= htmlspecialchars($d['loc_tel']) ?></span>
                            <span><i class="fas fa-envelope" style="color:var(--gold)"></i> <?= htmlspecialchars($d['loc_email']) ?></span>
                            <span><i class="fas fa-home" style="color:var(--gold)"></i> <?= htmlspecialchars($d['maison_titre']) ?></span>
                        </div>
                        <div style="display:flex;gap:16px;margin-top:8px;font-size:13px;color:var(--text-light)">
                            <span><i class="fas fa-calendar" style="color:var(--gold)"></i> Début : <?= $d['date_debut'] ? date('d/m/Y', strtotime($d['date_debut'])) : 'Non précisé' ?></span>
                            <span><i class="fas fa-clock" style="color:var(--gold)"></i> Durée : <?= $d['duree_mois'] ?> mois</span>
                            <span><i class="fas fa-coins" style="color:var(--gold)"></i> <?= formatPrix($d['prix_mensuel']) ?>/mois</span>
                        </div>
                        <?php if ($d['message']): ?>
                        <div style="margin-top:12px;padding:12px;background:var(--cream);border-radius:10px;font-size:14px;font-style:italic;color:var(--text-light)">
                            "<?= htmlspecialchars($d['message']) ?>"
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:10px">
                        <span class="status-badge status-<?= $d['statut'] ?>"><?= str_replace('_',' ',ucfirst($d['statut'])) ?></span>
                        <div style="font-size:12px;color:var(--text-muted)"><?= timeAgo($d['date_demande']) ?></div>
                        <?php if ($d['statut'] === 'en_attente'): ?>
                        <div style="display:flex;gap:8px">
                            <button onclick="openAgentActionModal(<?= $d['id'] ?>, 'accepter', '<?= htmlspecialchars($d['loc_prenom'].' '.$d['loc_nom'], ENT_QUOTES) ?>', '<?= htmlspecialchars($d['maison_titre'], ENT_QUOTES) ?>')"
                                class="btn btn-sm" style="background:#f0fdf4;color:var(--green);border:none;cursor:pointer">
                                <i class="fas fa-check"></i> Accepter
                            </button>
                            <button onclick="openAgentActionModal(<?= $d['id'] ?>, 'refuser', '<?= htmlspecialchars($d['loc_prenom'].' '.$d['loc_nom'], ENT_QUOTES) ?>', '<?= htmlspecialchars($d['maison_titre'], ENT_QUOTES) ?>')"
                                class="btn btn-sm" style="background:#fef2f2;color:var(--red);border:none;cursor:pointer">
                                <i class="fas fa-times"></i> Refuser
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL ACTION DEMANDE AGENT -->
<div id="modal-agent-action" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)" onclick="document.getElementById('modal-agent-action').style.display='none'"></div>
    <div style="position:relative;background:white;border-radius:20px;padding:36px;max-width:460px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn .2s ease">
        <button onclick="document.getElementById('modal-agent-action').style.display='none'" style="position:absolute;top:16px;right:16px;background:#f1f5f9;border:none;border-radius:50%;width:32px;height:32px;cursor:pointer;font-size:16px;color:var(--text-muted)">✕</button>
        <div style="text-align:center;margin-bottom:24px">
            <div id="aa-icon" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px"></div>
            <h3 id="aa-title" style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:10px"></h3>
            <p id="aa-desc" style="color:var(--text-muted);font-size:14px;line-height:1.6"></p>
        </div>
        <div style="display:flex;gap:12px">
            <button onclick="document.getElementById('modal-agent-action').style.display='none'" style="flex:1;padding:13px;border:1.5px solid #E5E7EB;border-radius:12px;background:white;cursor:pointer;font-size:14px;font-weight:600">Annuler</button>
            <a id="aa-btn" href="#" style="flex:1;padding:13px;border-radius:12px;color:white;text-align:center;text-decoration:none;font-size:14px;font-weight:600"></a>
        </div>
    </div>
</div>

<style>
@keyframes modalIn { from{opacity:0;transform:scale(.95) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
</style>
<script>
const SITE_URL = '<?= SITE_URL ?>';
function openAgentActionModal(id, action, locataire, maison) {
    const isAccepter = action === 'accepter';
    document.getElementById('aa-icon').style.background = isAccepter ? '#f0fdf4' : '#fef2f2';
    document.getElementById('aa-icon').innerHTML = isAccepter
        ? '<i class="fas fa-check-circle" style="color:var(--green);font-size:26px"></i>'
        : '<i class="fas fa-times-circle" style="color:var(--red);font-size:26px"></i>';
    document.getElementById('aa-title').textContent = isAccepter ? 'Accepter la demande' : 'Refuser la demande';
    document.getElementById('aa-desc').innerHTML = isAccepter
        ? `Accepter la demande de <strong>${locataire}</strong> pour <strong>${maison}</strong> ?<br><br><span style="color:var(--green);font-size:13px"><i class="fas fa-info-circle"></i> Un contrat sera automatiquement généré.</span>`
        : `Refuser la demande de <strong>${locataire}</strong> pour <strong>${maison}</strong> ?<br><br><span style="color:var(--text-muted);font-size:13px">Le locataire sera notifié du refus.</span>`;
    const btn = document.getElementById('aa-btn');
    btn.style.background = isAccepter ? 'var(--green)' : 'var(--red)';
    btn.textContent = isAccepter ? 'Accepter et créer le contrat' : 'Refuser la demande';
    btn.href = SITE_URL + '/agent/demandes.php?action=' + action + '&id=' + id;
    document.getElementById('modal-agent-action').style.display = 'flex';
}
</script>
</div>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
