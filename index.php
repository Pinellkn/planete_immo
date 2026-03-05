<?php
require_once 'includes/config.php';
require_once 'includes/card_maison.php';
$page_title = 'Accueil - Trouvez votre maison au Bénin';
$db = getDB();

$maisons = $db->query("
    SELECT m.*, v.nom as ville_nom, t.libelle as type_libelle,
           u.nom as agent_nom, u.prenom as agent_prenom
    FROM maisons m
    JOIN villes v ON m.ville_id = v.id
    JOIN types_maison t ON m.type_id = t.id
    JOIN users u ON m.agent_id = u.id
    WHERE m.statut = 'actif' AND m.disponibilite = 'disponible'
    ORDER BY m.date_creation DESC LIMIT 6
")->fetchAll();

$stats = $db->query("SELECT 
    (SELECT COUNT(*) FROM maisons WHERE statut='actif') as total_maisons,
    (SELECT COUNT(*) FROM users WHERE role='locataire') as total_locataires,
    (SELECT COUNT(*) FROM users WHERE role='agent') as total_agents,
    (SELECT COUNT(*) FROM villes) as total_villes
")->fetch();

$villes = $db->query("SELECT * FROM villes ORDER BY nom")->fetchAll();
$types  = $db->query("SELECT * FROM types_maison ORDER BY libelle")->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<main>

<!-- HERO -->
<section class="hero" id="hero">
    <div class="hero-bg" style="background:url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1920&q=80') center/cover;opacity:0.35;position:absolute;inset:0"></div>
    <div class="hero-overlay"></div>
    <div class="hero-pattern"></div>
    <div class="hero-content">
        <div class="hero-tag"><i class="fas fa-star"></i> N°1 de l'immobilier au Bénin</div>
        <h1 class="hero-title">Trouvez votre<br><span class="highlight">Maison Idéale</span><br>au Bénin</h1>
        <p class="hero-sub">Des centaines d'annonces vérifiées à Cotonou, Porto-Novo, Parakou et dans toutes les villes du Bénin.</p>
        <form class="hero-search" action="maisons.php" method="GET">
            <i class="fas fa-search" style="color:#9CA3AF;padding:0 8px"></i>
            <input type="text" name="recherche" class="hero-search-field" placeholder="Quartier, ville, type...">
            <select name="ville" class="hero-search-select">
                <option value="">Toutes les villes</option>
                <?php foreach ($villes as $v): ?>
                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nom']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="prix_max" class="hero-search-select">
                <option value="">Budget max</option>
                <option value="75000">75 000 FCFA</option>
                <option value="150000">150 000 FCFA</option>
                <option value="250000">250 000 FCFA</option>
                <option value="500000">500 000 FCFA</option>
                <option value="1000000">1 000 000 FCFA</option>
            </select>
            <button type="submit" class="hero-search-btn"><i class="fas fa-search"></i> Rechercher</button>
        </form>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;">
            <?php foreach (array_slice($types, 0, 5) as $t): ?>
            <a href="maisons.php?type=<?= $t['id'] ?>" style="background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.85);padding:5px 14px;border-radius:50px;font-size:13px;border:1px solid rgba(255,255,255,0.2);transition:all 0.2s" 
               onmouseover="this.style.background='rgba(200,151,58,0.3)';this.style.borderColor='var(--gold)'"
               onmouseout="this.style.background='rgba(255,255,255,0.1)';this.style.borderColor='rgba(255,255,255,0.2)'">
                <?= htmlspecialchars($t['libelle']) ?>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="hero-stats">
            <div><div class="hero-stat-value counter" data-target="<?= $stats['total_maisons'] ?>">0</div><div class="hero-stat-label">Annonces actives</div></div>
            <div><div class="hero-stat-value counter" data-target="<?= $stats['total_locataires'] ?>">0</div><div class="hero-stat-label">Locataires</div></div>
            <div><div class="hero-stat-value counter" data-target="<?= $stats['total_agents'] ?>">0</div><div class="hero-stat-label">Agents certifiés</div></div>
            <div><div class="hero-stat-value counter" data-target="<?= $stats['total_villes'] ?>">0</div><div class="hero-stat-label">Villes couvertes</div></div>
        </div>
    </div>
</section>

<!-- ANNONCES EN VEDETTE -->
<section style="padding:80px 0;background:var(--cream)">
    <div class="container">
        <div class="section-header" style="margin-bottom:48px">
            <span class="section-badge"><i class="fas fa-fire"></i> En vedette</span>
            <h2 class="section-title">Dernières <span class="text-gold">Annonces</span></h2>
            <p class="section-sub">Les meilleures propriétés disponibles, sélectionnées par nos agents certifiés.</p>
        </div>
        <?php if (empty($maisons)): ?>
        <div style="text-align:center;padding:60px;background:var(--white);border-radius:20px;color:var(--text-muted)">
            <i class="fas fa-home" style="font-size:60px;margin-bottom:16px;color:var(--gold);opacity:0.3;display:block"></i>
            <p style="font-size:18px">Aucune annonce disponible pour le moment.</p>
        </div>
        <?php else: ?>
        <div class="maisons-grid">
            <?php foreach ($maisons as $m): echo renderCardMaison($m, SITE_URL); endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:48px">
            <a href="<?= SITE_URL ?>/maisons.php" class="btn btn-dark btn-lg">
                <i class="fas fa-th-list"></i> Voir toutes les annonces
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- POURQUOI NOUS -->
<section class="features-section">
    <div class="container">
        <div class="section-header centered">
            <span class="section-badge" style="background:rgba(200,151,58,0.1);border-color:rgba(200,151,58,0.2)"><i class="fas fa-shield-alt"></i> Pourquoi nous</span>
            <h2 class="section-title" style="color:var(--white)">La plateforme <span style="color:var(--gold)">de confiance</span><br>pour l'immobilier</h2>
            <p class="section-sub" style="color:rgba(255,255,255,0.55);margin:0 auto">Nous mettons tout en œuvre pour vous offrir la meilleure expérience de location au Bénin.</p>
        </div>
        <div class="features-cards">
            <?php foreach ([
                ['fas fa-check-double','Annonces Vérifiées','Chaque propriété est visitée et vérifiée par nos agents certifiés avant d\'être publiée.'],
                ['fas fa-lock','Transactions Sécurisées','Vos paiements et données personnelles sont protégés. Transactions transparentes.'],
                ['fas fa-headset','Support 7j/7','Notre équipe est disponible tous les jours pour vous accompagner.'],
                ['fas fa-map-marked-alt','Tout le Bénin','Cotonou, Porto-Novo, Parakou, Abomey-Calavi et bien plus encore.'],
            ] as [$ic, $titre, $desc]): ?>
            <div class="feature-card">
                <div class="feature-card-icon"><i class="<?= $ic ?>"></i></div>
                <h3><?= $titre ?></h3>
                <p><?= $desc ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- COMMENT ÇA MARCHE -->
<section style="padding:80px 0;background:var(--cream-2)">
    <div class="container">
        <div class="section-header centered">
            <span class="section-badge"><i class="fas fa-list-ol"></i> Comment ça marche</span>
            <h2 class="section-title">Trouver votre maison en <span class="text-gold">3 étapes</span></h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:32px;margin-top:48px">
            <?php foreach ([
                ['1','Créez votre compte','Inscrivez-vous gratuitement en tant que locataire pour accéder à toutes les annonces.'],
                ['2','Explorez les annonces','Filtrez par ville, budget, nombre de chambres, et bien d\'autres critères.'],
                ['3','Contactez l\'agent','Envoyez une demande de visite ou de location directement à l\'agent.'],
            ] as [$n, $t, $d]): ?>
            <div style="text-align:center;padding:32px 24px;background:var(--white);border-radius:20px;box-shadow:var(--shadow-sm);border:1px solid var(--border)">
                <div style="width:72px;height:72px;background:linear-gradient(135deg,var(--gold),var(--gold-light));border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:28px;color:white;font-family:'Playfair Display',serif;font-weight:700"><?= $n ?></div>
                <h3 style="font-size:20px;margin-bottom:12px"><?= $t ?></h3>
                <p style="color:var(--text-light);font-size:14px;line-height:1.7"><?= $d ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container" style="position:relative;z-index:1">
        <h2 class="cta-title">Vous êtes propriétaire ou agent immobilier ?</h2>
        <p class="cta-sub">Publiez vos annonces gratuitement et rejoignez des milliers d'utilisateurs sur PlanèteImmo</p>
        <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
            <a href="<?= SITE_URL ?>/register.php?role=agent" class="btn btn-white btn-lg"><i class="fas fa-user-tie"></i> Devenir Agent</a>
            <a href="<?= SITE_URL ?>/contact.php" class="btn btn-lg" style="border:2px solid rgba(255,255,255,0.5);color:white;background:transparent"><i class="fas fa-envelope"></i> Nous Contacter</a>
        </div>
    </div>
</section>

</main>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>
<?php include 'includes/footer.php'; ?>
