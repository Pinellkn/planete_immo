# 🏡 BÉNIN IMMO - Plateforme de Location Immobilière

## Installation dans XAMPP

### Étape 1 : Copier le dossier
Extraire et copier le dossier `planete-immo` dans :
```
C:\xampp\htdocs\planete-immo\
```

### Étape 2 : Créer la base de données
1. Ouvrir **phpMyAdmin** : http://localhost/phpmyadmin
2. Créer une nouvelle base de données : `benin_immo`
3. Importer le fichier `database.sql`

### Étape 3 : Configurer la connexion
Ouvrir `includes/config.php` et vérifier :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'benin_immo');
define('DB_USER', 'root');
define('DB_PASS', '');  // Vide par défaut sur XAMPP
define('SITE_URL', 'http://localhost/planete-immo');
```

### Étape 4 : Lancer le site
Ouvrir : **http://localhost/planete-immo**

---

## 🔐 Comptes de démonstration
| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Administrateur | admin@planeteimmo.bj | password |
| Agent 1 | agent1@planeteimmo.bj | password |
| Agent 2 | agent2@planeteimmo.bj | password |
| Locataire | locataire1@gmail.com | password |

---

## 📁 Structure du projet
```
planete-immo/
├── includes/          # Config, header, footer communs
├── css/               # Feuilles de style
├── js/                # JavaScript
├── uploads/           # Photos maisons et avatars
├── ajax/              # Requêtes AJAX (favoris)
├── admin/             # Tableau de bord administrateur
│   └── includes/      # Sidebar admin
├── agent/             # Tableau de bord agent
│   └── includes/      # Sidebar agent
├── locataire/         # Tableau de bord locataire
│   └── includes/      # Sidebar locataire
├── index.php          # Page d'accueil
├── maisons.php        # Liste des annonces
├── detail.php         # Détail d'une maison
├── login.php          # Connexion
├── register.php       # Inscription
├── profil.php         # Gestion du profil
├── contact.php        # Page contact
├── logout.php         # Déconnexion
└── database.sql       # Base de données complète
```

## 📱 Fonctionnalités
- ✅ Inscription/Connexion (3 rôles : Admin, Agent, Locataire)
- ✅ Annonces immobilières avec filtres avancés
- ✅ Système de favoris
- ✅ Demandes de location
- ✅ Gestion des contrats
- ✅ Suivi des paiements
- ✅ Messagerie interne
- ✅ Notifications
- ✅ 3 tableaux de bord complets
- ✅ Design moderne et responsive
