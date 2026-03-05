-- ============================================================
-- BASE DE DONNÉES : BÉNIN IMMO - Location de Maison au Bénin
-- ============================================================

CREATE DATABASE IF NOT EXISTS benin_immo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE benin_immo;

-- Table des utilisateurs (admin, agents, locataires)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin','agent','locataire') DEFAULT 'locataire',
    avatar VARCHAR(255) DEFAULT 'default.png',
    ville VARCHAR(100) DEFAULT 'Cotonou',
    quartier VARCHAR(100),
    date_naissance DATE,
    piece_identite VARCHAR(255),
    statut ENUM('actif','inactif','suspendu') DEFAULT 'actif',
    token_reset VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL
);

-- Table des villes du Bénin
CREATE TABLE villes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    departement VARCHAR(100) NOT NULL
);

-- Table des quartiers
CREATE TABLE quartiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    ville_id INT,
    FOREIGN KEY (ville_id) REFERENCES villes(id)
);

-- Table des types de maison
CREATE TABLE types_maison (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL,
    description TEXT
);

-- Table des maisons/propriétés
CREATE TABLE maisons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    type_id INT,
    agent_id INT NOT NULL,
    ville_id INT,
    quartier VARCHAR(150),
    adresse_complete TEXT,
    prix_mensuel DECIMAL(10,2) NOT NULL,
    caution DECIMAL(10,2) DEFAULT 0,
    surface DECIMAL(8,2),
    nb_chambres INT DEFAULT 1,
    nb_salles_bain INT DEFAULT 1,
    nb_toilettes INT DEFAULT 1,
    nb_salons INT DEFAULT 1,
    garage TINYINT(1) DEFAULT 0,
    piscine TINYINT(1) DEFAULT 0,
    climatisation TINYINT(1) DEFAULT 0,
    eau_courante TINYINT(1) DEFAULT 1,
    electricite TINYINT(1) DEFAULT 1,
    gardien TINYINT(1) DEFAULT 0,
    meublee TINYINT(1) DEFAULT 0,
    balcon TINYINT(1) DEFAULT 0,
    cuisine_equipee TINYINT(1) DEFAULT 0,
    connexion_internet TINYINT(1) DEFAULT 0,
    disponibilite ENUM('disponible','louee','maintenance','vendue') DEFAULT 'disponible',
    date_disponibilite DATE,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    vues INT DEFAULT 0,
    statut ENUM('actif','inactif','en_attente') DEFAULT 'en_attente',
    photo_principale VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES types_maison(id),
    FOREIGN KEY (agent_id) REFERENCES users(id),
    FOREIGN KEY (ville_id) REFERENCES villes(id)
);

-- Table des photos de maisons
CREATE TABLE photos_maison (
    id INT AUTO_INCREMENT PRIMARY KEY,
    maison_id INT NOT NULL,
    chemin VARCHAR(255) NOT NULL,
    legende VARCHAR(255),
    ordre INT DEFAULT 0,
    FOREIGN KEY (maison_id) REFERENCES maisons(id) ON DELETE CASCADE
);

-- Table des demandes de location
CREATE TABLE demandes_location (
    id INT AUTO_INCREMENT PRIMARY KEY,
    maison_id INT NOT NULL,
    locataire_id INT NOT NULL,
    agent_id INT NOT NULL,
    date_debut DATE,
    duree_mois INT DEFAULT 12,
    message TEXT,
    statut ENUM('en_attente','acceptee','refusee','annulee') DEFAULT 'en_attente',
    date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_reponse TIMESTAMP NULL,
    FOREIGN KEY (maison_id) REFERENCES maisons(id),
    FOREIGN KEY (locataire_id) REFERENCES users(id),
    FOREIGN KEY (agent_id) REFERENCES users(id)
);

-- Table des contrats de location
CREATE TABLE contrats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    demande_id INT NOT NULL,
    maison_id INT NOT NULL,
    locataire_id INT NOT NULL,
    agent_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    loyer_mensuel DECIMAL(10,2) NOT NULL,
    caution_versee DECIMAL(10,2) DEFAULT 0,
    numero_contrat VARCHAR(50) UNIQUE,
    statut ENUM('actif','termine','resilie') DEFAULT 'actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (demande_id) REFERENCES demandes_location(id),
    FOREIGN KEY (maison_id) REFERENCES maisons(id),
    FOREIGN KEY (locataire_id) REFERENCES users(id),
    FOREIGN KEY (agent_id) REFERENCES users(id)
);

-- Table des paiements
CREATE TABLE paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrat_id INT NOT NULL,
    locataire_id INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    mois_concerne VARCHAR(20),
    methode_paiement ENUM('especes','mobile_money','virement','cheque') DEFAULT 'mobile_money',
    reference_paiement VARCHAR(100),
    statut ENUM('en_attente','valide','rejete') DEFAULT 'en_attente',
    date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_validation TIMESTAMP NULL,
    note TEXT,
    FOREIGN KEY (contrat_id) REFERENCES contrats(id),
    FOREIGN KEY (locataire_id) REFERENCES users(id)
);

-- Table des favoris
CREATE TABLE favoris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    maison_id INT NOT NULL,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favori (user_id, maison_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (maison_id) REFERENCES maisons(id) ON DELETE CASCADE
);

-- Table des avis/commentaires
CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    maison_id INT NOT NULL,
    user_id INT NOT NULL,
    note INT CHECK (note BETWEEN 1 AND 5),
    commentaire TEXT,
    statut ENUM('en_attente','publie','rejete') DEFAULT 'en_attente',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (maison_id) REFERENCES maisons(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table des messages/notifications
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expediteur_id INT NOT NULL,
    destinataire_id INT NOT NULL,
    sujet VARCHAR(200),
    contenu TEXT NOT NULL,
    lu TINYINT(1) DEFAULT 0,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id) REFERENCES users(id),
    FOREIGN KEY (destinataire_id) REFERENCES users(id)
);

-- Table des notifications système
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info','succes','alerte','erreur') DEFAULT 'info',
    lu TINYINT(1) DEFAULT 0,
    lien VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des visites programmées
CREATE TABLE visites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    maison_id INT NOT NULL,
    locataire_id INT NOT NULL,
    agent_id INT NOT NULL,
    date_visite DATETIME NOT NULL,
    statut ENUM('programmee','confirmee','effectuee','annulee') DEFAULT 'programmee',
    notes TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (maison_id) REFERENCES maisons(id),
    FOREIGN KEY (locataire_id) REFERENCES users(id),
    FOREIGN KEY (agent_id) REFERENCES users(id)
);

-- Table des paramètres du site
CREATE TABLE parametres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) UNIQUE NOT NULL,
    valeur TEXT,
    description VARCHAR(255)
);

-- ============================================================
-- DONNÉES DE BASE
-- ============================================================

-- Villes du Bénin
INSERT INTO villes (nom, departement) VALUES
('Cotonou', 'Littoral'),
('Porto-Novo', 'Ouémé'),
('Parakou', 'Borgou'),
('Abomey-Calavi', 'Atlantique'),
('Djougou', 'Donga'),
('Bohicon', 'Zou'),
('Kandi', 'Alibori'),
('Lokossa', 'Mono'),
('Ouidah', 'Atlantique'),
('Natitingou', 'Atacora'),
('Abomey', 'Zou'),
('Malanville', 'Alibori'),
('Bassila', 'Donga'),
('Savalou', 'Collines'),
('Tchaourou', 'Borgou');

-- Quartiers de Cotonou
INSERT INTO quartiers (nom, ville_id) VALUES
('Cadjehoun', 1), ('Akpakpa', 1), ('Fidjrossè', 1), ('Haie Vive', 1),
('Agla', 1), ('Gbégamey', 1), ('Zogbo', 1), ('Missèbo', 1),
('Dantokpa', 1), ('Agontikon', 1), ('Gbèdégbé', 1), ('Modjagan', 1),
('Aidjèdo', 1), ('Jonquet', 1), ('Xwlacodji', 1);

-- Types de maison
INSERT INTO types_maison (libelle, description) VALUES
('Studio', 'Appartement d''une seule pièce avec coin cuisine'),
('Appartement T2', 'Appartement avec 1 chambre, salon et cuisine'),
('Appartement T3', 'Appartement avec 2 chambres, salon et cuisine'),
('Villa simple', 'Maison individuelle de standing moyen'),
('Villa duplex', 'Maison sur deux niveaux avec jardin'),
('Villa de luxe', 'Propriété haut de gamme avec piscine et sécurité'),
('Maison de cour', 'Logement dans une cour commune'),
('Chambre meublée', 'Chambre individuelle tout équipé'),
('Bureaux', 'Espace professionnel pour entreprises');

-- Comptes utilisateurs (mots de passe hashés avec bcrypt = "password123")
INSERT INTO users (nom, prenom, email, telephone, mot_de_passe, role, ville, statut) VALUES
('ADMIN', 'Système', 'admin@beninimmo.bj', '+229 97000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Cotonou', 'actif'),
('AGOSSA', 'Koffi', 'agent1@beninimmo.bj', '+229 97000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent', 'Cotonou', 'actif'),
('HOUNSOU', 'Abibatou', 'agent2@beninimmo.bj', '+229 97000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent', 'Porto-Novo', 'actif'),
('DOSSOU', 'Jean-Pierre', 'locataire1@gmail.com', '+229 96000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'locataire', 'Cotonou', 'actif'),
('AKPOVI', 'Mariama', 'locataire2@gmail.com', '+229 96000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'locataire', 'Cotonou', 'actif');

-- Paramètres du site
INSERT INTO parametres (cle, valeur, description) VALUES
('site_nom', 'Bénin Immo', 'Nom du site'),
('site_slogan', 'Votre maison de rêve au Bénin', 'Slogan du site'),
('site_email', 'contact@beninimmo.bj', 'Email de contact'),
('site_telephone', '+229 21 30 00 00', 'Téléphone principal'),
('site_adresse', 'Avenue Jean-Paul II, Cotonou, Bénin', 'Adresse physique'),
('commission_agent', '5', 'Commission agent en %'),
('devise', 'FCFA', 'Devise utilisée'),
('maisons_par_page', '12', 'Nombre de maisons par page'),
('facebook', 'https://facebook.com/beninimmo', 'Page Facebook'),
('whatsapp', '+22997000000', 'Numéro WhatsApp'),
('maintenance', '0', 'Mode maintenance activé');

-- Maisons de démonstration
INSERT INTO maisons (titre, description, type_id, agent_id, ville_id, quartier, adresse_complete, prix_mensuel, caution, surface, nb_chambres, nb_salles_bain, nb_toilettes, nb_salons, climatisation, eau_courante, electricite, connexion_internet, disponibilite, statut, photo_principale) VALUES
('Belle Villa F4 à Fidjrossè', 'Magnifique villa moderne avec 3 chambres, salon spacieux, cuisine équipée et grande cour. Idéale pour famille. Sécurisée 24h/24.', 4, 2, 1, 'Fidjrossè', 'Rue des Cocotiers, Fidjrossè, Cotonou', 250000, 500000, 180, 3, 2, 2, 1, 1, 1, 1, 1, 'disponible', 'actif', 'villa_fidj.jpg'),
('Appartement T3 Moderne Haie Vive', 'Bel appartement au 2ème étage dans une résidence sécurisée. Vue dégagée, parking inclus, quartier calme et résidentiel.', 3, 2, 1, 'Haie Vive', 'Résidence Les Palmiers, Haie Vive, Cotonou', 180000, 360000, 120, 2, 1, 1, 1, 1, 1, 1, 1, 'disponible', 'actif', 'appart_hv.jpg'),
('Villa Duplex de Luxe Agla', 'Somptueuse villa duplex avec piscine, jardin aménagé, 4 chambres climatisées, sécurité 24h. Le summum du confort à Cotonou.', 6, 3, 1, 'Agla', 'Boulevard de la Paix, Agla, Cotonou', 600000, 1200000, 350, 4, 3, 3, 2, 1, 1, 1, 1, 'disponible', 'actif', 'villa_luxe.jpg'),
('Studio Meublé Cadjehoun', 'Studio entièrement meublé et équipé. Idéal pour célibataire ou couple. Proche de toutes commodités, internet inclus.', 1, 2, 1, 'Cadjehoun', 'Rue des Fleurs, Cadjehoun, Cotonou', 75000, 150000, 35, 0, 1, 1, 0, 1, 1, 1, 1, 'disponible', 'actif', 'studio.jpg'),
('Maison F3 Porto-Novo Centre', 'Belle maison familiale au coeur de Porto-Novo. Grande cour, 2 chambres, salon, cuisine moderne. Proche écoles et marchés.', 4, 3, 2, 'Centre-Ville', 'Quartier Centre, Porto-Novo', 120000, 240000, 150, 2, 1, 2, 1, 0, 1, 1, 0, 'disponible', 'actif', 'maison_pn.jpg');

-- Photos des maisons
INSERT INTO photos_maison (maison_id, chemin, legende, ordre) VALUES
(1, 'villa_fidj_1.jpg', 'Facade principale', 1),
(1, 'villa_fidj_2.jpg', 'Salon principal', 2),
(1, 'villa_fidj_3.jpg', 'Chambre maître', 3),
(2, 'appart_hv_1.jpg', 'Vue façade', 1),
(2, 'appart_hv_2.jpg', 'Salon moderne', 2),
(3, 'villa_luxe_1.jpg', 'Piscine et jardin', 1),
(3, 'villa_luxe_2.jpg', 'Suite principale', 2),
(4, 'studio_1.jpg', 'Studio meublé complet', 1),
(5, 'maison_pn_1.jpg', 'Cour et jardin', 1);
