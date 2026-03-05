-- Mise à jour des photos pour les maisons de démo
UPDATE maisons SET photo_principale = 'villa_fidj.jpg' WHERE id = 1;
UPDATE maisons SET photo_principale = 'appart_hv.jpg'  WHERE id = 2;
UPDATE maisons SET photo_principale = 'villa_luxe.jpg' WHERE id = 3;
UPDATE maisons SET photo_principale = 'studio.jpg'     WHERE id = 4;
UPDATE maisons SET photo_principale = 'maison_pn.jpg'  WHERE id = 5;

-- S'assurer que les maisons ont salon et toilettes renseignés
UPDATE maisons SET nb_salons = 1, nb_toilettes = 1 WHERE nb_salons = 0 OR nb_salons IS NULL;
UPDATE maisons SET nb_salons = 2 WHERE nb_chambres >= 4;
UPDATE maisons SET nb_salons = 1 WHERE nb_salons IS NULL;

-- S'assurer que le statut est actif
UPDATE maisons SET statut = 'actif', disponibilite = 'disponible';
