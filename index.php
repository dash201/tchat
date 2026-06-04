<?php
    session_start();
    if(!isset($_SESSION['csrf'])){
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    require('controller.php');
    // Whitelist des contrôleurs autorisés — évite l'exécution de fonctions PHP arbitraires via l'URL
    $routes = ['signup', 'signin', 'dashbord', 'messenger', 'disconnect'];
    $ctrl = $_GET['controller'] ?? 'signup';
    if(in_array($ctrl, $routes)){
        $ctrl();
    }else{
        signup();
    }
?>