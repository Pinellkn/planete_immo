<?php
require_once '../includes/config.php';
requireAgent();
$db = getDB();
$uid = $_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);
$maison = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM maisons WHERE id=? AND agent_id=?");
    $stmt->execute([$id, $uid]);
    $maison = $stmt->fetch();
    if (!$maison) redirect(SITE_URL . '/agent/mes_maisons.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        sanitize($_POST['titre']), sanitize($_POST['description']),
        (int)$_POST['type_id'], $uid, (int)$_POST['ville_id'],
        sanitize($_POST['quartier']), sanitize($_POST['adresse_complete']),
        (float)$_POST['prix_mensuel'], (float)($_POST['caution'] ?? 0),
        (float)($_POST['surface'] ?? 0), (int)($_POST['nb_chambres'] ?? 1),
        (int)($_POST['nb_salles_bain'] ?? 1), (int)($_POST['nb_toilettes'] ?? 1),
        (int)($_POST['nb_salons'] ?? 1),
        isset($_POST['climatisation'])?1:0, isset($_POST['eau_courante'])?1:0,
        isset($_POST['electricite'])?1:0, isset($_POST['gardien'])?1:0,
        isset($_POST['garage'])?1:0, isset($_POST['piscine'])?1:0,
        isset($_POST['meublee'])?1:0, isset($_POST['balcon'])?1:0,
        isset($_POST['cuisine_equipee'])?1:0, isset($_POST['connexion_internet'])?1:0,
        sanitize($_POST['disponibilite'] ?? 'disponible'),
        'en_attente'
    ];
    if ($id) {
        $data[] = $id;
        $db->prepare("UPDATE maisons SET titre=?,description=?,type_id=?,agent_id=?,ville_id=?,quartier=?,adresse_complete=?,
            prix_mensuel=?,caution=?,surface=?,nb_chambres=?,nb_salles_bain=?,nb_toilettes=?,nb_salons=?,
            climatisation=?,eau_courante=?,electricite=?,gardien=?,garage=?,piscine=?,meublee=?,balcon=?,
            cuisine_equipee=?,connexion_internet=?,disponibilite=?,statut=? WHERE id=?")->execute($data);
        flashMessage('succes', 'Annonce mise à jour et soumise pour validation.');
    } else {
        $db->prepare("INSERT INTO maisons (titre,description,type_id,agent_id,ville_id,quartier,adresse_complete,
            prix_mensuel,caution,surface,nb_chambres,nb_salles_bain,nb_toilettes,nb_salons,
            climatisation,eau_courante,electricite,gardien,garage,piscine,meublee,balcon,
            cuisine_equipee,connexion_internet,disponibilite,statut) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")->execute($data);
        flashMessage('succes', 'Annonce créée et soumise pour validation par l\'admin.');
    }
    redirect(SITE_URL . '/agent/mes_maisons.php');
}

$villes = $db->query("SELECT * FROM villes ORDER BY nom")->fetchAll();
$types  = $db->query("SELECT * FROM types_maison ORDER BY libelle")->fetchAll();
$page_title = $id ? 'Modifier l\'annonce' : 'Nouvelle annonce';
include 'includes/sidebar_agent.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar">
        <div class="topbar-title"><?= $id ? 'Modifier l\'annonce' : 'Nouvelle annonce' ?></div>
        <div class="topbar-actions">
            <a href="<?= SITE_URL ?>/agent/mes_maisons.php" class="btn btn-dark btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
        </div>
    </div>
    <div class="dashboard-content">
        <div style="background:var(--white);border-radius:16px;padding:32px;border:1px solid #F1F5F9">
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;margin-bottom:24px;font-size:14px;color:#92400e">
                <i class="fas fa-info-circle"></i> Votre annonce sera soumise pour validation à l'administrateur avant d'être publiée.
            </div>
            <form method="POST">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div style="grid-column:span 2" class="form-group">
                        <label>Titre de l'annonce *</label>
                        <input type="text" name="titre" class="form-control" required placeholder="Ex: Belle villa F4 à Fidjrossè" value="<?= htmlspecialchars($maison['titre'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Type de logement *</label>
                        <select name="type_id" class="form-control" required>
                            <?php foreach ($types as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($maison['type_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['libelle']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ville *</label>
                        <select name="ville_id" class="form-control" required>
                            <?php foreach ($villes as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= ($maison['ville_id'] ?? '') == $v['id'] ? 'selected' : '' ?>><?= htmlspecialchars($v['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quartier</label>
                        <input type="text" name="quartier" class="form-control" placeholder="Ex: Fidjrossè" value="<?= htmlspecialchars($maison['quartier'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Prix mensuel (FCFA) *</label>
                        <input type="number" name="prix_mensuel" class="form-control" required min="0" value="<?= $maison['prix_mensuel'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Caution (FCFA)</label>
                        <input type="number" name="caution" class="form-control" min="0" value="<?= $maison['caution'] ?? '0' ?>">
                    </div>
                    <div class="form-group">
                        <label>Surface (m²)</label>
                        <input type="number" name="surface" class="form-control" step="0.1" value="<?= $maison['surface'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Chambres</label>
                        <input type="number" name="nb_chambres" class="form-control" min="0" value="<?= $maison['nb_chambres'] ?? '1' ?>">
                    </div>
                    <div class="form-group">
                        <label>Salles de bain</label>
                        <input type="number" name="nb_salles_bain" class="form-control" min="0" value="<?= $maison['nb_salles_bain'] ?? '1' ?>">
                    </div>
                    <div class="form-group">
                        <label>Toilettes</label>
                        <input type="number" name="nb_toilettes" class="form-control" min="0" value="<?= $maison['nb_toilettes'] ?? '1' ?>">
                    </div>
                    <div class="form-group">
                        <label>Salons</label>
                        <input type="number" name="nb_salons" class="form-control" min="0" value="<?= $maison['nb_salons'] ?? '1' ?>">
                    </div>
                    <div class="form-group">
                        <label>Disponibilité</label>
                        <select name="disponibilite" class="form-control">
                            <option value="disponible" <?= ($maison['disponibilite'] ?? 'disponible') === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                            <option value="maintenance" <?= ($maison['disponibilite'] ?? '') === 'maintenance' ? 'selected' : '' ?>>En maintenance</option>
                        </select>
                    </div>
                    <div style="grid-column:span 2" class="form-group">
                        <label>Adresse complète</label>
                        <input type="text" name="adresse_complete" class="form-control" placeholder="Rue, quartier, ville" value="<?= htmlspecialchars($maison['adresse_complete'] ?? '') ?>">
                    </div>
                    <div style="grid-column:span 2" class="form-group">
                        <label>Description détaillée *</label>
                        <textarea name="description" class="form-control" rows="6" required placeholder="Décrivez en détail la propriété (état, environnement, accès, particularités...)"><?= htmlspecialchars($maison['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <h3 style="font-family:'Playfair Display',serif;margin:24px 0 16px;padding-bottom:10px;border-bottom:2px solid var(--border)">Équipements disponibles</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-bottom:28px">
                    <?php foreach (['climatisation'=>'❄️ Climatisation','eau_courante'=>'💧 Eau courante','electricite'=>'⚡ Électricité','gardien'=>'🛡️ Gardien','garage'=>'🚗 Garage','piscine'=>'🏊 Piscine','meublee'=>'🛋️ Meublée','balcon'=>'🏢 Balcon','cuisine_equipee'=>'🍳 Cuisine équipée','connexion_internet'=>'📶 Internet'] as $k => $label): ?>
                    <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:1.5px solid <?= ($maison[$k] ?? 0) ? 'var(--gold)' : '#E5E7EB' ?>;border-radius:10px;cursor:pointer;font-size:14px;background:<?= ($maison[$k] ?? 0) ? 'rgba(200,151,58,0.06)' : 'var(--cream)' ?>">
                        <input type="checkbox" name="<?= $k ?>" <?= ($maison[$k] ?? 0) ? 'checked' : '' ?> style="accent-color:var(--gold)">
                        <?= $label ?>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div style="display:flex;gap:12px">
                    <button type="submit" class="btn btn-gold btn-lg"><i class="fas fa-paper-plane"></i> <?= $id ? 'Mettre à jour' : 'Soumettre l\'annonce' ?></button>
                    <a href="<?= SITE_URL ?>/agent/mes_maisons.php" class="btn btn-dark">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
