# Journal des modifications

Toutes les évolutions notables du projet sont consignées dans ce fichier.

Le format s'inspire de [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/),
et le projet suit le [versionnage sémantique](https://semver.org/lang/fr/).

## [2.7.1] — 2026-06-07

### Sécurité
- **Longueur minimale du mot de passe** (`controller.php`) : refus à l'inscription
  des mots de passe de moins de 8 caractères.

## [2.7.0] — 2026-06-07

### Sécurité
- **En-têtes de sécurité HTTP** (`bootstrap.php`) :
  - **Content-Security-Policy** : `default-src 'self'`, `script-src 'self' 'nonce-…'`,
    `style-src 'self'`. Nonce aléatoire par requête (`CSP_NONCE`) injecté dans les scripts
    inline légitimes (`dashboard.html`, `messenger.html`) ; aucun `'unsafe-inline'`.
  - **X-Frame-Options: DENY** (anti-clickjacking).
  - **X-Content-Type-Options: nosniff**.

### Modifié
- Suppression du gestionnaire inline `onclick="send_sms()"` (bloqué par la CSP) au profit
  d'un `addEventListener` dans `ui.js`. Bouton d'envoi renommé `#sendsms`, écouteur attaché
  avec chaînage optionnel (`?.`) pour ne rien tenter sur les pages sans bouton.

## [2.6.0] — 2026-06-07

### Sécurité
- **Protection anti-force-brute** sur la connexion (`controller.php`, `db/schema.sql`) :
  - Nouvelle table `login_attempt` (email, IP, horodatage) avec index `(login_email, login_date)`.
  - Comptage des échecs récents (< 15 min) par couple email + IP **avant** la vérification
    du mot de passe ; au-delà de 5 échecs → blocage avec code HTTP `429`.
  - Purge des tentatives après une connexion réussie.
  - Toutes les requêtes liées sont encadrées par `try/catch (PDOException)`.

## [2.5.1] — 2026-06-07

### Corrigé
- **Clignotement de la liste des utilisateurs** : l'animation d'entrée était rejouée
  par chaque contact à chaque reconnexion SSE. Déplacée sur le conteneur (une seule
  fois au chargement).

### Amélioré
- **Mise à jour ciblée de la liste** (`ui.js`, `event.php`) : chaque contact porte
  désormais une clé stable `data-id`. Le DOM n'est plus reconstruit en bloc — une
  fonction de réconciliation ne remplace que les lignes réellement modifiées (statut),
  ajoute les nouveaux inscrits et retire les partis.
- Garde anti-re-rendu : si le contenu reçu est identique au précédent, le DOM n'est
  pas touché du tout.

## [2.5.0] — 2026-06-07

### Ajouté
- **Refonte visuelle complète** : nouvelle identité indigo → violet → rose
  (dégradés signature, verre dépoli, ombres douces, animations d'entrée).
- **Avatars** : pastille circulaire avec l'initiale du contact (liste et conversation).
- **En-tête de conversation** : bouton retour, avatar, nom et statut du destinataire.
- Étiquettes de champs sur les formulaires d'authentification.
- Lien de bascule connexion ↔ inscription dans la carte d'auth.

### Modifié
- **Pages connexion / inscription** repensées : fond dégradé immersif, carte vitrée
  centrée, logo en pastille.
- **Messagerie** : bulles modernisées (envoyé en dégradé, reçu bordé), fond à motif
  pointillé, bouton d'envoi circulaire à icône.
- **Tableau de bord** : en-tête « Discussions », liste de contacts redessinée.
- En-tête du site rendu *sticky* avec effet de flou ; marque en dégradé.
- Boutons d'auth migrés vers `<button name="btn" value="…">` (libellés lisibles
  sans changer la logique du contrôleur).
- `template.html` : `lang="fr"`, classe `body` par page, correction du pied de page
  (`copyrigth 2022` → `© <année courante>`).

### Responsive
- Adaptation mobile complète : conversation en plein écran (`100dvh`), bulles
  élargies, en-tête compacté, prise en charge de `prefers-reduced-motion`.

## [2.4.0] — 2026-06-06

### Sécurité
- **Validation des entrées** (inscription et connexion) :
  - Email validé avec `filter_var(FILTER_VALIDATE_EMAIL)` + `trim()`
  - Nom et prénom : champs requis, longueur max 50 caractères
  - Variables assainies (`$nom`, `$prenom`, `$mail`) utilisées partout dans l'insertion et la session
- **Validation du contenu des messages** (`server.php`) :
  - `trim()` + rejet des messages vides
  - Longueur limitée à 2000 caractères (`mb_strlen`)
- **Gestion des exceptions PDO** (`controller.php`) :
  - `try/catch (PDOException $e)` autour de toutes les opérations base de données
  - Code `23000` (contrainte UNIQUE) → message « Email déjà utilisé » sans exposer la trace
  - Autres erreurs → `error_log()` + `http_response_code(500)`
  - `signin()` : suppression du `SELECT` préalable — la contrainte UNIQUE + exception élimine la race condition
- **Durcissement des cookies de session** (`bootstrap.php`) :
  - `HttpOnly` : cookie inaccessible via JavaScript
  - `Secure` : détecté automatiquement via `$_SERVER['HTTPS']` — actif en prod HTTPS, inactif en dev HTTP
  - `SameSite: Strict` : renforce la protection CSRF côté cookie
  - Centralisé dans `bootstrap.php`, inclus par `index.php`, `server.php` et `event.php`

## [2.3.0] — 2026-06-05

### Amélioré
- **Affichage des messages** : passage de `innerHTML =` (remplacement complet) à
  `insertAdjacentHTML('beforeend', ...)` (ajout en fin de liste) — fini le clignotement
  et la perte de position du scroll.
- **Scroll automatique** vers le dernier message à chaque nouveau message reçu.
- **SSE incrémental** (`Last-Event-ID`) : à la première connexion, tous les messages
  sont chargés ; aux reconnexions suivantes, seuls les nouveaux sont envoyés — évite
  les doublons et réduit la charge réseau/base de données.
- **Paramètre `append`** ajouté à la fonction `event()` JS : `true` pour les messages
  (ajout), `false` pour la liste de contacts (remplacement) — chaque composant a
  maintenant le comportement adapté à son usage.
- **Protection des données SSE** : les sauts de ligne dans le contenu des messages
  sont nettoyés avant envoi (`str_replace`) pour respecter le format SSE.

## [2.2.0] — 2026-06-04

### Sécurité
- **CSRF** : token aléatoire (`bin2hex(random_bytes(32))`) généré en session et vérifié
  avec `hash_equals()` sur toutes les actions sensibles — connexion, inscription, envoi de message.
- Token injecté dans les formulaires `signup.html`, `signin.html` et `messenger.html`
  via un champ `<input type="hidden">`.
- `server.php` migré de **GET → POST** pour l'envoi de message ; token vérifié côté serveur.
- **Fixation de session** : `session_regenerate_id(true)` ajouté après chaque authentification
  réussie (connexion et inscription) dans `controller.php`.
- `exit()` ajouté après chaque `header('location:...')` pour stopper l'exécution.

### Corrigé
- Bug `xhr.readyState==200 && xhr.status==4` inversé dans `ui.js` →
  corrigé en `readyState==4 && xhr.status==200` (le textarea se vide maintenant correctement).
- `encodeURIComponent()` ajouté sur le contenu et le token dans `send_sms()` pour éviter
  la troncature des messages contenant des caractères spéciaux (`&`, `=`, `+`…).

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

[2.7.1]: #271--2026-06-07
[2.7.0]: #270--2026-06-07
[2.6.0]: #260--2026-06-07
[2.5.1]: #251--2026-06-07
[2.5.0]: #250--2026-06-07
[2.4.0]: #240--2026-06-06
[2.3.0]: #230--2026-06-05
[2.2.0]: #220--2026-06-04
[2.0.0]: #200--2026-06-03
[1.0.0]: #100--2022
