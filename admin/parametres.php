<?php
require_once '../includes/config.php';
requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $k => $v) {
        if ($k === 'submit') continue;
        $k = sanitize($k); $v = sanitize($v);
        $db->prepare("UPDATE parametres SET valeur=? WHERE cle=?")->execute([$v, $k]);
    }
    flashMessage('succes', 'Paramètres sauvegardés.');
    redirect(SITE_URL . '/admin/parametres.php');
}

$params = $db->query("SELECT * FROM parametres ORDER BY id")->fetchAll();
$params_map = array_column($params, 'valeur', 'cle');

$page_title = 'Paramètres';
include 'includes/sidebar_admin.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar"><div class="topbar-title">Paramètres du Site</div></div>
    <div class="dashboard-content">
        <?php $flash = getFlash(); if ($flash): ?>
        <div style="background:#f0fdf4;border-left:4px solid var(--green);padding:14px;border-radius:10px;margin-bottom:20px;color:#166534;display:flex;gap:10px">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>
        <form method="POST">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
                <!-- Informations du site -->
                <div style="background:var(--white);border-radius:16px;padding:28px;border:1px solid #F1F5F9">
                    <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid var(--border)">
                        <i class="fas fa-globe" style="color:var(--gold)"></i> Informations du site
                    </h3>
                    <?php foreach (['site_nom'=>'Nom du site','site_slogan'=>'Slogan','site_email'=>'Email de contact','site_telephone'=>'Téléphone','site_adresse'=>'Adresse physique'] as $k => $label): ?>
                    <div class="form-group">
                        <label><?= $label ?></label>
                        <input type="text" name="<?= $k ?>" class="form-control" value="<?= htmlspecialchars($params_map[$k] ?? '') ?>">
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Paramètres métier -->
                <div style="background:var(--white);border-radius:16px;padding:28px;border:1px solid #F1F5F9">
                    <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid var(--border)">
                        <i class="fas fa-cog" style="color:var(--gold)"></i> Paramètres métier
                    </h3>
                    <div class="form-group">
                        <label>Commission agent (%)</label>
                        <input type="number" name="commission_agent" class="form-control" min="0" max="100" value="<?= htmlspecialchars($params_map['commission_agent'] ?? '5') ?>">
                    </div>
                    <div class="form-group">
                        <label>Devise</label>
                        <input type="text" name="devise" class="form-control" value="<?= htmlspecialchars($params_map['devise'] ?? 'FCFA') ?>">
                    </div>
                    <div class="form-group">
                        <label>Annonces par page</label>
                        <select name="maisons_par_page" class="form-control">
                            <?php foreach ([6,8,12,16,24] as $n): ?>
                            <option value="<?= $n ?>" <?= ($params_map['maisons_par_page'] ?? '12') == $n ? 'selected' : '' ?>><?= $n ?> annonces</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mode maintenance</label>
                        <select name="maintenance" class="form-control">
                            <option value="0" <?= ($params_map['maintenance'] ?? '0') === '0' ? 'selected' : '' ?>>Désactivé (site en ligne)</option>
                            <option value="1" <?= ($params_map['maintenance'] ?? '0') === '1' ? 'selected' : '' ?>>Activé (site en maintenance)</option>
                        </select>
                    </div>
                </div>

                <!-- Réseaux sociaux -->
                <div style="background:var(--white);border-radius:16px;padding:28px;border:1px solid #F1F5F9;grid-column:span 2">
                    <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid var(--border)">
                        <i class="fas fa-share-alt" style="color:var(--gold)"></i> Réseaux sociaux
                    </h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <?php foreach (['facebook'=>'🔵 Facebook','whatsapp'=>'🟢 WhatsApp'] as $k => $label): ?>
                        <div class="form-group">
                            <label><?= $label ?></label>
                            <input type="text" name="<?= $k ?>" class="form-control" value="<?= htmlspecialchars($params_map[$k] ?? '') ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div style="margin-top:24px">
                <button type="submit" name="submit" class="btn btn-gold btn-lg"><i class="fas fa-save"></i> Sauvegarder les paramètres</button>
            </div>
        </form>
    </div>
</div>
</div>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
