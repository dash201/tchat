<?php
    session_start();
    require('db/model.php');
    if(isset($_GET['task']) && $_GET['task']=='envoyer' && isset($_SESSION['id'], $_SESSION['rcv'])){
        $crud = new crud();
        $crud->add(
            'messenger',
            'messenger_content, messenger_id_sender, messenger_id_receiver',
            '?, ?, ?',
            [$_GET['content'], $_SESSION['id'], $_SESSION['rcv']]
        );
    }
