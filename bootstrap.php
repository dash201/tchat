<?php
    session_set_cookie_params([
        'lifetime' => 0, // La session expire lorsque le navigateur est fermé
        'domain' => '', // Utiliser le domaine actuel
        'secure' => isset($_SERVER['HTTPS']), // Utiliser des cookies sécurisés si HTTPS est utilisé
        'httponly' => true, // Empêcher l'accès aux cookies via JavaScript
        'samesite' => 'Strict' // Empêcher les requêtes intersites
    ]);

    session_start();
?>