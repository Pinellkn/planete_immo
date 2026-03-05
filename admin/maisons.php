<?php
require_once '../includes/config.php';
requireAdmin();
$db = getDB();

$action = $_GET['action'] ?? 'liste';
$id = (int)($_GET['id'] ?? 0);

if ($action === 'supprimer' && $id) {
    $db->prepare("DELETE FROM maisons WHERE id = ?")->execute([$id]);
    flashMessage('succes', 'Annonce supprimée avec succès.');
    redirect(SITE_URL . '/admin/maisons.php');
}

if ($action === 'toggle_statut' && $id) {
    $m = $db->query("SELECT statut FROM maisons WHERE id = $id")->fetch();
    $ns = $m['statut'] === 'actif' ? 'inactif' : 'actif';
    $db->prepare("UPDATE maisons SET statut = ? WHERE id = ?")->execute([$ns, $id]);
    flashMessage('succes', 'Statut modifié.');
    redirect(SITE_URL . '/admin/maisons.php');
}

$maison = null;
if ($action === 'modifier' && $id) {
    $maison = $db->query("SELECT * FROM maisons WHERE id = $id")->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sauvegarder'])) {
    $data = [
        sanitize($_POST['titre']), sanitize($_POST['description']),
        (int)$_POST['type_id'], (int)$_POST['agent_id'], (int)$_POST['ville_id'],
        sanitize($_POST['quartier']), sanitize($_POST['adresse_complete']),
        (float)$_POST['prix_mensuel'], (float)$_POST['caution'],
        (float)$_POST['surface'], (int)$_POST['nb_chambres'],
        (int)$_POST['nb_salles_bain'], (int)$_POST['nb_toilettes'],
        (int)$_POST['nb_salons'],
        isset($_POST['climatisation']) ? 1 : 0,
        isset($_POST['eau_courante']) ? 1 : 0,
        isset($_POST['electricite']) ? 1 : 0,
        isset($_POST['gardien']) ? 1 : 0,
        isset($_POST['garage']) ? 1 : 0,
        isset($_POST['piscine']) ? 1 : 0,
        isset($_POST['meublee']) ? 1 : 0,
        isset($_POST['balcon']) ? 1 : 0,
        isset($_POST['cuisine_equipee']) ? 1 : 0,
        isset($_POST['connexion_internet']) ? 1 : 0,
        sanitize($_POST['disponibilite']),
        sanitize($_POST['statut']),
    ];

    if ($id) {
        $sql = "UPDATE maisons SET titre=?,description=?,type_id=?,agent_id=?,ville_id=?,quartier=?,adresse_complete=?,
                prix_mensuel=?,caution=?,surface=?,nb_chambres=?,nb_salles_bain=?,nb_toilettes=?,nb_salons=?,
                climatisation=?,eau_courante=?,electricite=?,gardien=?,garage=?,piscine=?,meublee=?,balcon=?,
                cuisine_equipee=?,connexion_internet=?,disponibilite=?,statut=? WHERE id=?";
        $data[] = $id;
        $db->prepare($sql)->execute($data);
        flashMessage('succes', 'Annonce modifiée avec succès.');
    } else {
        $sql = "INSERT INTO maisons (titre,description,type_id,agent_id,ville_id,quartier,adresse_complete,
                prix_mensuel,caution,surface,nb_chambres,nb_salles_bain,nb_toilettes,nb_salons,
                climatisation,eau_courante,electricite,gardien,garage,piscine,meublee,balcon,
                cuisine_equipee,connexion_internet,disponibilite,statut) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $db->prepare($sql)->execute($data);
        flashMessage('succes', 'Annonce ajoutée avec succès.');
    }
    redirect(SITE_URL . '/admin/maisons.php');
}

$villes = $db->query("SELECT * FROM villes ORDER BY nom")->fetchAll();
$types  = $db->query("SELECT * FROM types_maison ORDER BY libelle")->fetchAll();
$agents = $db->query("SELECT * FROM users WHERE role IN ('agent','admin') AND statut='actif' ORDER BY nom")->fetchAll();

$search = sanitize($_GET['s'] ?? '');
$w = $search ? "WHERE (m.titre LIKE '%$search%' OR v.nom LIKE '%$search%' OR u.nom LIKE '%$search%')" : '';
$maisons = $db->query("
    SELECT m.*, v.nom as ville_nom, t.libelle as type_libelle, u.nom as agent_nom, u.prenom as agent_prenom
    FROM maisons m JOIN villes v ON m.ville_id=v.id JOIN types_maison t ON m.type_id=t.id JOIN users u ON m.agent_id=u.id
    $w ORDER BY m.date_creation DESC
")->fetchAll();

$page_title = 'Gestion des Annonces';
include 'includes/sidebar_admin.php';
?>

<div class="dashboard-main">
    <div class="dashboard-topbar">
        <div class="topbar-title"><?= $action === 'ajouter' ? 'Nouvelle annonce' : ($action === 'modifier' ? 'Modifier l\'annonce' : 'Gestion des annonces') ?></div>
        <div class="topbar-actions">
            <?php if ($action === 'liste'): ?>
            <a href="?action=ajouter" class="btn btn-gold btn-sm"><i class="fas fa-plus"></i> Ajouter</a>
            <?php else: ?>
            <a href="<?= SITE_URL ?>/admin/maisons.php" class="btn btn-dark btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-content">
        <?php $flash = getFlash(); if ($flash): ?>
        <div style="background:<?= $flash['type']==='succes'?'#f0fdf4':'#fef2f2' ?>;border-left:4px solid <?= $flash['type']==='succes'?'var(--green)':'var(--red)' ?>;padding:14px 16px;border-radius:10px;margin-bottom:20px;color:<?= $flash['type']==='succes'?'#166534':'#991b1b' ?>;display:flex;gap:10px">
            <i class="fas fa-<?= $flash['type']==='succes'?'check-circle':'exclamation-circle' ?>"></i> <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <?php if ($action === 'liste'): ?>
        <div style="background:var(--white);border-radius:14px;padding:20px;margin-bottom:20px;border:1px solid #F1F5F9">
            <form method="GET" style="display:flex;gap:12px;align-items:center">
                <input type="hidden" name="action" value="liste">
                <input type="text" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher une annonce..." style="flex:1;padding:10px 14px;border:1.5px solid #E5E7EB;border-radius:10px;font-size:14px;outline:none">
                <button type="submit" class="btn btn-gold btn-sm"><i class="fas fa-search"></i> Chercher</button>
                <?php if ($search): ?><a href="?" class="btn btn-dark btn-sm">Réinitialiser</a><?php endif; ?>
            </form>
        </div>

        <div class="table-card">
            <table class="data-table">
                <thead><tr>
                    <th>Titre & Localisation</th><th>Agent</th><th>Prix/mois</th><th>Type</th><th>Statut</th><th>Dispo.</th><th>Vues</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    <?php if (empty($maisons)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">Aucune annonce trouvée</td></tr>
                    <?php else: ?>
                    <?php foreach ($maisons as $ma): ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($ma['titre']) ?></div>
                            <div style="font-size:12px;color:var(--text-muted)"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($ma['quartier'] ?? '') ?>, <?= htmlspecialchars($ma['ville_nom']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($ma['agent_prenom'] . ' ' . $ma['agent_nom']) ?></td>
                        <td style="font-weight:700;color:var(--gold)"><?= formatPrix($ma['prix_mensuel']) ?></td>
                        <td><?= htmlspecialchars($ma['type_libelle']) ?></td>
                        <td>
                            <button onclick="openToggleStatutModal(<?= $ma['id'] ?>, '<?= htmlspecialchars($ma['titre'], ENT_QUOTES) ?>', '<?= $ma['statut'] ?>')" style="background:none;border:none;cursor:pointer;padding:0">
                                <span class="status-badge status-<?= $ma['statut'] ?>"><?= $ma['statut'] ?></span>
                            </button>
                        </td>
                        <td><span class="status-badge status-<?= $ma['disponibilite'] ?>"><?= $ma['disponibilite'] ?></span></td>
                        <td><?= $ma['vues'] ?></td>
                        <td>
                            <div style="display:flex;gap:4px">
                                <a href="<?= SITE_URL ?>/detail.php?id=<?= $ma['id'] ?>" target="_blank"
                                   style="padding:5px 9px;border-radius:7px;background:#f0fdf4;color:var(--green);font-size:12px" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="?action=modifier&id=<?= $ma['id'] ?>"
                                   style="padding:5px 9px;border-radius:7px;background:#eff6ff;color:var(--blue);font-size:12px" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="openDeleteMaisonModal(<?= $ma['id'] ?>, '<?= htmlspecialchars($ma['titre'], ENT_QUOTES) ?>')"
                                   style="padding:5px 9px;border-radius:7px;background:#fef2f2;color:var(--red);font-size:12px;border:none;cursor:pointer" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($action === 'ajouter' || $action === 'modifier'): ?>
        <form method="POST" class="table-card" style="padding:28px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                <div style="grid-column:span 2" class="form-group">
                    <label>Titre de l'annonce *</label>
                    <input type="text" name="titre" class="form-control" required placeholder="Ex: Belle villa F4 à Fidjrossè" value="<?= htmlspecialchars($maison['titre'] ?? $_POST['titre'] ?? '') ?>">
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
                    <label>Agent responsable *</label>
                    <select name="agent_id" class="form-control" required>
                        <?php foreach ($agents as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($maison['agent_id'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></option>
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
                <div style="grid-column:span 2" class="form-group">
                    <label>Adresse complète</label>
                    <input type="text" name="adresse_complete" class="form-control" placeholder="Rue, numéro, quartier, ville" value="<?= htmlspecialchars($maison['adresse_complete'] ?? '') ?>">
                </div>
                <div style="grid-column:span 2" class="form-group">
                    <label>Description *</label>
                    <textarea name="description" class="form-control" rows="5" required placeholder="Décrivez la propriété en détail..."><?= htmlspecialchars($maison['description'] ?? '') ?></textarea>
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
                    <input type="number" name="surface" class="form-control" step="0.1" min="0" value="<?= $maison['surface'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Nombre de chambres</label>
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
                        <option value="disponible" <?= ($maison['disponibilite'] ?? '') === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="louee" <?= ($maison['disponibilite'] ?? '') === 'louee' ? 'selected' : '' ?>>Louée</option>
                        <option value="maintenance" <?= ($maison['disponibilite'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="statut" class="form-control">
                        <option value="actif" <?= ($maison['statut'] ?? 'actif') === 'actif' ? 'selected' : '' ?>>Actif (visible)</option>
                        <option value="inactif" <?= ($maison['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                        <option value="en_attente" <?= ($maison['statut'] ?? '') === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                    </select>
                </div>
            </div>
            <h3 style="font-family:'Playfair Display',serif;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid var(--border)">Équipements</h3>
            <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:24px">
                <?php
                $equipements = ['climatisation'=>'Climatisation','eau_courante'=>'Eau courante','electricite'=>'Électricité','gardien'=>'Gardien','garage'=>'Garage','piscine'=>'Piscine','meublee'=>'Meublée','balcon'=>'Balcon','cuisine_equipee'=>'Cuisine équipée','connexion_internet'=>'Internet'];
                foreach ($equipements as $key => $label):
                ?>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px;background:var(--cream);border-radius:10px;border:1.5px solid <?= ($maison[$key] ?? 0) ? 'var(--gold)' : '#E5E7EB' ?>">
                    <input type="checkbox" name="<?= $key ?>" <?= ($maison[$key] ?? 0) ? 'checked' : '' ?> style="accent-color:var(--gold)">
                    <span style="font-size:13px"><?= $label ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <div style="display:flex;gap:12px">
                <button type="submit" name="sauvegarder" class="btn btn-gold btn-lg">
                    <i class="fas fa-save"></i> <?= $id ? 'Mettre à jour' : 'Publier l\'annonce' ?>
                </button>
                <a href="<?= SITE_URL ?>/admin/maisons.php" class="btn btn-dark">Annuler</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL SUPPRESSION MAISON -->
<div id="modal-delete-maison" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)" onclick="document.getElementById('modal-delete-maison').style.display='none'"></div>
    <div style="position:relative;background:white;border-radius:20px;padding:36px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn .2s ease">
        <div style="text-align:center;margin-bottom:24px">
            <div style="width:64px;height:64px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                <i class="fas fa-home" style="font-size:24px;color:var(--red)"></i>
            </div>
            <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:8px">Supprimer l'annonce</h3>
            <p style="color:var(--text-muted);font-size:14px">Vous allez supprimer définitivement l'annonce <strong id="dm-name"></strong>. Toutes les demandes associées seront perdues.</p>
        </div>
        <div style="display:flex;gap:12px">
            <button onclick="document.getElementById('modal-delete-maison').style.display='none'" style="flex:1;padding:13px;border:1.5px solid #E5E7EB;border-radius:12px;background:white;cursor:pointer;font-size:14px;font-weight:600">Annuler</button>
            <a id="dm-link" href="#" style="flex:1;padding:13px;border-radius:12px;background:var(--red);color:white;text-align:center;text-decoration:none;font-size:14px;font-weight:600">Supprimer</a>
        </div>
    </div>
</div>

<!-- MODAL TOGGLE STATUT MAISON -->
<div id="modal-toggle-maison" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)" onclick="document.getElementById('modal-toggle-maison').style.display='none'"></div>
    <div style="position:relative;background:white;border-radius:20px;padding:36px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn .2s ease">
        <div style="text-align:center;margin-bottom:24px">
            <div style="width:64px;height:64px;border-radius:50%;background:#fffbeb;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                <i class="fas fa-toggle-on" style="font-size:24px;color:var(--gold)"></i>
            </div>
            <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:8px">Modifier la visibilité</h3>
            <p style="color:var(--text-muted);font-size:14px" id="tm-desc"></p>
        </div>
        <div style="display:flex;gap:12px">
            <button onclick="document.getElementById('modal-toggle-maison').style.display='none'" style="flex:1;padding:13px;border:1.5px solid #E5E7EB;border-radius:12px;background:white;cursor:pointer;font-size:14px;font-weight:600">Annuler</button>
            <a id="tm-link" href="#" style="flex:1;padding:13px;border-radius:12px;background:var(--gold);color:white;text-align:center;text-decoration:none;font-size:14px;font-weight:600">Confirmer</a>
        </div>
    </div>
</div>

<style>
@keyframes modalIn { from{opacity:0;transform:scale(.95) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
</style>
<script>
const SITE_URL = '<?= SITE_URL ?>';
function openDeleteMaisonModal(id, titre) {
    document.getElementById('dm-name').textContent = titre;
    document.getElementById('dm-link').href = SITE_URL + '/admin/maisons.php?action=supprimer&id=' + id;
    document.getElementById('modal-delete-maison').style.display = 'flex';
}
function openToggleStatutModal(id, titre, statut) {
    const action = statut === 'actif' ? 'désactiver' : 'activer';
    document.getElementById('tm-desc').innerHTML = 'Vous allez <strong>' + action + '</strong> l\'annonce <strong>' + titre + '</strong>.';
    document.getElementById('tm-link').textContent = action.charAt(0).toUpperCase() + action.slice(1);
    document.getElementById('tm-link').href = SITE_URL + '/admin/maisons.php?action=toggle_statut&id=' + id;
    document.getElementById('modal-toggle-maison').style.display = 'flex';
}
</script>
</div>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
