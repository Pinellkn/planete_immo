<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    $role = getRole();
    redirect(SITE_URL . "/$role/dashboard.php");
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';

    if (empty($email) || empty($mdp)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND statut != 'suspendu'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($mdp, $user['mot_de_passe'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['nom']      = $user['nom'];
            $_SESSION['prenom']   = $user['prenom'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['avatar']   = $user['avatar'];

            // Mise à jour dernière connexion
            $db->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id = ?")->execute([$user['id']]);

            flashMessage('succes', 'Bienvenue, ' . $user['prenom'] . ' !');

            // Redirection selon le rôle
            $dest = $user['role'] === 'admin' ? '/admin/dashboard.php' :
                   ($user['role'] === 'agent' ? '/agent/dashboard.php' : '/locataire/dashboard.php');
            redirect(SITE_URL . $dest);
        } else {
            $erreur = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | PlanèteImmo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="auth-page">
    <!-- Côté gauche visuel -->
    <div class="auth-left">
        <div class="auth-left-bg"></div>
        <div class="auth-left-overlay"></div>
        <div class="auth-left-logo">🏡 Planète<span>Immo</span></div>
        <div class="auth-left-content">
            <div style="background:rgba(200,151,58,0.15);border:1px solid rgba(200,151,58,0.3);border-radius:16px;padding:24px;margin-bottom:32px">
                <div style="display:flex;gap:10px;margin-bottom:12px">
                    <div style="color:var(--gold);font-size:20px">★★★★★</div>
                </div>
                <p style="color:rgba(255,255,255,0.8);font-style:italic;margin-bottom:12px">"PlanèteImmo m'a permis de trouver ma villa à Fidjrossè en moins d'une semaine. Service impeccable !"</p>
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:40px;height:40px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:700">M</div>
                    <div>
                        <div style="color:white;font-weight:600;font-size:14px">Mariama K.</div>
                        <div style="color:rgba(255,255,255,0.5);font-size:12px">Locataire à Cotonou</div>
                    </div>
                </div>
            </div>
            <h2 class="auth-left-title">Bienvenue sur<br>PlanèteImmo</h2>
            <p class="auth-left-text">La référence de l'immobilier locatif au Bénin. Accédez à votre espace personnel.</p>
        </div>
    </div>

    <!-- Formulaire connexion -->
    <div class="auth-right">
        <a href="<?= SITE_URL ?>/index.php" style="display:flex;align-items:center;gap:8px;color:var(--text-light);font-size:14px;margin-bottom:40px">
            <i class="fas fa-arrow-left"></i> Retour à l'accueil
        </a>

        <h1 class="auth-title">Connexion</h1>
        <p class="auth-sub">Connectez-vous à votre espace personnel</p>

        <?php if ($erreur): ?>
        <div style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--red);padding:14px 16px;border-radius:10px;margin-bottom:24px;display:flex;gap:10px;align-items:center;color:#991b1b">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($erreur) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Adresse Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="votre@email.com" required>
                </div>
            </div>

            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" 
                           placeholder="Votre mot de passe" required>
                    <button type="button" class="password-toggle">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:var(--text-light)">
                    <input type="checkbox" name="remember" style="accent-color:var(--gold)">
                    Se souvenir de moi
                </label>
                <a href="#" style="color:var(--gold);font-size:14px;font-weight:600">Mot de passe oublié ?</a>
            </div>

            <button type="submit" class="auth-btn">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>

        <!-- Identifiants de démo -->
        <div style="margin-top:28px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:16px">
            <p style="font-weight:700;color:#92400e;margin-bottom:10px;font-size:13px"><i class="fas fa-info-circle"></i> Comptes de démonstration</p>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:12px;color:#78350f">
                <div><strong>Admin :</strong> admin@planeteimmo.bj</div>
                <div><strong>Agent 1 :</strong> agent1@planeteimmo.bj</div>
                <div><strong>Agent 2 :</strong> agent2@planeteimmo.bj</div>
                <div><strong>Locataire :</strong> locataire1@gmail.com</div>
                <div style="margin-top:4px"><strong>Mot de passe pour tous :</strong> password</div>
            </div>
        </div>

        <div class="auth-link">
            Pas encore de compte ? <a href="<?= SITE_URL ?>/register.php">Créer un compte gratuit</a>
        </div>
    </div>
</div>

<script src="<?= SITE_URL ?>/js/main.js"></script>
</body>
</html>
