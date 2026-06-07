<?php
    session_set_cookie_params([
        'lifetime' => 0, // La session expire lorsque le navigateur est fermé
        'domain' => '', // Utiliser le domaine actuel
        'secure' => isset($_SERVER['HTTPS']), // Utiliser des cookies sécurisés si HTTPS est utilisé
        'httponly' => true, // Empêcher l'accès aux cookies via JavaScript
        'samesite' => 'Strict' // Empêcher les requêtes intersites
    ]);

    session_start();

    define('CSP_NONCE', base64_encode(random_bytes(16))); // Génère un nonce aléatoire pour la politique de sécurité du contenu

    header('X-Frame-Options: DENY'); // Empêche l'inclusion de la page dans une iframe (protection contre le clickjacking)
    header('X-Content-Type-Options: nosniff'); // Empêche le navigateur de deviner le type de contenu
    header(
        "Content-Security-Policy: ".
        "default-src 'self'; ".
        "script-src 'self' 'nonce-".CSP_NONCE."'; ".
        "style-src 'self'"
    );

    

?>