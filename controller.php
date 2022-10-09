<?php
    require('model.php');
    //connexion
    function signup(){
        if(isset($_POST['btn']) && $_POST['btn']==="connexion"){
            $db = new db('member');
            //$verify = $db->verify("member_email", $_POST['mail']);
            if($verify = $db->verify("member_email", $_POST['mail'])){
                if(password_verify($_POST['password'],$verify['member_pwd'])){
                    $db->update($verify['member_id'], ["member_statut"=>"connecté"]);
                    $_SESSION['nom']=$verify['member_nom'];$_SESSION['prenom']=$verify['member_prenom'];$_SESSION['id']=$verify['member_id'];
                    header('location:index.php?controller=dashbord');
                }else{
                    header('location:index.php?controller=signup');
                }
            }else{
                header('location:index.php?controller=signup');
            }
        }
        require('./views/signup.html');
    }

    //inscription
    function signin(){
        if(isset($_POST['btn']) && $_POST['btn']==="inscription"){
            $db = new db('member');
            $verify = $db->verify("member_email", $_POST['mail']);
            if($verify['member_email'] != $_POST['mail']){
                $id=date("d-m-Y H:i:s:u");
                $db->add([
                    "member_id"=>$id,
                    "member_nom"=>$_POST['nom'],
                    "member_prenom"=>$_POST['prenom'],
                    "member_email"=>$_POST['mail'],
                    "member_pwd"=>password_hash($_POST['password'], PASSWORD_DEFAULT),
                    "member_statut"=>"connecté",
                ]);
                $_SESSION['nom']=$_POST['nom'];$_SESSION['prenom']=$_POST['prenom'];$_SESSION['id']=$id;
                header('location:index.php?controller=dashbord');
            }else{
                echo 'Email deja utilise, veillez saisir un nouveau';
            }
        }
        require('./views/signin.html');
    }

    function dashbord(){
        if(isset($_SESSION["id"])){
            $db = new db('member');
            $member = $db->read();
            require('./views/dashboard.html');
        }
    }

    function messenger(){
        if(isset($_SESSION["id"])){
            $_SESSION['rcv'] = $_GET["rcv"];
            require('./views/messenger.html');
        }
    }

    function disconnect(){
        $db = new db('member'); $db->update($_SESSION['id'], ["member_statut"=>"déconnecté"]);
        session_destroy();
        header('location:index.php?controller=signup');
    }

?>