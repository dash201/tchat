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

                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                $limite = date('Y-m-d H:i:s', time() - 15*60); // il y a 15 minutes

                // 1. Compte des tentatives de connexion échouées depuis ce mail +IP
                try {
                    $echecs = $crud->readwhere(
                        'login_attempt',
                        'COUNT(*) AS nb',
                        'login_email = ? AND login_ip = ? AND login_date > ?',
                        [$mail, $ip, $limite]
                    )->fetch();
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    http_response_code(500);
                    die('Une erreur est survenue, veuillez réessayer');
                }

                if ($echecs['nb'] >= 5){
                    http_response_code(429); // Trop de tentatives
                    die('Trop de tentatives de connexion échouées, veuillez réessayer dans 15 minutes');
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

                        // 2.Succès -> On supprime les tentatives échouées pour ce mail +IP
                        $crud->del('login_attempt', 'login_email = ?', [$mail]);
                        
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

                    // 3.Échec -> On enregistre la tentative échouée
                    try {
                        $crud->add(
                            'login_attempt',
                            'login_email, login_ip',
                            '?, ?',
                            [$mail, $ip]
                        );
                    } catch (PDOException $e) {
                        error_log($e->getMessage());
                    }

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

                $password = $_POST['password'] ?? '';
                if(strlen($password) < 8){
                    die('Le mot de passe doit contenir au moins 8 caractères');
                }

                try {
                    $id = $crud->add(
                        'member',
                        'member_nom, member_prenom, member_email, member_pwd, member_statut',
                        '?, ?, ?, ?, ?',
                        [$nom, $prenom, $mail, password_hash($password, PASSWORD_DEFAULT), 'connecté']
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
