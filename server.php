<?php
    session_start();
    require('db/model.php');
    if(isset($_POST['task']) && $_POST['task']=='envoyer' && isset($_SESSION['id'], $_SESSION['rcv'])){
        if(isset($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf'])){
            $crud = new crud();
            $crud->add(
                'messenger',
                'messenger_content, messenger_id_sender, messenger_id_receiver',
                '?, ?, ?',
                [$_POST['content'], $_SESSION['id'], $_SESSION['rcv']]
            );
         }
        exit();
    }
?>