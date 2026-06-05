# Journal des modifications

Toutes les évolutions notables du projet sont consignées dans ce fichier.

Le format s'inspire de [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/),
et le projet suit le [versionnage sémantique](https://semver.org/lang/fr/).

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

[2.3.0]: #230--2026-06-05
[2.2.0]: #220--2026-06-04
[2.0.0]: #200--2026-06-03
[1.0.0]: #100--2022
