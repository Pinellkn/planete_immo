<?php
require_once 'includes/config.php';
$page_title = 'Toutes les annonces';
$db = getDB();

// Filtres
$recherche = sanitize($_GET['recherche'] ?? '');
$ville_id  = (int)($_GET['ville'] ?? 0);
$type_id   = (int)($_GET['type'] ?? 0);
$prix_max  = (int)($_GET['prix_max'] ?? 0);
$prix_min  = (int)($_GET['prix_min'] ?? 0);
$chambres  = (int)($_GET['chambres'] ?? 0);
$dispo     = sanitize($_GET['disponibilite'] ?? '');
$tri       = in_array($_GET['tri'] ?? '', ['prix_asc','prix_desc','recent','vues']) ? $_GET['tri'] : 'recent';

// Pagination
$per_page = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

// Construction de la requête
$where = ["m.statut = 'actif'"];
$params = [];

if ($recherche) {
    $where[] = "(m.titre LIKE ? OR m.description LIKE ? OR m.quartier LIKE ?)";
    $params = array_merge($params, ["%$recherche%", "%$recherche%", "%$recherche%"]);
}
if ($ville_id) { $where[] = "m.ville_id = ?"; $params[] = $ville_id; }
if ($type_id)  { $where[] = "m.type_id = ?";  $params[] = $type_id; }
if ($prix_max) { $where[] = "m.prix_mensuel <= ?"; $params[] = $prix_max; }
if ($prix_min) { $where[] = "m.prix_mensuel >= ?"; $params[] = $prix_min; }
if ($chambres) { $where[] = "m.nb_chambres >= ?";  $params[] = $chambres; }
if ($dispo)    { $where[] = "m.disponibilite = ?"; $params[] = $dispo; }

$order = match($tri) {
    'prix_asc'  => 'ORDER BY m.prix_mensuel ASC',
    'prix_desc' => 'ORDER BY m.prix_mensuel DESC',
    'vues'      => 'ORDER BY m.vues DESC',
    default     => 'ORDER BY m.date_creation DESC',
};

$whereStr = implode(' AND ', $where);

// Total
$countStmt = $db->prepare("SELECT COUNT(*) FROM maisons m WHERE $whereStr");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Maisons
$sql = "SELECT m.*, v.nom as ville_nom, t.libelle as type_libelle
        FROM maisons m
        JOIN villes v ON m.ville_id = v.id
        JOIN types_maison t ON m.type_id = t.id
        WHERE $whereStr $order LIMIT $per_page OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$maisons = $stmt->fetchAll();

// Données filtres
$villes = $db->query("SELECT * FROM villes ORDER BY nom")->fetchAll();
$types  = $db->query("SELECT * FROM types_maison ORDER BY libelle")->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<main style="padding-top:76px">
    <div style="background:linear-gradient(135deg,var(--dark),var(--dark-2));padding:48px 0;margin-bottom:0">
        <div class="container">
            <h1 style="font-family:'Playfair Display',serif;color:var(--white);font-size:36px;margin-bottom:8px">
                Nos <span style="color:var(--gold)">Annonces</span>
            </h1>
            <p style="color:rgba(255,255,255,0.6)"><?= $total ?> bien<?= $total > 1 ? 's' : '' ?> trouvé<?= $total > 1 ? 's' : '' ?><?= $recherche ? " pour \"" . htmlspecialchars($recherche) . "\"" : '' ?></p>
        </div>
    </div>

    <div class="container" style="padding:32px 24px">
        <!-- Filtres -->
        <form method="GET" action="" class="search-filters">
            <div class="filters-row">
                <div class="filter-group" style="flex:2;min-width:220px">
                    <label>Recherche</label>
                    <input type="text" name="recherche" value="<?= htmlspecialchars($recherche) ?>" placeholder="Titre, quartier, description...">
                </div>
                <div class="filter-group">
                    <label>Ville</label>
                    <select name="ville">
                        <option value="">Toutes</option>
                        <?php foreach ($villes as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= $ville_id == $v['id'] ? 'selected' : '' ?>><?= htmlspecialchars($v['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Type</label>
                    <select name="type">
                        <option value="">Tous</option>
                        <?php foreach ($types as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $type_id == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Budget max (FCFA)</label>
                    <select name="prix_max">
                        <option value="">Illimité</option>
                        <?php foreach ([75000,150000,250000,400000,600000,1000000] as $p): ?>
                        <option value="<?= $p ?>" <?= $prix_max == $p ? 'selected' : '' ?>><?= number_format($p,0,',',' ') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Chambres min.</label>
                    <select name="chambres">
                        <option value="">Toutes</option>
                        <?php foreach ([1,2,3,4,5] as $c): ?>
                        <option value="<?= $c ?>" <?= $chambres == $c ? 'selected' : '' ?>><?= $c ?>+</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Disponibilité</label>
                    <select name="disponibilite">
                        <option value="">Toutes</option>
                        <option value="disponible" <?= $dispo === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="louee" <?= $dispo === 'louee' ? 'selected' : '' ?>>Louée</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Trier par</label>
                    <select name="tri">
                        <option value="recent" <?= $tri === 'recent' ? 'selected' : '' ?>>Plus récent</option>
                        <option value="prix_asc" <?= $tri === 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                        <option value="prix_desc" <?= $tri === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                        <option value="vues" <?= $tri === 'vues' ? 'selected' : '' ?>>Plus vus</option>
                    </select>
                </div>
                <div class="filter-group" style="min-width:auto">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-gold" style="width:100%;border-radius:10px;justify-content:center">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </div>
            </div>
        </form>

        <!-- Résultats -->
        <?php if (empty($maisons)): ?>
        <div style="text-align:center;padding:80px 24px;background:var(--white);border-radius:20px">
            <i class="fas fa-search" style="font-size:64px;color:var(--text-muted);opacity:0.3;margin-bottom:20px;display:block"></i>
            <h3 style="color:var(--dark);margin-bottom:10px">Aucun résultat trouvé</h3>
            <p style="color:var(--text-light)">Modifiez vos critères de recherche pour trouver des annonces.</p>
            <a href="maisons.php" class="btn btn-gold" style="margin-top:20px">Voir toutes les annonces</a>
        </div>
        <?php else: ?>
        <div class="maisons-grid">
            <?php foreach ($maisons as $m): ?>
            <div class="maison-card">
                <div class="card-image">
                    <?php if ($m['photo_principale']): ?>
                    <img src="<?= SITE_URL ?>/uploads/maisons/<?= htmlspecialchars($m['photo_principale']) ?>"
                         alt="<?= htmlspecialchars($m['titre']) ?>"
                         onerror="this.parentElement.innerHTML='<div class=\'card-image-placeholder\'><i class=\'fas fa-home\'></i><span>Photo non disponible</span></div>'">
                    <?php else: ?>
                    <div class="card-image-placeholder">
                        <i class="fas fa-home"></i>
                        <span>Photo bientôt disponible</span>
                    </div>
                    <?php endif; ?>
                    <span class="card-badge badge-<?= $m['disponibilite'] ?>">
                        <?= $m['disponibilite'] === 'disponible' ? '✓ Disponible' : '✗ Loué' ?>
                    </span>
                    <button class="card-fav toggle-fav" data-id="<?= $m['id'] ?>" title="Favoris">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="card-type-row">
                        <span class="card-type"><?= htmlspecialchars($m['type_libelle']) ?></span>
                        <?php if ($m['meublee']): ?><span class="card-tag-meuble">🛋️ Meublée</span><?php endif; ?>
                    </div>
                    <h3 class="card-title"><?= htmlspecialchars($m['titre']) ?></h3>
                    <div class="card-location">
                        <i class="fas fa-map-marker-alt" style="color:var(--gold)"></i>
                        <?= htmlspecialchars($m['quartier'] ?? '') ?><?= $m['quartier'] ? ', ' : '' ?><?= htmlspecialchars($m['ville_nom']) ?>
                    </div>

                    <!-- Grille de caractéristiques -->
                    <div class="card-specs">
                        <div class="card-spec">
                            <i class="fas fa-bed"></i>
                            <span><?= $m['nb_chambres'] > 0 ? $m['nb_chambres'] . ' ch.' : 'Studio' ?></span>
                        </div>
                        <div class="card-spec">
                            <i class="fas fa-couch"></i>
                            <span><?= max(1,$m['nb_salons'] ?? 1) ?> salon<?= ($m['nb_salons'] ?? 1) > 1 ? 's' : '' ?></span>
                        </div>
                        <div class="card-spec">
                            <i class="fas fa-bath"></i>
                            <span><?= $m['nb_salles_bain'] ?> sdb</span>
                        </div>
                        <div class="card-spec">
                            <i class="fas fa-toilet"></i>
                            <span><?= $m['nb_toilettes'] ?> wc</span>
                        </div>
                        <?php if ($m['surface']): ?>
                        <div class="card-spec">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span><?= $m['surface'] ?> m²</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Équipements extras -->
                    <?php
                    $extras = [];
                    if ($m['climatisation'])      $extras[] = ['fas fa-snowflake','Climatisé'];
                    if ($m['piscine'])            $extras[] = ['fas fa-swimming-pool','Piscine'];
                    if ($m['gardien'])            $extras[] = ['fas fa-shield-alt','Gardien'];
                    if ($m['garage'])             $extras[] = ['fas fa-car','Garage'];
                    if ($m['connexion_internet']) $extras[] = ['fas fa-wifi','Internet'];
                    if ($m['balcon'])             $extras[] = ['fas fa-building','Balcon'];
                    if ($m['cuisine_equipee'])    $extras[] = ['fas fa-utensils','Cuisine équip.'];
                    if (!empty($extras)): ?>
                    <div class="card-extras">
                        <?php foreach (array_slice($extras, 0, 4) as $ex): ?>
                        <span class="card-extra-tag"><i class="<?= $ex[0] ?>"></i> <?= $ex[1] ?></span>
                        <?php endforeach; ?>
                        <?php if (count($extras) > 4): ?>
                        <span class="card-extra-tag">+<?= count($extras)-4 ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="card-footer">
                        <div>
                            <div class="card-prix"><?= formatPrix($m['prix_mensuel']) ?><span>/mois</span></div>
                            <?php if ($m['caution']): ?>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Caution: <?= formatPrix($m['caution']) ?></div>
                            <?php endif; ?>
                        </div>
                        <a href="<?= SITE_URL ?>/detail.php?id=<?= $m['id'] ?>" class="card-btn">Voir plus</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="page-btn">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
               class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="page-btn">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<script>const SITE_URL = '<?= SITE_URL ?>';</script>
<?php include 'includes/footer.php'; ?>
