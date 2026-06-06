<?php
    require_once('bootstrap.php');
    require("db/model.php");
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');

    // retry doit être après les headers
    echo "retry: 500" . PHP_EOL;

    function liste_utilisateur(){
        $crud = new crud();
        $member = $crud->readAll('member', '*')->fetchAll(); $d="";
        foreach($member as $data):
            if($data["member_id"] != $_SESSION['id']):
                $statut = ($data['member_statut'] === 'connecté') ? 'on' : 'off';
                $d .= '<a class="contact" href="index.php?controller=messenger&rcv='.htmlspecialchars($data['member_id']).'">'
                    . '<span class="contact-name">'.htmlspecialchars($data['member_nom'].' '.$data['member_prenom']).'</span>'
                    . '<span class="contact-status status-'.$statut.'">'.htmlspecialchars($data['member_statut']).'</span>'
                    . '</a>';
            endif;
        endforeach;
        return $d;
    }

    function lister_message(){
        $crud = new crud();
        $rcv = $crud->readWhere('member', '*', 'member_id = ?', [$_SESSION['rcv']])->fetch(); $d="";

        // Last-Event-ID : envoyé automatiquement par le navigateur à chaque reconnexion
        $lastId = isset($_SERVER['HTTP_LAST_EVENT_ID']) ? (int)$_SERVER['HTTP_LAST_EVENT_ID'] : 0;

        if($lastId === 0){
            // Première connexion : tous les messages de la conversation
            $condition = '((messenger_id_sender = ? AND messenger_id_receiver = ?) OR (messenger_id_sender = ? AND messenger_id_receiver = ?)) ORDER BY messenger_id ASC';
            $params = [$_SESSION['id'], $_SESSION['rcv'], $_SESSION['rcv'], $_SESSION['id']];
        } else {
            // Reconnexion : uniquement les messages plus récents que le dernier reçu
            $condition = '((messenger_id_sender = ? AND messenger_id_receiver = ?) OR (messenger_id_sender = ? AND messenger_id_receiver = ?)) AND messenger_id > ? ORDER BY messenger_id ASC';
            $params = [$_SESSION['id'], $_SESSION['rcv'], $_SESSION['rcv'], $_SESSION['id'], $lastId];
        }

        $msg = $crud->readWhere('messenger', '*', $condition, $params)->fetchAll();
        $newLastId = !empty($msg) ? end($msg)['messenger_id'] : $lastId;

        foreach($msg as $t){
            if($_SESSION['id'] == $t["messenger_id_sender"]){
                $d .= '<div class="msg msg-sent"><div class="bubble">'
                    . '<p class="text">'.htmlspecialchars($t["messenger_content"]).'</p>'
                    . '<span class="time">'.htmlspecialchars($t["messenger_date"]).'</span>'
                    . '</div></div>';
            }
            elseif($_SESSION["id"] == $t["messenger_id_receiver"]){
                $d .= '<div class="msg msg-received"><div class="bubble">'
                    . '<span class="author">'.htmlspecialchars($rcv['member_nom'].' '.$rcv['member_prenom']).'</span>'
                    . '<p class="text">'.htmlspecialchars($t["messenger_content"]).'</p>'
                    . '<span class="time">'.htmlspecialchars($t["messenger_date"]).'</span>'
                    . '</div></div>';
            }
        }
        return ['html' => $d, 'lastId' => $newLastId];
    }

    function event($id, $event, $data){
        // Supprime les sauts de ligne — le champ data: doit tenir sur une seule ligne en SSE
        $data = str_replace(["\r\n", "\r", "\n"], ' ', $data);
        echo "id: ".$id.PHP_EOL;
        echo "event: ".$event.PHP_EOL;
        echo "data: ".$data.PHP_EOL;
        echo PHP_EOL;
        flush();
    }

    $result = lister_message();
    event(time(), 'user', liste_utilisateur());
    event($result['lastId'], 'message', $result['html']);
?>
