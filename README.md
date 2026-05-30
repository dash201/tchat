# Tchat — Application de messagerie en temps réel

Une application de chat en temps réel développée en PHP pur, sans framework ni base de données relationnelle. Les utilisateurs peuvent s'inscrire, se connecter, voir qui est en ligne et échanger des messages en direct.

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
| Stockage | Fichiers JSON |
| Sessions | PHP Sessions natives |
| Sécurité | `password_hash` / `password_verify` |
| Frontend | HTML, JavaScript vanilla |

---

## Architecture

Le projet suit une architecture **MVC maison** sans framework :

```
tchat/
├── index.php          # Point d'entrée — routeur
├── controller.php     # Contrôleurs (signup, signin, dashboard, messenger...)
├── model.php          # Classe db — accès aux fichiers JSON
├── server.php         # Endpoint d'envoi de message (appelé par XHR)
├── event.php          # Endpoint SSE — diffuse les messages et la liste d'utilisateurs
├── asset/
│   └── ui.js          # JavaScript côté client (SSE + envoi de message)
├── views/
│   ├── template.html  # Layout principal
│   ├── nav.html       # Navigation
│   ├── dashboard.html # Vue du tableau de bord
│   ├── messenger.html # Vue de la conversation
│   ├── signup.html    # Vue connexion
│   └── signin.html    # Vue inscription
└── db/                # Fichiers JSON (données — ignorés par git)
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

1. Cloner le dépôt
2. Lancer un serveur PHP local à la racine du projet :
   ```bash
   php -S localhost:8000
   ```
3. Ouvrir [http://localhost:8000](http://localhost:8000) dans le navigateur

Aucune dépendance externe, aucune base de données à configurer.

---

## Auteur

Dash201 : Projet tchat-SSE — 2022 
