<?php
    session_start();
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