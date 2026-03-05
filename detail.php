<?php
require_once 'includes/config.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect(SITE_URL . '/maisons.php');

$stmt = $db->prepare("
    SELECT m.*, v.nom as ville_nom, t.libelle as type_libelle,
           u.nom as agent_nom, u.prenom as agent_prenom, u.telephone as agent_tel,
           u.email as agent_email, u.avatar as agent_avatar
    FROM maisons m
    JOIN villes v ON m.ville_id = v.id
    JOIN types_maison t ON m.type_id = t.id
    JOIN users u ON m.agent_id = u.id
    WHERE m.id = ? AND m.statut = 'actif'
");
$stmt->execute([$id]);
$m = $stmt->fetch();

if (!$m) {
    redirect(SITE_URL . '/maisons.php');
}

// Incrémenter les vues
$db->prepare("UPDATE maisons SET vues = vues + 1 WHERE id = ?")->execute([$id]);

// Photos
$photos = $db->prepare("SELECT * FROM photos_maison WHERE maison_id = ? ORDER BY ordre");
$photos->execute([$id]);
$photos = $photos->fetchAll();

// Avis
$avis = $db->prepare("
    SELECT a.*, u.nom, u.prenom, u.avatar FROM avis a
    JOIN users u ON a.user_id = u.id
    WHERE a.maison_id = ? AND a.statut = 'publie'
    ORDER BY a.date_creation DESC
");
$avis->execute([$id]);
$avis = $avis->fetchAll();

// Maisons similaires
$similaires = $db->prepare("
    SELECT m.*, v.nom as ville_nom, t.libelle as type_libelle
    FROM maisons m JOIN villes v ON m.ville_id = v.id JOIN types_maison t ON m.type_id = t.id
    WHERE m.id != ? AND m.statut = 'actif' AND (m.ville_id = ? OR m.type_id = ?)
    LIMIT 3
");
$similaires->execute([$id, $m['ville_id'], $m['type_id']]);
$similaires = $similaires->fetchAll();

$page_title = $m['titre'];

// Traitement de la demande de location
$msg_envoi = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer_demande'])) {
    if (!isLoggedIn()) {
        redirect(SITE_URL . "/login.php");
    }
    $message = sanitize($_POST['message_demande'] ?? '');
    $date_debut = sanitize($_POST['date_debut'] ?? '');
    $duree = (int)($_POST['duree'] ?? 12);
    
    $ins = $db->prepare("INSERT INTO demandes_location (maison_id, locataire_id, agent_id, date_debut, duree_mois, message) VALUES (?, ?, ?, ?, ?, ?)");
    $ins->execute([$id, $_SESSION['user_id'], $m['agent_id'], $date_debut, $duree, $message]);
    
    // Notification à l'agent
    $notif = $db->prepare("INSERT INTO notifications (user_id, titre, message, type, lien) VALUES (?, ?, ?, 'info', ?)");
    $notif->execute([$m['agent_id'], 'Nouvelle demande de location', 'Un locataire souhaite louer : ' . $m['titre'], SITE_URL . '/agent/demandes.php']);
    
    $msg_envoi = 'Votre demande a été envoyée avec succès ! L\'agent vous contactera bientôt.';
}
?>
<?php include 'includes/header.php'; ?>

<main>
    <!-- Hero -->
    <div class="detail-hero">
        <?php if ($m['photo_principale']): ?>
        <img src="<?= SITE_URL ?>/uploads/maisons/<?= htmlspecialchars($m['photo_principale']) ?>" 
             alt="<?= htmlspecialchars($m['titre']) ?>" id="mainPhoto" style="transition:opacity 0.2s">
        <?php else: ?>
        <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--dark-2),var(--dark-3));display:flex;align-items:center;justify-content:center">
            <i class="fas fa-home" style="font-size:80px;color:rgba(255,255,255,0.15)"></i>
        </div>
        <?php endif; ?>
        <div class="detail-hero-overlay"></div>
        
        <div class="detail-hero-info">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                <span class="card-badge badge-<?= $m['disponibilite'] ?>" style="position:static">
                    <?= $m['disponibilite'] === 'disponible' ? '✓ Disponible' : '✗ Loué' ?>
                </span>
                <span style="background:rgba(200,151,58,0.2);border:1px solid rgba(200,151,58,0.3);color:var(--gold);padding:3px 12px;border-radius:50px;font-size:13px">
                    <?= htmlspecialchars($m['type_libelle']) ?>
                </span>
            </div>
            <h1 style="font-size:clamp(22px,4vw,42px);color:white;margin-bottom:8px"><?= htmlspecialchars($m['titre']) ?></h1>
            <div style="display:flex;align-items:center;gap:16px;color:rgba(255,255,255,0.7);font-size:14px">
                <span><i class="fas fa-map-marker-alt" style="color:var(--gold)"></i> <?= htmlspecialchars($m['adresse_complete'] ?? $m['quartier'] . ', ' . $m['ville_nom']) ?></span>
                <span><i class="fas fa-eye" style="color:var(--gold)"></i> <?= $m['vues'] + 1 ?> vues</span>
            </div>

            <?php if (!empty($photos)): ?>
            <div class="gallery-thumbs" style="margin-top:16px">
                <?php foreach ($photos as $i => $p): ?>
                <div class="gallery-thumb <?= $i === 0 ? 'active' : '' ?>" 
                     data-src="<?= SITE_URL ?>/uploads/maisons/<?= htmlspecialchars($p['chemin']) ?>">
                    <img src="<?= SITE_URL ?>/uploads/maisons/<?= htmlspecialchars($p['chemin']) ?>" 
                         alt="<?= htmlspecialchars($p['legende'] ?? '') ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="detail-content">
        <!-- Colonne gauche -->
        <div>
            <?php if ($msg_envoi): ?>
            <div style="background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--green);padding:16px;border-radius:12px;margin-bottom:24px;color:#166534;display:flex;gap:10px;align-items:center">
                <i class="fas fa-check-circle fa-lg"></i> <?= htmlspecialchars($msg_envoi) ?>
            </div>
            <?php endif; ?>

            <!-- Description -->
            <div class="detail-section">
                <h2 class="detail-section-title">Description</h2>
                <p style="color:var(--text);line-height:1.8;font-size:16px"><?= nl2br(htmlspecialchars($m['description'])) ?></p>
            </div>

            <!-- Caractéristiques -->
            <div class="detail-section">
                <h2 class="detail-section-title">Caractéristiques</h2>
                <div class="features-grid">
                    <?php if ($m['nb_chambres']): ?>
                    <div class="feature-item"><i class="fas fa-bed"></i> <?= $m['nb_chambres'] ?> Chambre(s)</div>
                    <?php endif; ?>
                    <div class="feature-item"><i class="fas fa-bath"></i> <?= $m['nb_salles_bain'] ?> Salle(s) de bain</div>
                    <div class="feature-item"><i class="fas fa-toilet"></i> <?= $m['nb_toilettes'] ?> Toilette(s)</div>
                    <div class="feature-item"><i class="fas fa-couch"></i> <?= $m['nb_salons'] ?> Salon(s)</div>
                    <?php if ($m['surface']): ?>
                    <div class="feature-item"><i class="fas fa-expand-arrows-alt"></i> Surface : <?= $m['surface'] ?> m²</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Équipements -->
            <div class="detail-section">
                <h2 class="detail-section-title">Équipements & Services</h2>
                <div class="amenities-grid">
                    <div class="feature-item <?= !$m['climatisation'] ? 'unavailable' : '' ?>">
                        <i class="fas fa-snowflake"></i> Climatisation
                    </div>
                    <div class="feature-item <?= !$m['eau_courante'] ? 'unavailable' : '' ?>">
                        <i class="fas fa-tint"></i> Eau courante
                    </div>
                    <div class="feature-item <?= !$m['electricite'] ? 'unavailable' : '' ?>">
                        <i class="fas fa-bolt"></i> Électricité
                    </div>
                    <div class="feature-item <?= !$m['gardien'] ? 'unavailable' : '' ?>">
                        <i class="fas fa-shield-alt"></i> Gardien 24h
                    </div>
                    <div class="feature-item <?= !$m['garage'] ? 'unavailable' : '' ?>">
                        <i class="fas fa-car"></i> Garage
                    </div>
                    <div class="feature-item <?= !$m['piscine'] ? 'unavailable' : '' ?>">
                        <i class="fas fa-swimming-pool"></i> Piscine
                    </div>
                    <div class="feature-item <?= !$m['meublee'] ? 'unavailable' : '' ?>">
                        <i class="fas fa-couch"></i> Meublée
                    </div>
                    <div class="feature-item <?= !$m['balcon'] ? 'unavailable' : '' ?>">
                        <i class="fas fa-building"></i> Balcon
                    </div>
                    <div class="feature-item <?= !$m['cuisine_equipee'] ? 'unavailable' : '' ?>">
                        <i class="fas fa-utensils"></i> Cuisine équipée
                    </div>
                    <div class="feature-item <?= !$m['connexion_internet'] ? 'unavailable' : '' ?>">
                        <i class="fas fa-wifi"></i> Internet inclus
                    </div>
                </div>
            </div>

            <!-- Avis -->
            <div class="detail-section">
                <h2 class="detail-section-title">Avis (<?= count($avis) ?>)</h2>
                <?php if (empty($avis)): ?>
                <p style="color:var(--text-muted);font-style:italic">Aucun avis pour cette propriété pour le moment.</p>
                <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:16px">
                    <?php foreach ($avis as $av): ?>
                    <div style="background:var(--cream);border-radius:14px;padding:20px;border:1px solid var(--border)">
                        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
                            <div style="display:flex;gap:10px;align-items:center">
                                <div style="width:36px;height:36px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:14px">
                                    <?= strtoupper(substr($av['prenom'],0,1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($av['prenom'] . ' ' . substr($av['nom'],0,1)) ?>.</div>
                                    <div style="font-size:12px;color:var(--text-muted)"><?= timeAgo($av['date_creation']) ?></div>
                                </div>
                            </div>
                            <div style="color:var(--gold)"><?= str_repeat('★', $av['note']) ?><?= str_repeat('☆', 5 - $av['note']) ?></div>
                        </div>
                        <p style="font-size:14px;color:var(--text);line-height:1.6"><?= htmlspecialchars($av['commentaire']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Maisons similaires -->
            <?php if (!empty($similaires)): ?>
            <div class="detail-section">
                <h2 class="detail-section-title">Propriétés similaires</h2>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:20px">
                    <?php foreach ($similaires as $s): ?>
                    <a href="<?= SITE_URL ?>/detail.php?id=<?= $s['id'] ?>" style="background:var(--white);border-radius:14px;overflow:hidden;box-shadow:var(--shadow-sm);transition:var(--transition);display:block" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
                        <div style="height:140px;background:var(--dark-3);display:flex;align-items:center;justify-content:center">
                            <?php if ($s['photo_principale']): ?>
                            <img src="<?= SITE_URL ?>/uploads/maisons/<?= $s['photo_principale'] ?>" alt="" style="width:100%;height:100%;object-fit:cover">
                            <?php else: ?>
                            <i class="fas fa-home" style="font-size:36px;color:rgba(255,255,255,0.2)"></i>
                            <?php endif; ?>
                        </div>
                        <div style="padding:14px">
                            <div style="font-weight:600;font-size:15px;margin-bottom:4px"><?= htmlspecialchars($s['titre']) ?></div>
                            <div style="color:var(--gold);font-weight:700"><?= formatPrix($s['prix_mensuel']) ?>/mois</div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Colonne droite : contact -->
        <div>
            <div class="contact-card">
                <div class="contact-card-header">
                    <div style="font-size:13px;opacity:0.7;margin-bottom:4px">Loyer mensuel</div>
                    <div class="contact-card-prix"><?= formatPrix($m['prix_mensuel']) ?></div>
                    <div style="font-size:13px;opacity:0.7;margin-top:4px">Caution : <?= formatPrix($m['caution']) ?></div>
                    <?php if ($m['date_disponibilite']): ?>
                    <div style="margin-top:12px;background:rgba(34,197,94,0.15);border-radius:8px;padding:6px 12px;font-size:13px;color:var(--green)">
                        <i class="fas fa-calendar-check"></i> Disponible le <?= date('d/m/Y', strtotime($m['date_disponibilite'])) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="contact-card-body">
                    <!-- Agent -->
                    <div class="agent-mini">
                        <img src="<?= SITE_URL ?>/uploads/avatars/<?= $m['agent_avatar'] ?? 'default.png' ?>" 
                             alt="Agent" onerror="this.src='<?= SITE_URL ?>/uploads/avatars/default.png'">
                        <div>
                            <div class="agent-mini-name"><?= htmlspecialchars($m['agent_prenom'] . ' ' . $m['agent_nom']) ?></div>
                            <div class="agent-mini-title">Agent Immobilier</div>
                        </div>
                    </div>

                    <!-- Boutons contact direct -->
                    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px">
                        <a href="tel:<?= $m['agent_tel'] ?>" class="btn btn-dark" style="justify-content:center;border-radius:10px">
                            <i class="fas fa-phone"></i> <?= htmlspecialchars($m['agent_tel']) ?>
                        </a>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $m['agent_tel']) ?>" 
                           target="_blank" class="btn" style="background:#25D366;color:white;justify-content:center;border-radius:10px">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    </div>

                    <div style="text-align:center;color:var(--text-muted);font-size:13px;margin-bottom:16px">— ou —</div>

                    <!-- Formulaire de demande -->
                    <?php if (isLoggedIn() && isLocataire()): ?>
                    <form method="POST" action="">
                        <div style="margin-bottom:12px">
                            <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block">Date de début souhaitée</label>
                            <input type="date" name="date_debut" class="form-control" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div style="margin-bottom:12px">
                            <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block">Durée (mois)</label>
                            <select name="duree" class="form-control">
                                <option value="6">6 mois</option>
                                <option value="12" selected>12 mois</option>
                                <option value="24">24 mois</option>
                                <option value="36">36 mois</option>
                            </select>
                        </div>
                        <div style="margin-bottom:14px">
                            <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block">Message</label>
                            <textarea name="message_demande" class="form-control" rows="3" placeholder="Présentez-vous brièvement..." style="resize:vertical"></textarea>
                        </div>
                        <button type="submit" name="envoyer_demande" class="btn btn-gold" style="width:100%;justify-content:center;border-radius:10px">
                            <i class="fas fa-paper-plane"></i> Envoyer la demande
                        </button>
                    </form>
                    <?php elseif (!isLoggedIn()): ?>
                    <div style="text-align:center;padding:16px;background:var(--cream);border-radius:12px">
                        <p style="color:var(--text-light);font-size:14px;margin-bottom:12px">Connectez-vous pour envoyer une demande de location</p>
                        <a href="<?= SITE_URL ?>/login.php" class="btn btn-gold btn-sm" style="margin-right:8px">Se connecter</a>
                        <a href="<?= SITE_URL ?>/register.php" class="btn btn-outline btn-sm">S'inscrire</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>const SITE_URL = '<?= SITE_URL ?>';</script>
<?php include 'includes/footer.php'; ?>
