<?php
    session_start();
    require('controller.php');
    if(isset($_GET['controller'])){
        $_GET['controller']();
    }else{
        signup();
    }
?>