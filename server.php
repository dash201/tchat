<?php
    require_once('bootstrap.php');
    require('db/model.php');
    
    if(isset($_POST['task']) && $_POST['task']=='envoyer' && isset($_SESSION['id'], $_SESSION['rcv'])){
        if(isset($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf'])){
            $content = trim($_POST['content'] ?? '');
            if($content !== '' && mb_strlen($content) <= 2000){
                $crud = new crud();
                $crud->add(
                    'messenger',
                    'messenger_content, messenger_id_sender, messenger_id_receiver',
                    '?, ?, ?',
                    [$content, $_SESSION['id'], $_SESSION['rcv']]
                );
            }
        }
        exit();
    }
?>