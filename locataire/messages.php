<?php
require_once '../includes/config.php';
requireLogin();
$db = getDB();
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer'])) {
    $dest = (int)$_POST['destinataire_id'];
    $contenu = sanitize($_POST['contenu']);
    if ($dest && $contenu) {
        $db->prepare("INSERT INTO messages (expediteur_id,destinataire_id,sujet,contenu) VALUES (?,?,?,?)")->execute([$uid,$dest,sanitize($_POST['sujet'] ?? ''),$contenu]);
        flashMessage('succes', 'Message envoyé.');
        redirect(SITE_URL . '/locataire/messages.php');
    }
}
$messages_recus = $db->query("SELECT m.*, u.prenom as exp_prenom, u.nom as exp_nom FROM messages m JOIN users u ON m.expediteur_id=u.id WHERE m.destinataire_id=$uid ORDER BY m.date_envoi DESC LIMIT 20")->fetchAll();
$db->prepare("UPDATE messages SET lu=1 WHERE destinataire_id=?")->execute([$uid]);
$agents = $db->query("SELECT id, nom, prenom FROM users WHERE role IN ('agent','admin') AND statut='actif' ORDER BY nom")->fetchAll();
$page_title = 'Messages';
include 'includes/sidebar_locataire.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar"><div class="topbar-title">Messages</div></div>
    <div class="dashboard-content">
        <?php $flash = getFlash(); if ($flash): ?><div style="background:#f0fdf4;border-left:4px solid var(--green);padding:14px;border-radius:10px;margin-bottom:20px;color:#166534;display:flex;gap:10px"><i class="fas fa-check-circle"></i> <?= $flash['message'] ?></div><?php endif; ?>
        <div style="display:grid;grid-template-columns:1fr 360px;gap:24px">
            <div class="table-card">
                <div class="table-header"><span class="table-title">Messages reçus</span></div>
                <?php if (empty($messages_recus)): ?><div style="padding:40px;text-align:center;color:var(--text-muted)"><i class="fas fa-inbox fa-2x" style="opacity:0.2;display:block;margin-bottom:10px"></i>Aucun message</div>
                <?php else: ?><div><?php foreach ($messages_recus as $msg): ?>
                    <div style="padding:14px 20px;border-bottom:1px solid var(--cream-2)">
                        <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($msg['exp_prenom'].' '.$msg['exp_nom']) ?> <span style="font-size:11px;color:var(--text-muted);float:right"><?= timeAgo($msg['date_envoi']) ?></span></div>
                        <?php if ($msg['sujet']): ?><div style="font-size:13px;font-weight:600;color:var(--text-light);margin-bottom:4px"><?= htmlspecialchars($msg['sujet']) ?></div><?php endif; ?>
                        <div style="font-size:13px;color:var(--text-light)"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></div>
                    </div>
                <?php endforeach; ?></div>
                <?php endif; ?>
            </div>
            <div style="background:var(--white);border-radius:16px;padding:24px;border:1px solid #F1F5F9">
                <h3 style="font-family:'Playfair Display',serif;font-size:18px;margin-bottom:16px">Contacter un agent</h3>
                <form method="POST">
                    <div class="form-group"><label>Agent</label><select name="destinataire_id" class="form-control" required><option value="">Choisir...</option><?php foreach ($agents as $a): ?><option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['prenom'].' '.$a['nom']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label>Sujet</label><input type="text" name="sujet" class="form-control" placeholder="Objet du message"></div>
                    <div class="form-group"><label>Message *</label><textarea name="contenu" class="form-control" rows="5" required placeholder="Votre message..."></textarea></div>
                    <button type="submit" name="envoyer" class="btn btn-gold" style="width:100%;justify-content:center;border-radius:10px"><i class="fas fa-paper-plane"></i> Envoyer</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
