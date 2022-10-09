<?php
    session_start();
    require('model.php');
    if(isset($_GET['task']) && $_GET['task']=='envoyer'){
        $db = new db("messenger");
        $db->add([
            "messenger_id"=>date("d-m-Y H:i:s:u"),
            "messenger_content"=>$_GET['content'],
            "messenger_id_sender"=>$_SESSION['id'],
            "messenger_id_receiver"=>$_SESSION['rcv'],
        ]);
    }