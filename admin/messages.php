<?php
require_once '../includes/config.php';
requireAdmin();
$db = getDB();
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer'])) {
    $dest = (int)$_POST['destinataire_id'];
    $sujet = sanitize($_POST['sujet']);
    $contenu = sanitize($_POST['contenu']);
    if ($dest && $contenu) {
        $db->prepare("INSERT INTO messages (expediteur_id,destinataire_id,sujet,contenu) VALUES (?,?,?,?)")->execute([$uid,$dest,$sujet,$contenu]);
        flashMessage('succes', 'Message envoyé.');
        redirect(SITE_URL . '/admin/messages.php');
    }
}

$messages_recus = $db->query("
    SELECT m.*, u.prenom as exp_prenom, u.nom as exp_nom, u.role as exp_role
    FROM messages m JOIN users u ON m.expediteur_id=u.id
    WHERE m.destinataire_id=$uid ORDER BY m.date_envoi DESC LIMIT 20
")->fetchAll();
$db->prepare("UPDATE messages SET lu=1 WHERE destinataire_id=?")->execute([$uid]);

$users = $db->query("SELECT id, nom, prenom, role FROM users WHERE id != $uid AND statut='actif' ORDER BY nom")->fetchAll();

$page_title = 'Messages';
include 'includes/sidebar_admin.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar"><div class="topbar-title">Messages (<?= count($messages_recus) ?> reçus)</div></div>
    <div class="dashboard-content">
        <?php $flash = getFlash(); if ($flash): ?>
        <div style="background:#f0fdf4;border-left:4px solid var(--green);padding:14px;border-radius:10px;margin-bottom:20px;color:#166534;display:flex;gap:10px">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start">
            <!-- Messages reçus -->
            <div class="table-card">
                <div class="table-header"><span class="table-title">📨 Messages reçus</span></div>
                <?php if (empty($messages_recus)): ?>
                <div style="padding:40px;text-align:center;color:var(--text-muted)"><i class="fas fa-inbox fa-2x" style="opacity:0.2;display:block;margin-bottom:10px"></i>Aucun message</div>
                <?php else: ?>
                <div>
                    <?php foreach ($messages_recus as $msg): ?>
                    <div style="padding:16px 20px;border-bottom:1px solid var(--cream-2);<?= !$msg['lu'] ? 'background:#fffbeb;' : '' ?>">
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                            <div style="display:flex;gap:10px;align-items:center">
                                <div style="width:32px;height:32px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:12px">
                                    <?= strtoupper(substr($msg['exp_prenom'],0,1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($msg['exp_prenom'].' '.$msg['exp_nom']) ?></div>
                                    <div style="font-size:11px;color:var(--text-muted)"><?= $msg['exp_role'] ?></div>
                                </div>
                            </div>
                            <div style="font-size:11px;color:var(--text-muted)"><?= timeAgo($msg['date_envoi']) ?></div>
                        </div>
                        <?php if ($msg['sujet']): ?>
                        <div style="font-weight:600;font-size:13px;margin-bottom:4px"><?= htmlspecialchars($msg['sujet']) ?></div>
                        <?php endif; ?>
                        <div style="font-size:13px;color:var(--text-light);line-height:1.5"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Envoyer un message -->
            <div style="background:var(--white);border-radius:16px;padding:24px;border:1px solid #F1F5F9">
                <h3 style="font-family:'Playfair Display',serif;font-size:18px;margin-bottom:20px">✉️ Envoyer un message</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Destinataire</label>
                        <select name="destinataire_id" class="form-control" required>
                            <option value="">Choisir un utilisateur</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?> (<?= $u['role'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sujet</label>
                        <input type="text" name="sujet" class="form-control" placeholder="Objet du message">
                    </div>
                    <div class="form-group">
                        <label>Message *</label>
                        <textarea name="contenu" class="form-control" rows="5" required placeholder="Votre message..."></textarea>
                    </div>
                    <button type="submit" name="envoyer" class="btn btn-gold" style="width:100%;justify-content:center;border-radius:10px">
                        <i class="fas fa-paper-plane"></i> Envoyer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
