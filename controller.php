<?php
    require('db/model.php');
    //connexion
    function signup(){
        if(isset($_POST['btn']) && $_POST['btn']==="connexion"){
            $crud = new crud();
            if(isset($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf'])){

                unset($_SESSION['csrf']);

                $mail = trim($_POST['mail']);
                if(!filter_var($mail, FILTER_VALIDATE_EMAIL)){
                    die('Email invalide');
                }

                try {
                    $member = $crud->readWhere('member', '*', 'member_email = ?', [$mail])->fetch();
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    http_response_code(500);
                    die('Une erreur est survenue, veuillez réessayer');
                }

                if($member && password_verify($_POST['password'], $member['member_pwd'])){
                    try {
                        $crud->up(
                            'member',
                            'member_statut = ?', 'member_id = ?',
                            ['connecté', $member['member_id']]
                        );
                    } catch (PDOException $e) {
                        error_log($e->getMessage());
                    }

                    session_regenerate_id(true);

                    $_SESSION['nom']=$member['member_nom'];
                    $_SESSION['prenom']=$member['member_prenom'];
                    $_SESSION['id']=$member['member_id'];

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

                $nom = trim($_POST['nom']);
                $prenom = trim($_POST['prenom']);
                if(empty($nom) || empty($prenom)){
                    die('Nom et prénom sont requis');
                }
                if(strlen($nom) > 50 || strlen($prenom) > 50){
                    die('Nom et prénom doivent être inférieurs à 50 caractères');
                }

                $mail = trim($_POST['mail']);
                if(!filter_var($mail, FILTER_VALIDATE_EMAIL)){
                    die('Email invalide');
                }

                try {
                    $id = $crud->add(
                        'member',
                        'member_nom, member_prenom, member_email, member_pwd, member_statut',
                        '?, ?, ?, ?, ?',
                        [$nom, $prenom, $mail, password_hash($_POST['password'], PASSWORD_DEFAULT), 'connecté']
                    );
                } catch (PDOException $e) {
                    if($e->getCode() === '23000'){
                        die('Email déjà utilisé, veuillez saisir un nouveau');
                    }
                    error_log($e->getMessage());
                    http_response_code(500);
                    die('Une erreur est survenue, veuillez réessayer');
                }

                session_regenerate_id(true);

                $_SESSION['nom']=$nom;
                $_SESSION['prenom']=$prenom;
                $_SESSION['id']=$id;

                header('location:index.php?controller=dashbord');
                exit();

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
        $crud = new crud(); 
        $crud->up('member', 'member_statut = ?', 'member_id = ?', ['déconnecté', $_SESSION['id']]);
        session_destroy();
        header('location:index.php?controller=signup');
        exit();
    }

?>
