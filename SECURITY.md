# Politique de sécurité — Chat

Ce document décrit les protections mises en place, les limites connues du projet,
et la procédure pour signaler une vulnérabilité.

---

## Protections implémentées

### Authentification
- Mots de passe hachés avec `password_hash()` (bcrypt, algorithme `PASSWORD_DEFAULT`)
  et vérifiés avec `password_verify()` — jamais stockés en clair.
- `session_regenerate_id(true)` après chaque authentification réussie pour prévenir
  les attaques de **fixation de session**.

### Protection CSRF
- Token aléatoire de 256 bits (`bin2hex(random_bytes(32))`) généré par session.
- Injecté dans chaque formulaire (`signup`, `signin`, messagerie) via `<input type="hidden">`.
- Vérifié côté serveur avec `hash_equals()` (comparaison en temps constant) avant
  toute action sensible — connexion, inscription, envoi de message.

### Injections
- **SQL** : toutes les requêtes passent par des requêtes préparées PDO avec
  placeholders `?`. Aucune valeur utilisateur n'est concaténée dans le SQL.
- **XSS** : tout contenu affiché est échappé avec `htmlspecialchars()`.
  Le contenu des messages est encodé avec `encodeURIComponent()` côté client.

### Routage
- Whitelist explicite des contrôleurs autorisés dans `index.php`.
  Empêche l'exécution de fonctions PHP arbitraires via le paramètre `controller`.

### Données sensibles
- Identifiants de connexion à la base externalisés dans `db/config.php`,
  exclu du dépôt via `.gitignore`. Étant un fichier PHP qui *retourne* un tableau,
  il est **exécuté** par le serveur (et non servi en texte brut) : son contenu
  n'est pas exposé par un accès direct à l'URL.
- `db/schema.sql` est **public volontairement** : il ne contient aucun secret
  (uniquement la structure des tables) et sert de référence pour installer le projet.
- Connexion PDO durcie : `ERRMODE_EXCEPTION`, `EMULATE_PREPARES => false`,
  jeu de caractères `utf8mb4`.

### Gardes de session
- Vérification de session avant toute action authentifiée (`dashbord`, `messenger`,
  `server.php`).

### Cookies de session durcis
- `HttpOnly` : cookie inaccessible via JavaScript (prévient le vol par XSS).
- `Secure` : cookie transmis uniquement en HTTPS, détecté automatiquement via `$_SERVER['HTTPS']` (compatible dev HTTP et prod HTTPS sans modification).
- `SameSite: Strict` : renforce la protection CSRF en bloquant l'envoi du cookie depuis un site tiers.
- Centralisé dans `bootstrap.php`, inclus par `index.php`, `server.php` et `event.php`.

### Validation des entrées
- Email validé avec `filter_var(FILTER_VALIDATE_EMAIL)` + `trim()` à la connexion et l'inscription.
- Nom et prénom : champs requis, longueur max 50 caractères.
- Contenu des messages : rejet des messages vides, limite à 2000 caractères (`mb_strlen`).

### Gestion des exceptions PDO
- Toutes les opérations base de données sont encadrées par `try/catch (PDOException $e)`.
- Violation de contrainte UNIQUE (code `23000`) : message utilisateur explicite « Email déjà utilisé ».
- Autres erreurs PDO : journalisées via `error_log()`, `http_response_code(500)`, aucune trace exposée à l'utilisateur.
- `signin()` ne fait plus de `SELECT` préalable avant l'`INSERT` — la contrainte UNIQUE de la base fait foi, ce qui élimine la race condition « vérifier puis insérer ».

### Protection anti-force-brute (connexion)
- Table `login_attempt` qui journalise chaque échec de connexion (email, IP, horodatage).
- Avant toute vérification de mot de passe, comptage des échecs récents (< 15 min) pour
  le couple email + IP ; au-delà de **5 échecs**, la connexion est bloquée avec un code
  HTTP `429` (Too Many Requests).
- Les tentatives sont purgées après une connexion réussie.

---

## Limites connues (projet en développement)

| Limitation | Impact | Statut |
|---|---|---|
| Pas de validation du mot de passe (longueur min) | Mots de passe trop courts acceptés | Planifié |
| Pas d'en-têtes de sécurité HTTP | Pas de CSP, X-Frame-Options, X-Content-Type-Options | Planifié |
| Pas de HTTPS configuré | Données en transit non chiffrées (hors TLS externe) | Hors scope dev local |

---

## Signalement d'une vulnérabilité

Ce projet est un projet d'apprentissage. Si vous identifiez une vulnérabilité :

1. **Ne pas l'exposer publiquement** (issue GitHub, réseaux sociaux…).
2. Contacter directement le mainteneur par email.
3. Décrire le vecteur d'attaque, les conditions de reproductibilité et l'impact estimé.

Une réponse sera apportée sous 72 heures.

---

## Versions

| Version | Support sécurité |
|---|---|
| 2.x (actuelle) | ✅ Maintenue |
| 1.x (JSON) | ❌ Abandonnée |
