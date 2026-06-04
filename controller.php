<?php
    require('db/model.php');
    //connexion
    function signup(){
        if(isset($_POST['btn']) && $_POST['btn']==="connexion"){
            $crud = new crud();
            if(isset($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf'])){
                unset($_SESSION['csrf']);
                $member = $crud->readWhere('member', '*', 'member_email = ?', [$_POST['mail']])->fetch();
                if($member && password_verify($_POST['password'], $member['member_pwd'])){
                    $crud->up('member', 'member_statut = ?', 'member_id = ?', ['connecté', $member['member_id']]);
                    $_SESSION['nom']=$member['member_nom'];$_SESSION['prenom']=$member['member_prenom'];$_SESSION['id']=$member['member_id'];
                    header('location:index.php?controller=dashbord');
                    exit();
                }else{
                    header('location:index.php?controller=signup');
                    exit();
                }
            }else{
                die('CSRF token mismatch');
            }
            
        }
        require('./views/signup.html');
    }

    //inscription
    function signin(){
        if(isset($_POST['btn']) && $_POST['btn']==="inscription"){
            $crud = new crud();
            if(isset($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf'])){
                unset($_SESSION['csrf']);
                $exists = $crud->readWhere('member', 'member_id', 'member_email = ?', [$_POST['mail']])->fetch();
                if(!$exists){
                    $id = $crud->add(
                        'member',
                        'member_nom, member_prenom, member_email, member_pwd, member_statut',
                        '?, ?, ?, ?, ?',
                        [$_POST['nom'], $_POST['prenom'], $_POST['mail'], password_hash($_POST['password'], PASSWORD_DEFAULT), 'connecté']
                    );
                    $_SESSION['nom']=$_POST['nom'];$_SESSION['prenom']=$_POST['prenom'];$_SESSION['id']=$id;
                    header('location:index.php?controller=dashbord');
                    exit();
                }else{
                    echo 'Email deja utilise, veillez saisir un nouveau';
                }
                
            }else{
                die('CSRF token mismatch');
            }
            
        }
        require('./views/signin.html');
    }

    function dashbord(){
        if(isset($_SESSION["id"])){
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
        $crud = new crud(); $crud->up('member', 'member_statut = ?', 'member_id = ?', ['déconnecté', $_SESSION['id']]);
        session_destroy();
        header('location:index.php?controller=signup');
    }

?>
