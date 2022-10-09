<?php
    session_start();
    require("model.php");
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');

    function liste_utilisateur(){
        $db = new db('member');
        $member = $db->read(); $d="";
        foreach($member as $data):
            if($data["member_id"] != $_SESSION['id']):
                $d .= '<a href="index.php?controller=messenger&rcv='.$data['member_id'].'">'.$data['member_nom'].'&nbsp;'.$data['member_prenom'].'('.$data['member_statut'].')</a><br/>';
            endif;
        endforeach;

        return $d;
    }

    function lister_message(){
        $db = new db('member');
        $rcv = $db->readWhere($_SESSION['rcv'], "=="); $d="";
        $db= new db('messenger'); $msg = $db->read();
        foreach($msg as $t){
            if($_SESSION['id'] == $t['messenger_id_sender'] && $_SESSION['rcv'] == $t['messenger_id_receiver'] OR $_SESSION['id'] == $t['messenger_id_receiver'] && $_SESSION['rcv'] == $t['messenger_id_sender']){
                
                $d .="<div>";
                    if($_SESSION['id'] == $t["messenger_id_sender"]){
                        $d .='<span style="color:green">'.$_SESSION['nom'].'&nbsp;'.$_SESSION['prenom'].': '.$t["messenger_content"].'</span><br>';
                    }
                    elseif($_SESSION["id"] == $t["messenger_id_receiver"]){
                        $d .='<span style="color:red">'.$rcv['member_nom'].'&nbsp;'.$rcv['member_prenom'].': '.$t["messenger_content"].'</span><br>';
                    }
                    $d .='<span>'.$t["messenger_id"].'</span><br>';
                $d .="</div>";
            }
        }
        return $d;
    }

    function event($id, $event, $data){
        echo "id: ". $id. PHP_EOL;
        echo "event: ".$event. PHP_EOL;
        echo "data: ".$data. PHP_EOL;
        echo PHP_EOL;
        flush();
    }
    

    $serverTime = time();

    event($serverTime, 'user', liste_utilisateur());
    event($serverTime, 'message', lister_message());

?>