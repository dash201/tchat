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
  exclu du dépôt via `.gitignore`.
- Connexion PDO durcie : `ERRMODE_EXCEPTION`, `EMULATE_PREPARES => false`,
  jeu de caractères `utf8mb4`.

### Gardes de session
- Vérification de session avant toute action authentifiée (`dashbord`, `messenger`,
  `server.php`).

### Validation des entrées
- Email validé avec `filter_var(FILTER_VALIDATE_EMAIL)` + `trim()` à la connexion et l'inscription.
- Nom et prénom : champs requis, longueur max 50 caractères.
- Contenu des messages : rejet des messages vides, limite à 2000 caractères (`mb_strlen`).

---

## Limites connues (projet en développement)

| Limitation | Impact | Statut |
|---|---|---|
| Pas de cookies `HttpOnly / Secure / SameSite` | Cookie de session accessible via JS / transmis en clair | Planifié |
| Pas de validation du mot de passe (longueur min) | Mots de passe trop courts acceptés | Planifié |
| Pas de protection anti-force-brute | Tentatives de mot de passe illimitées | Planifié |
| Pas d'en-têtes de sécurité HTTP | Pas de CSP, X-Frame-Options, X-Content-Type-Options | Planifié |
| Pas de HTTPS configuré | Données en transit non chiffrées (hors TLS externe) | Hors scope dev local |
| `db/schema.sql` servi par le serveur web | Divulgation du schéma de base possible | Planifié |

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
