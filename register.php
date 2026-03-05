<?php
require_once 'includes/config.php';

if (isLoggedIn()) redirect(SITE_URL . '/' . getRole() . '/dashboard.php');

$erreur = '';
$succes = '';
$role_pre = sanitize($_GET['role'] ?? 'locataire');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom       = sanitize($_POST['nom'] ?? '');
    $prenom    = sanitize($_POST['prenom'] ?? '');
    $email     = sanitize($_POST['email'] ?? '');
    $telephone = sanitize($_POST['telephone'] ?? '');
    $ville     = sanitize($_POST['ville'] ?? '');
    $role      = in_array($_POST['role'] ?? '', ['locataire', 'agent']) ? $_POST['role'] : 'locataire';
    $mdp       = $_POST['mot_de_passe'] ?? '';
    $mdp2      = $_POST['mot_de_passe2'] ?? '';

    if (!$nom || !$prenom || !$email || !$telephone || !$mdp) {
        $erreur = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'L\'adresse email n\'est pas valide.';
    } elseif (strlen($mdp) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($mdp !== $mdp2) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } else {
        $db = getDB();
        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $erreur = 'Cette adresse email est déjà utilisée.';
        } else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (nom, prenom, email, telephone, ville, role, mot_de_passe, statut) VALUES (?, ?, ?, ?, ?, ?, ?, 'actif')");
            $stmt->execute([$nom, $prenom, $email, $telephone, $ville, $role, $hash]);
            
            $new_id = $db->lastInsertId();
            $_SESSION['user_id'] = $new_id;
            $_SESSION['nom']     = $nom;
            $_SESSION['prenom']  = $prenom;
            $_SESSION['email']   = $email;
            $_SESSION['role']    = $role;
            $_SESSION['avatar']  = 'default.png';

            flashMessage('succes', 'Compte créé avec succès ! Bienvenue, ' . $prenom . ' !');
            redirect(SITE_URL . "/$role/dashboard.php");
        }
    }
}

$db = getDB();
$villes = $db->query("SELECT * FROM villes ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | PlanèteImmo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="auth-page">
    <!-- Côté gauche -->
    <div class="auth-left">
        <div class="auth-left-bg" style="background-image:url('https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=900&q=80');background-size:cover"></div>
        <div class="auth-left-overlay"></div>
        <div class="auth-left-logo">🏡 Planète<span>Immo</span></div>
        <div class="auth-left-content">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:32px">
                <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.1);border-radius:14px;padding:20px;text-align:center">
                    <div style="font-size:28px;color:var(--gold);font-family:'Playfair Display',serif;font-weight:700">500+</div>
                    <div style="font-size:12px;color:rgba(255,255,255,0.6);margin-top:4px">Annonces actives</div>
                </div>
                <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.1);border-radius:14px;padding:20px;text-align:center">
                    <div style="font-size:28px;color:var(--gold);font-family:'Playfair Display',serif;font-weight:700">15+</div>
                    <div style="font-size:12px;color:rgba(255,255,255,0.6);margin-top:4px">Villes couvertes</div>
                </div>
            </div>
            <h2 class="auth-left-title">Rejoignez la<br>communauté</h2>
            <p class="auth-left-text">Créez votre compte gratuitement et trouvez votre prochain logement au Bénin en quelques clics.</p>
        </div>
    </div>

    <!-- Formulaire inscription -->
    <div class="auth-right" style="max-width:560px">
        <a href="<?= SITE_URL ?>/index.php" style="display:flex;align-items:center;gap:8px;color:var(--text-light);font-size:14px;margin-bottom:32px">
            <i class="fas fa-arrow-left"></i> Retour à l'accueil
        </a>

        <h1 class="auth-title">Créer un compte</h1>
        <p class="auth-sub">Inscription gratuite et rapide</p>

        <?php if ($erreur): ?>
        <div style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--red);padding:14px 16px;border-radius:10px;margin-bottom:24px;display:flex;gap:10px;align-items:center;color:#991b1b">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erreur) ?>
        </div>
        <?php endif; ?>

        <!-- Sélection du rôle -->
        <div style="margin-bottom:24px">
            <label style="display:block;font-size:14px;font-weight:600;color:var(--text);margin-bottom:10px">Je suis :</label>
            <div class="role-select">
                <label class="role-option <?= $role_pre === 'locataire' ? 'selected' : '' ?>">
                    <input type="radio" name="role" value="locataire" form="register-form" <?= $role_pre === 'locataire' ? 'checked' : '' ?>>
                    <i class="fas fa-user"></i>
                    <span>Locataire</span>
                </label>
                <label class="role-option <?= $role_pre === 'agent' ? 'selected' : '' ?>">
                    <input type="radio" name="role" value="agent" form="register-form" <?= $role_pre === 'agent' ? 'checked' : '' ?>>
                    <i class="fas fa-user-tie"></i>
                    <span>Agent immobilier</span>
                </label>
            </div>
        </div>

        <form method="POST" action="" id="register-form">
            <input type="hidden" name="role" id="hidden_role" value="<?= $role_pre ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" placeholder="Votre nom" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" placeholder="Votre prénom" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Adresse Email *</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="votre@email.com" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="telephone">Téléphone *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>" placeholder="+229 97 00 00 00" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="ville">Ville</label>
                    <div class="input-wrapper">
                        <i class="fas fa-map-marker-alt"></i>
                        <select id="ville" name="ville">
                            <option value="">Choisir une ville</option>
                            <?php foreach ($villes as $v): ?>
                            <option value="<?= htmlspecialchars($v['nom']) ?>" <?= ($_POST['ville'] ?? '') === $v['nom'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v['nom']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Min. 6 caractères" required>
                        <button type="button" class="password-toggle"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="mot_de_passe2">Confirmer *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="mot_de_passe2" name="mot_de_passe2" placeholder="Répéter le mot de passe" required>
                    </div>
                </div>
            </div>

            <div style="margin-bottom:20px">
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:13px;color:var(--text-light)">
                    <input type="checkbox" name="cgu" required style="margin-top:2px;accent-color:var(--gold)">
                    J'accepte les <a href="#" style="color:var(--gold)">Conditions Générales d'Utilisation</a> et la <a href="#" style="color:var(--gold)">Politique de Confidentialité</a> de PlanèteImmo.
                </label>
            </div>

            <button type="submit" class="auth-btn">
                <i class="fas fa-user-plus"></i> Créer mon compte gratuitement
            </button>
        </form>

        <div class="auth-link">
            Vous avez déjà un compte ? <a href="<?= SITE_URL ?>/login.php">Se connecter</a>
        </div>
    </div>
</div>

<script src="<?= SITE_URL ?>/js/main.js"></script>
<script>
// Synchroniser les radio buttons du rôle avec le champ caché
document.querySelectorAll('.role-option').forEach(opt => {
    opt.addEventListener('click', function () {
        const radio = this.querySelector('input[type="radio"]');
        if (radio) document.getElementById('hidden_role').value = radio.value;
    });
});
</script>
</body>
</html>
