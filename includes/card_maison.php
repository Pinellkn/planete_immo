<?php
/**
 * Template réutilisable pour une card maison
 * Usage : include avec $m = données maison
 */
function renderCardMaison($m, $site_url, $show_fav = true) {
    $amenities = [];
    if ($m['nb_chambres'] > 0)   $amenities[] = ['fas fa-bed',        $m['nb_chambres'] . ' Chambre' . ($m['nb_chambres'] > 1 ? 's' : '')];
    if ($m['nb_salons'] > 0)     $amenities[] = ['fas fa-couch',      $m['nb_salons'] . ' Salon' . ($m['nb_salons'] > 1 ? 's' : '')];
    if ($m['nb_salles_bain'] > 0) $amenities[] = ['fas fa-bath',      $m['nb_salles_bain'] . ' SDB'];
    if ($m['nb_toilettes'] > 0)  $amenities[] = ['fas fa-toilet',     $m['nb_toilettes'] . ' WC'];
    if ($m['surface'])            $amenities[] = ['fas fa-expand-arrows-alt', $m['surface'] . ' m²'];
    if ($m['garage'])             $amenities[] = ['fas fa-car',        'Garage'];
    if ($m['piscine'])            $amenities[] = ['fas fa-swimming-pool', 'Piscine'];
    if ($m['meublee'])            $amenities[] = ['fas fa-couch',      'Meublée'];
    if ($m['climatisation'])      $amenities[] = ['fas fa-snowflake',  'Climatisé'];
    if ($m['gardien'])            $amenities[] = ['fas fa-shield-alt', 'Gardien'];
    if ($m['connexion_internet']) $amenities[] = ['fas fa-wifi',       'Internet'];
    if ($m['balcon'])             $amenities[] = ['fas fa-building',   'Balcon'];
    
    ob_start();
    ?>
    <div class="maison-card">
        <div class="card-image">
            <?php if (!empty($m['photo_principale'])): ?>
            <img src="<?= $site_url ?>/uploads/maisons/<?= htmlspecialchars($m['photo_principale']) ?>"
                 alt="<?= htmlspecialchars($m['titre']) ?>"
                 loading="lazy"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="card-image-placeholder" style="display:none">
                <i class="fas fa-home"></i><span>Photo non disponible</span>
            </div>
            <?php else: ?>
            <div class="card-image-placeholder">
                <i class="fas fa-home"></i><span>Photo bientôt disponible</span>
            </div>
            <?php endif; ?>
            
            <span class="card-badge badge-<?= $m['disponibilite'] ?>">
                <?= $m['disponibilite'] === 'disponible' ? '✓ Disponible' : '✗ Loué' ?>
            </span>
            
            <?php if ($show_fav): ?>
            <button class="card-fav toggle-fav" data-id="<?= $m['id'] ?>" title="Ajouter aux favoris">
                <i class="far fa-heart"></i>
            </button>
            <?php endif; ?>
            
            <!-- Type badge -->
            <?php if (!empty($m['type_libelle'])): ?>
            <div style="position:absolute;bottom:10px;left:10px;background:rgba(15,20,25,0.75);backdrop-filter:blur(8px);color:var(--gold);padding:4px 12px;border-radius:50px;font-size:11px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase">
                <?= htmlspecialchars($m['type_libelle']) ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="card-body">
            <h3 class="card-title"><?= htmlspecialchars($m['titre']) ?></h3>
            
            <div class="card-location">
                <i class="fas fa-map-marker-alt" style="color:var(--gold)"></i>
                <?= htmlspecialchars($m['quartier'] ?? '') ?><?= $m['quartier'] ? ', ' : '' ?><?= htmlspecialchars($m['ville_nom'] ?? '') ?>
            </div>
            
            <!-- Stats rapides (chambres, salon, etc.) -->
            <div class="card-stats">
                <?php if ($m['nb_chambres'] > 0): ?>
                <div class="card-stat-item">
                    <div class="card-stat-icon"><i class="fas fa-bed"></i></div>
                    <div class="card-stat-value"><?= $m['nb_chambres'] ?></div>
                    <div class="card-stat-label">Chambre<?= $m['nb_chambres'] > 1 ? 's' : '' ?></div>
                </div>
                <?php else: ?>
                <div class="card-stat-item">
                    <div class="card-stat-icon"><i class="fas fa-bed"></i></div>
                    <div class="card-stat-value">—</div>
                    <div class="card-stat-label">Studio</div>
                </div>
                <?php endif; ?>
                
                <div class="card-stat-item">
                    <div class="card-stat-icon"><i class="fas fa-couch"></i></div>
                    <div class="card-stat-value"><?= $m['nb_salons'] ?: '1' ?></div>
                    <div class="card-stat-label">Salon<?= ($m['nb_salons'] ?? 1) > 1 ? 's' : '' ?></div>
                </div>
                
                <div class="card-stat-item">
                    <div class="card-stat-icon"><i class="fas fa-bath"></i></div>
                    <div class="card-stat-value"><?= $m['nb_salles_bain'] ?></div>
                    <div class="card-stat-label">SDB</div>
                </div>
                
                <div class="card-stat-item">
                    <div class="card-stat-icon"><i class="fas fa-toilet"></i></div>
                    <div class="card-stat-value"><?= $m['nb_toilettes'] ?></div>
                    <div class="card-stat-label">WC</div>
                </div>
                
                <?php if ($m['surface']): ?>
                <div class="card-stat-item">
                    <div class="card-stat-icon"><i class="fas fa-expand-arrows-alt"></i></div>
                    <div class="card-stat-value"><?= $m['surface'] ?></div>
                    <div class="card-stat-label">m²</div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Équipements (entrée couverte, piscine, etc.) -->
            <?php if (!empty($amenities)): ?>
            <div class="maison-amenities">
                <?php foreach (array_slice($amenities, 5, 4) as $am): ?>
                <span class="amenity-tag">
                    <i class="<?= $am[0] ?>"></i> <?= $am[1] ?>
                </span>
                <?php endforeach; ?>
                <?php
                $extras = [];
                if ($m['meublee'])            $extras[] = '🛋️ Meublée';
                if ($m['piscine'])            $extras[] = '🏊 Piscine';
                if ($m['gardien'])            $extras[] = '🛡️ Gardien';
                if ($m['climatisation'])      $extras[] = '❄️ Clim';
                if ($m['garage'])             $extras[] = '🚗 Garage';
                if ($m['connexion_internet']) $extras[] = '📶 WiFi';
                if ($m['balcon'])             $extras[] = '🏢 Balcon';
                foreach (array_slice($extras, 0, 3) as $ex):
                ?>
                <span class="amenity-tag"><?= $ex ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="card-footer" style="margin-top:12px">
                <div>
                    <div class="card-prix">
                        <?= number_format($m['prix_mensuel'], 0, ',', ' ') ?> <span style="font-size:11px;color:var(--text-muted);font-family:'DM Sans',sans-serif">FCFA/mois</span>
                    </div>
                    <?php if (!empty($m['caution']) && $m['caution'] > 0): ?>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
                        Caution : <?= number_format($m['caution'], 0, ',', ' ') ?> FCFA
                    </div>
                    <?php endif; ?>
                </div>
                <a href="<?= $site_url ?>/detail.php?id=<?= $m['id'] ?>" class="card-btn">
                    Voir détail
                </a>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
