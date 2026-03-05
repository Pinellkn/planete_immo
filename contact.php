<?php
require_once 'includes/config.php';
$page_title = 'Nous Contacter';
$succes = $erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = sanitize($_POST['nom'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $sujet   = sanitize($_POST['sujet'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    if (!$nom || !$email || !$message) {
        $erreur = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        $succes = 'Votre message a bien été envoyé ! Nous vous répondrons sous 24h.';
    }
}
include 'includes/header.php';
?>
<main style="padding-top:76px">
    <!-- Hero contact -->
    <div style="background:linear-gradient(135deg,var(--dark),var(--dark-2));padding:60px 0">
        <div class="container" style="text-align:center">
            <span class="section-badge" style="border-color:rgba(200,151,58,0.3)"><i class="fas fa-envelope"></i> Contact</span>
            <h1 style="color:var(--white);font-size:clamp(28px,5vw,52px);margin:12px 0">Contactez-<span style="color:var(--gold)">nous</span></h1>
            <p style="color:rgba(255,255,255,0.6);max-width:500px;margin:0 auto">Notre équipe est disponible du lundi au samedi de 8h à 18h pour répondre à toutes vos questions.</p>
        </div>
    </div>

    <div class="container" style="padding:60px 24px">
        <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:48px;align-items:start">
            <!-- Infos de contact -->
            <div>
                <h2 style="font-family:'Playfair Display',serif;font-size:28px;margin-bottom:8px">Parlons-nous</h2>
                <p style="color:var(--text-light);margin-bottom:32px">Nous sommes là pour vous aider à trouver votre logement idéal au Bénin.</p>

                <div style="display:flex;flex-direction:column;gap:20px">
                    <?php foreach ([
                        ['fas fa-map-marker-alt','Adresse','Avenue Jean-Paul II, Cotonou, Bénin'],
                        ['fas fa-phone','Téléphone','+229 21 30 00 00'],
                        ['fab fa-whatsapp','WhatsApp','+229 97 00 00 00'],
                        ['fas fa-envelope','Email','contact@planeteimmo.bj'],
                        ['fas fa-clock','Horaires','Lun - Sam : 8h00 - 18h00'],
                    ] as [$icon, $label, $val]): ?>
                    <div style="display:flex;gap:16px;align-items:flex-start">
                        <div style="width:48px;height:48px;background:rgba(200,151,58,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="<?= $icon ?>" style="color:var(--gold);font-size:18px"></i>
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px"><?= $label ?></div>
                            <div style="font-size:15px;color:var(--dark);font-weight:500"><?= $val ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Réseaux sociaux -->
                <div style="margin-top:32px;padding-top:32px;border-top:1px solid var(--border)">
                    <p style="font-size:13px;font-weight:600;color:var(--text-muted);margin-bottom:14px;text-transform:uppercase">Suivez-nous</p>
                    <div style="display:flex;gap:12px">
                        <?php foreach ([['fab fa-facebook-f','#1877F2'],['fab fa-instagram','#E4405F'],['fab fa-whatsapp','#25D366'],['fab fa-twitter','#1DA1F2']] as [$ic,$col]): ?>
                        <a href="#" style="width:42px;height:42px;border-radius:50%;background:<?= $col ?>22;border:1.5px solid <?= $col ?>44;display:flex;align-items:center;justify-content:center;color:<?= $col ?>;transition:all 0.2s" onmouseover="this.style.background='<?= $col ?>'" onmouseout="this.style.background='<?= $col ?>22'">
                            <i class="<?= $ic ?>"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Formulaire -->
            <div style="background:var(--white);border-radius:20px;padding:40px;box-shadow:var(--shadow);border:1px solid var(--border)">
                <h3 style="font-family:'Playfair Display',serif;font-size:24px;margin-bottom:6px">Envoyez-nous un message</h3>
                <p style="color:var(--text-light);font-size:14px;margin-bottom:28px">Nous vous répondrons dans les plus brefs délais</p>

                <?php if ($succes): ?>
                <div style="background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--green);padding:16px;border-radius:12px;margin-bottom:24px;color:#166534;display:flex;gap:10px">
                    <i class="fas fa-check-circle fa-lg"></i> <?= $succes ?>
                </div>
                <?php elseif ($erreur): ?>
                <div style="background:#fef2f2;border-left:4px solid var(--red);padding:16px;border-radius:12px;margin-bottom:24px;color:#991b1b;display:flex;gap:10px">
                    <i class="fas fa-exclamation-circle"></i> <?= $erreur ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                        <div class="form-group">
                            <label>Votre nom *</label>
                            <div class="input-wrapper"><i class="fas fa-user"></i>
                                <input type="text" name="nom" placeholder="Jean Dupont" value="<?= htmlspecialchars($_POST['nom'] ?? (isLoggedIn() ? $_SESSION['prenom'].' '.$_SESSION['nom'] : '')) ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <div class="input-wrapper"><i class="fas fa-envelope"></i>
                                <input type="email" name="email" placeholder="votre@email.com" value="<?= htmlspecialchars($_POST['email'] ?? (isLoggedIn() ? $_SESSION['email'] : '')) ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:16px">
                        <label>Sujet</label>
                        <div class="input-wrapper"><i class="fas fa-tag"></i>
                            <select name="sujet">
                                <option>Demande de renseignements</option>
                                <option>Problème avec une annonce</option>
                                <option>Devenir agent</option>
                                <option>Signaler un problème</option>
                                <option>Autre</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:24px">
                        <label>Message *</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Décrivez votre demande en détail..." required style="resize:vertical"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="auth-btn" style="border-radius:12px">
                        <i class="fas fa-paper-plane"></i> Envoyer le message
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
