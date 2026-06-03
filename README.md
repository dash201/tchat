# Tchat — Application de messagerie en temps réel

Une application de chat en temps réel développée en PHP pur, sans framework. Les utilisateurs peuvent s'inscrire, se connecter, voir qui est en ligne et échanger des messages en direct. Les données sont stockées dans une base **MySQL** via une couche d'accès PDO maison.

---

## Fonctionnalités

- Inscription et connexion avec mot de passe chiffré
- Tableau de bord listant les utilisateurs et leur statut (connecté / déconnecté)
- Messagerie privée entre deux utilisateurs
- Mise à jour en temps réel sans rechargement de page
- Déconnexion avec mise à jour du statut

---

## Technologies utilisées

| Couche | Technologie |
|---|---|
| Backend | PHP 8+ |
| Temps réel | Server-Sent Events (SSE) |
| Requêtes async | XMLHttpRequest (XHR) |
| Base de données | MySQL via PDO (requêtes préparées) |
| Sessions | PHP Sessions natives |
| Sécurité | `password_hash` / `password_verify`, `htmlspecialchars`, whitelist de routes |
| Frontend | HTML, CSS maison (rendu type WhatsApp), JavaScript vanilla |

---

## Architecture

Le projet suit une architecture **MVC maison** sans framework :

```
tchat/
├── index.php          # Point d'entrée — routeur (whitelist de contrôleurs)
├── controller.php     # Contrôleurs (signup, signin, dashboard, messenger...)
├── server.php         # Endpoint d'envoi de message (appelé par XHR)
├── event.php          # Endpoint SSE — diffuse les messages et la liste d'utilisateurs
├── asset/
│   ├── ui.js          # JavaScript côté client (SSE + envoi de message)
│   └── style.css      # Feuille de style (rendu type WhatsApp / Messenger)
├── views/
│   ├── template.html  # Layout principal
│   ├── nav.html       # Navigation
│   ├── dashboard.html # Vue du tableau de bord
│   ├── messenger.html # Vue de la conversation
│   ├── signup.html    # Vue connexion
│   └── signin.html    # Vue inscription
└── db/
    ├── model.php      # Classe crud — accès MySQL (PDO)
    ├── config.php     # Identifiants de connexion (ignoré par git)
    └── schema.sql     # Schéma des tables (member, messenger)
```

### Fonctionnement du temps réel

Le client ouvre une connexion `EventSource` vers `event.php`. Le serveur répond avec le format SSE et pousse les données (liste d'utilisateurs, messages). La reconnexion automatique est gérée par le navigateur.

```
Navigateur                        Serveur
    |--- EventSource(event.php) --->|
    |<-- event: user (HTML) --------|
    |<-- event: message (HTML) -----|
    |    (reconnexion auto)         |
    |--- XHR GET server.php ------->|  (envoi d'un message)
```

---

## Installation

Prérequis : **PHP 8+** (extension `pdo_mysql`) et un serveur **MySQL / MariaDB**.

1. Cloner le dépôt
2. Créer la base et les tables :
   ```bash
   mysql -u root -p < db/schema.sql
   ```
3. Renseigner les identifiants de connexion dans `db/config.php`
4. Lancer un serveur PHP local à la racine du projet :
   ```bash
   php -S localhost:8000
   ```
5. Ouvrir [http://localhost:8000](http://localhost:8000) dans le navigateur

---

## Refonte — Migration JSON → MySQL

Le projet reposait initialement sur un stockage en **fichiers JSON**. Il a été refondu pour s'appuyer sur une véritable base **MySQL**, avec un renforcement de la sécurité.

### Inventaire avant / après

| Élément | Avant (v1 — JSON) | Après (refonte — MySQL) |
|---|---|---|
| Stockage | Fichiers JSON (classe `db`, racine `model.php`) | MySQL via PDO (classe `crud`, `db/model.php`) |
| Identifiants | Chaînes `date("d-m-Y H:i:s:u")` | Entiers `AUTO_INCREMENT` |
| Schéma | Implicite (clés JSON) | Explicite (`db/schema.sql`) |
| Connexion | — | Externalisée dans `db/config.php` |
| Horodatage des messages | `messenger_id` (chaîne date) | Colonne dédiée `messenger_date` (DATETIME) |
| Filtrage des messages | En PHP (lecture de tout, puis tri) | En SQL (requête filtrée + `ORDER BY`) |
| Interface | HTML brut, sans style | CSS maison (rendu type WhatsApp / Messenger) |

### Volet sécurité

Failles présentes en v1 et **corrigées** lors de la refonte :

- **Exécution de code arbitraire (RCE)** — `index.php` appelait `$_GET['controller']()`, exécutant n'importe quelle fonction PHP via l'URL → remplacé par une **whitelist** de contrôleurs autorisés.
- **Injection HTML / XSS** — le contenu des messages et des contacts était affiché en HTML brut → **`htmlspecialchars()`** appliqué systématiquement à l'affichage.
- **Endpoint non protégé** — `server.php` insérait un message sans vérifier la session → **garde de session** (`isset`) ajoutée avant toute écriture.

Précautions propres à la **nouvelle couche MySQL** :

- **Injection SQL** — les valeurs transitent uniquement par des **requêtes préparées PDO** (placeholders `?`) ; les noms de table/colonnes restent des littéraux codés en dur.
- **Identifiants de connexion** — déplacés dans `db/config.php`, **exclu du dépôt** via `.gitignore`.
- **Connexion durcie** — mode `ERRMODE_EXCEPTION`, jeu de caractères `utf8mb4`, `EMULATE_PREPARES => false`.

> Conservé de la v1 : le hachage des mots de passe (`password_hash` / `password_verify`), déjà correct.

---

## Auteur

Dash201 : Projet tchat-SSE — 2022 
