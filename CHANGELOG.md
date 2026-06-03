# Journal des modifications

Toutes les évolutions notables du projet sont consignées dans ce fichier.

Le format s'inspire de [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/),
et le projet suit le [versionnage sémantique](https://semver.org/lang/fr/).

## [2.0.0] — 2026-06-03

### Ajouté
- Base de données **MySQL** via une couche d'accès PDO (`db/model.php`, classe `crud`).
- Schéma de base explicite (`db/schema.sql`) : tables `member` et `messenger`.
- Configuration de connexion externalisée (`db/config.php`, ignorée par git).
- Colonne `messenger_date` (DATETIME) pour l'horodatage des messages.
- Feuille de style maison (`asset/style.css`) au rendu type WhatsApp / Messenger.

### Modifié
- Renommage de la plateforme en **« Chat »**.
- Identifiants en entiers `AUTO_INCREMENT` au lieu de chaînes `date(...)`.
- Filtrage des messages déplacé en **SQL** (requête filtrée + `ORDER BY`) au lieu d'un tri en PHP.
- Interfaces inscription / connexion et messagerie entièrement restylées.

### Sécurité
- **RCE** corrigée : `index.php` n'exécute plus une fonction PHP arbitraire issue de l'URL
  (whitelist de contrôleurs autorisés).
- **XSS** corrigée : tout contenu affiché passe par `htmlspecialchars()`.
- **Injection SQL** évitée : requêtes préparées PDO (valeurs liées par `?`).
- Garde de session ajoutée sur `server.php` avant toute écriture.
- Identifiants de connexion sortis du code et exclus du dépôt.

### Supprimé
- Ancien stockage en fichiers JSON (`model.php` à la racine, classe `db`).

## [1.0.0] — 2022

### Ajouté
- Première version : chat en temps réel via Server-Sent Events (SSE).
- Inscription / connexion avec hachage des mots de passe (`password_hash`).
- Stockage des données en fichiers JSON.
- Tableau de bord des utilisateurs et messagerie privée entre deux membres.

[2.0.0]: #200--2026-06-03
[1.0.0]: #100--2022
