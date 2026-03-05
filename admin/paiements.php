<?php
require_once '../includes/config.php';
requireAdmin();
$db = getDB();

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
if ($action === 'valider' && $id) {
    $db->prepare("UPDATE paiements SET statut='valide', date_validation=NOW() WHERE id=?")->execute([$id]);
    flashMessage('succes', 'Paiement validé.');
    redirect(SITE_URL . '/admin/paiements.php');
}
if ($action === 'rejeter' && $id) {
    $db->prepare("UPDATE paiements SET statut='rejete' WHERE id=?")->execute([$id]);
    flashMessage('info', 'Paiement rejeté.');
    redirect(SITE_URL . '/admin/paiements.php');
}

$paiements = $db->query("
    SELECT p.*, c.numero_contrat, u.prenom as loc_prenom, u.nom as loc_nom
    FROM paiements p JOIN contrats c ON p.contrat_id=c.id JOIN users u ON p.locataire_id=u.id
    ORDER BY p.date_paiement DESC
")->fetchAll();

$total_valide = array_sum(array_map(fn($p) => $p['statut']==='valide' ? $p['montant'] : 0, $paiements));
$page_title = 'Paiements';
include 'includes/sidebar_admin.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar">
        <div class="topbar-title">Gestion des Paiements</div>
        <div style="background:var(--white);border:1px solid var(--border);border-radius:10px;padding:8px 16px;font-size:14px;color:var(--text)">
            Total validé : <strong style="color:var(--gold)"><?= formatPrix($total_valide) ?></strong>
        </div>
    </div>
    <div class="dashboard-content">
        <?php $flash = getFlash(); if ($flash): ?>
        <div style="background:#f0fdf4;border-left:4px solid var(--green);padding:14px;border-radius:10px;margin-bottom:20px;color:#166534;display:flex;gap:10px">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>
        <div class="table-card">
            <table class="data-table">
                <thead><tr><th>Locataire</th><th>Contrat</th><th>Montant</th><th>Méthode</th><th>Mois</th><th>Référence</th><th>Statut</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (empty($paiements)): ?>
                    <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted)">Aucun paiement</td></tr>
                    <?php else: ?>
                    <?php foreach ($paiements as $p): ?>
                    <tr>
                        <td style="font-weight:600;font-size:13px"><?= htmlspecialchars($p['loc_prenom'].' '.$p['loc_nom']) ?></td>
                        <td style="font-size:12px;color:var(--gold);font-weight:600"><?= htmlspecialchars($p['numero_contrat']) ?></td>
                        <td style="font-weight:700;color:var(--dark)"><?= formatPrix($p['montant']) ?></td>
                        <td style="font-size:13px"><?= ucfirst(str_replace('_',' ',$p['methode_paiement'])) ?></td>
                        <td style="font-size:13px"><?= htmlspecialchars($p['mois_concerne'] ?? '-') ?></td>
                        <td style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($p['reference_paiement'] ?? '-') ?></td>
                        <td><span class="status-badge status-<?= $p['statut'] ?>"><?= $p['statut'] ?></span></td>
                        <td style="font-size:12px;color:var(--text-muted)"><?= date('d/m/Y',strtotime($p['date_paiement'])) ?></td>
                        <td>
                            <?php if ($p['statut'] === 'en_attente'): ?>
                            <div style="display:flex;gap:4px">
                                <button onclick="openPaiementModal(<?= $p['id'] ?>, 'valider', '<?= htmlspecialchars($p['loc_prenom'].' '.$p['loc_nom'], ENT_QUOTES) ?>', '<?= formatPrix($p['montant']) ?>', '<?= htmlspecialchars($p['numero_contrat'], ENT_QUOTES) ?>')"
                                    style="padding:5px 10px;border-radius:7px;background:#f0fdf4;color:var(--green);font-size:12px;border:none;cursor:pointer;font-weight:600">
                                    <i class="fas fa-check"></i> Valider
                                </button>
                                <button onclick="openPaiementModal(<?= $p['id'] ?>, 'rejeter', '<?= htmlspecialchars($p['loc_prenom'].' '.$p['loc_nom'], ENT_QUOTES) ?>', '<?= formatPrix($p['montant']) ?>', '<?= htmlspecialchars($p['numero_contrat'], ENT_QUOTES) ?>')"
                                    style="padding:5px 10px;border-radius:7px;background:#fef2f2;color:var(--red);font-size:12px;border:none;cursor:pointer;font-weight:600">
                                    <i class="fas fa-times"></i> Rejeter
                                </button>
                            </div>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL PAIEMENT -->
<div id="modal-paiement" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)" onclick="document.getElementById('modal-paiement').style.display='none'"></div>
    <div style="position:relative;background:white;border-radius:20px;padding:36px;max-width:460px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn .2s ease">
        <button onclick="document.getElementById('modal-paiement').style.display='none'" style="position:absolute;top:16px;right:16px;background:#f1f5f9;border:none;border-radius:50%;width:32px;height:32px;cursor:pointer;font-size:16px;color:var(--text-muted)">✕</button>
        <div style="text-align:center;margin-bottom:24px">
            <div id="p-icon" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px"></div>
            <h3 id="p-title" style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:10px"></h3>
        </div>
        <!-- Récap paiement -->
        <div style="background:#f8fafc;border-radius:14px;padding:16px;margin-bottom:24px">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px">
                <span style="color:var(--text-muted)">Locataire</span>
                <strong id="p-locataire"></strong>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px">
                <span style="color:var(--text-muted)">Contrat</span>
                <strong id="p-contrat" style="color:var(--gold)"></strong>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:14px">
                <span style="color:var(--text-muted)">Montant</span>
                <strong id="p-montant" style="font-size:16px"></strong>
            </div>
        </div>
        <div style="display:flex;gap:12px">
            <button onclick="document.getElementById('modal-paiement').style.display='none'" style="flex:1;padding:13px;border:1.5px solid #E5E7EB;border-radius:12px;background:white;cursor:pointer;font-size:14px;font-weight:600">Annuler</button>
            <a id="p-btn" href="#" style="flex:1;padding:13px;border-radius:12px;color:white;text-align:center;text-decoration:none;font-size:14px;font-weight:600"></a>
        </div>
    </div>
</div>

<style>
@keyframes modalIn { from{opacity:0;transform:scale(.95) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
</style>
<script>
const SITE_URL = '<?= SITE_URL ?>';
function openPaiementModal(id, action, locataire, montant, contrat) {
    const isValider = action === 'valider';
    document.getElementById('p-icon').style.background = isValider ? '#f0fdf4' : '#fef2f2';
    document.getElementById('p-icon').innerHTML = isValider ? '<i class="fas fa-check-circle" style="color:var(--green);font-size:26px"></i>' : '<i class="fas fa-times-circle" style="color:var(--red);font-size:26px"></i>';
    document.getElementById('p-title').textContent = isValider ? 'Valider ce paiement ?' : 'Rejeter ce paiement ?';
    document.getElementById('p-locataire').textContent = locataire;
    document.getElementById('p-contrat').textContent = contrat;
    document.getElementById('p-montant').textContent = montant;
    const btn = document.getElementById('p-btn');
    btn.style.background = isValider ? 'var(--green)' : 'var(--red)';
    btn.textContent = isValider ? 'Valider le paiement' : 'Rejeter le paiement';
    btn.href = SITE_URL + '/admin/paiements.php?action=' + action + '&id=' + id;
    document.getElementById('modal-paiement').style.display = 'flex';
}
</script>
</div>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
