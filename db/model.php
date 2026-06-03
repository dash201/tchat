<?php
    class crud{
        // Connexion PDO partagée (ouverte une seule fois pour toute la requête).
        private static $pdo = null;

        private function server(){
            if(self::$pdo === null){
                $cfg = require __DIR__.'/config.php';
                $dsn = 'mysql:host='.$cfg['host'].';dbname='.$cfg['dbname'].';charset='.$cfg['charset'];
                self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            }
            return self::$pdo;
        }

        // ATTENTION : $table / $field / $condition / $value sont concaténés dans la
        // requête — ils doivent toujours être des littéraux codés en dur, jamais une
        // entrée utilisateur. Seul $data (placeholders « ? ») reçoit les valeurs.

        public function readAll($table, $field){
            $db = $this->server();
            return $db->query('SELECT '.$field.' FROM '.$table);
        }

        public function readWhere($table, $field, $condition, $data){
            $db = $this->server();
            $req = $db->prepare('SELECT '.$field.' FROM '.$table.' WHERE '.$condition);
            $req->execute($data);
            return $req;
        }

        public function add($table, $field, $value, $data){
            $db = $this->server();
            $req = $db->prepare('INSERT INTO '.$table.'('.$field.') VALUES('.$value.')');
            $req->execute($data);
            return $db->lastInsertId();
        }

        public function del($table, $condition, $data){
            $db = $this->server();
            $req = $db->prepare('DELETE FROM '.$table.' WHERE '.$condition);
            $req->execute($data);
        }

        public function up($table, $field, $condition, $data){
            $db = $this->server();
            $req = $db->prepare('UPDATE '.$table.' SET '.$field.' WHERE '.$condition);
            $req->execute($data);
        }
    }
?>
