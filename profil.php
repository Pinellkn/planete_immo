<?php
require_once 'includes/config.php';
requireLogin();
$db = getDB();
$uid = $_SESSION['user_id'];

$user = $db->prepare("SELECT * FROM users WHERE id=?")->execute([$uid]) && ($user = $db->query("SELECT * FROM users WHERE id=$uid")->fetch());
$erreur = $succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom       = sanitize($_POST['nom'] ?? '');
    $prenom    = sanitize($_POST['prenom'] ?? '');
    $telephone = sanitize($_POST['telephone'] ?? '');
    $ville     = sanitize($_POST['ville'] ?? '');
    $quartier  = sanitize($_POST['quartier'] ?? '');

    if (!$nom || !$prenom || !$telephone) {
        $erreur = 'Veuillez remplir les champs obligatoires.';
    } else {
        $db->prepare("UPDATE users SET nom=?,prenom=?,telephone=?,ville=?,quartier=? WHERE id=?")->execute([$nom,$prenom,$telephone,$ville,$quartier,$uid]);
        $_SESSION['nom']    = $nom;
        $_SESSION['prenom'] = $prenom;

        // Changement de mot de passe
        if (!empty($_POST['nouveau_mdp'])) {
            $u = $db->query("SELECT mot_de_passe FROM users WHERE id=$uid")->fetch();
            if (password_verify($_POST['ancien_mdp'] ?? '', $u['mot_de_passe'])) {
                if ($_POST['nouveau_mdp'] === $_POST['confirmer_mdp']) {
                    $hash = password_hash($_POST['nouveau_mdp'], PASSWORD_DEFAULT);
                    $db->prepare("UPDATE users SET mot_de_passe=? WHERE id=?")->execute([$hash, $uid]);
                    $succes = 'Profil et mot de passe mis à jour !';
                } else { $erreur = 'Les nouveaux mots de passe ne correspondent pas.'; }
            } else { $erreur = 'Mot de passe actuel incorrect.'; }
        } else {
            $succes = 'Profil mis à jour avec succès !';
        }
    }
}

$user = $db->query("SELECT * FROM users WHERE id=$uid")->fetch();
$villes = $db->query("SELECT * FROM villes ORDER BY nom")->fetchAll();
$page_title = 'Mon Profil';
include 'includes/header.php';
?>
<main style="padding-top:76px;background:#F4F6FB;min-height:100vh">
    <div class="container" style="padding:40px 24px;max-width:900px">
        <h1 style="font-family:'Playfair Display',serif;font-size:32px;margin-bottom:28px">Mon <span class="text-gold">Profil</span></h1>

        <?php if ($succes): ?>
        <div style="background:#f0fdf4;border-left:4px solid var(--green);padding:14px;border-radius:10px;margin-bottom:20px;color:#166534;display:flex;gap:10px">
            <i class="fas fa-check-circle"></i> <?= $succes ?>
        </div>
        <?php elseif ($erreur): ?>
        <div style="background:#fef2f2;border-left:4px solid var(--red);padding:14px;border-radius:10px;margin-bottom:20px;color:#991b1b;display:flex;gap:10px">
            <i class="fas fa-exclamation-circle"></i> <?= $erreur ?>
        </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:280px 1fr;gap:28px;align-items:start">
            <!-- Carte profil -->
            <div style="background:var(--white);border-radius:20px;padding:28px;text-align:center;box-shadow:var(--shadow-sm);border:1px solid var(--border)">
                <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--gold-light));display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:36px;color:white;font-family:'Playfair Display',serif;font-weight:700">
                    <?= strtoupper(substr($user['prenom'],0,1)) ?>
                </div>
                <div style="font-family:'Playfair Display',serif;font-size:20px;font-weight:700;margin-bottom:4px"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></div>
                <div style="font-size:13px;color:var(--text-muted);margin-bottom:12px"><?= htmlspecialchars($user['email']) ?></div>
                <span style="background:<?= $user['role']==='admin'?'#fef3c7':($user['role']==='agent'?'#eff6ff':'#f0fdf4') ?>;color:<?= $user['role']==='admin'?'#92400e':($user['role']==='agent'?'var(--blue)':'var(--green)') ?>;padding:5px 14px;border-radius:50px;font-size:12px;font-weight:700;text-transform:uppercase">
                    <?= ucfirst($user['role']) ?>
                </span>
                <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border);font-size:13px;color:var(--text-muted)">
                    Membre depuis <?= date('F Y', strtotime($user['date_creation'])) ?>
                </div>
                <?php if ($user['derniere_connexion']): ?>
                <div style="font-size:12px;color:var(--text-muted);margin-top:6px">
                    Dernière connexion : <?= timeAgo($user['derniere_connexion']) ?>
                </div>
                <?php endif; ?>

                <a href="<?= SITE_URL ?>/<?= $user['role'] ?>/dashboard.php" class="btn btn-gold btn-sm" style="margin-top:20px;width:100%;justify-content:center">
                    <i class="fas fa-tachometer-alt"></i> Mon tableau de bord
                </a>
            </div>

            <!-- Formulaire -->
            <div style="background:var(--white);border-radius:20px;padding:32px;box-shadow:var(--shadow-sm);border:1px solid var(--border)">
                <form method="POST">
                    <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:20px">Informations personnelles</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                        <div class="form-group">
                            <label>Nom *</label>
                            <div class="input-wrapper"><i class="fas fa-user"></i>
                                <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Prénom *</label>
                            <div class="input-wrapper"><i class="fas fa-user"></i>
                                <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Téléphone *</label>
                            <div class="input-wrapper"><i class="fas fa-phone"></i>
                                <input type="tel" name="telephone" value="<?= htmlspecialchars($user['telephone']) ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email (non modifiable)</label>
                            <div class="input-wrapper"><i class="fas fa-envelope"></i>
                                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly style="opacity:0.7;cursor:not-allowed">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Ville</label>
                            <div class="input-wrapper"><i class="fas fa-city"></i>
                                <select name="ville">
                                    <option value="">Choisir une ville</option>
                                    <?php foreach ($villes as $v): ?>
                                    <option value="<?= $v['nom'] ?>" <?= $user['ville'] === $v['nom'] ? 'selected' : '' ?>><?= htmlspecialchars($v['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Quartier</label>
                            <div class="input-wrapper"><i class="fas fa-map-marker-alt"></i>
                                <input type="text" name="quartier" placeholder="Votre quartier" value="<?= htmlspecialchars($user['quartier'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin:24px 0 16px;padding-top:20px;border-top:1px solid var(--border)">Changer le mot de passe</h3>
                    <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px">Laissez vide si vous ne souhaitez pas changer votre mot de passe.</p>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:24px">
                        <div class="form-group">
                            <label>Mot de passe actuel</label>
                            <div class="input-wrapper"><i class="fas fa-lock"></i>
                                <input type="password" name="ancien_mdp" placeholder="••••••">
                                <button type="button" class="password-toggle"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nouveau mot de passe</label>
                            <div class="input-wrapper"><i class="fas fa-lock"></i>
                                <input type="password" name="nouveau_mdp" placeholder="Min. 6 caractères">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Confirmer</label>
                            <div class="input-wrapper"><i class="fas fa-lock"></i>
                                <input type="password" name="confirmer_mdp" placeholder="Répéter le mot de passe">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gold btn-lg">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<script>const SITE_URL = '<?= SITE_URL ?>';</script>
<?php include 'includes/footer.php'; ?>
