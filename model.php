<?php
    class db{
        private $file;
        public function __construct($file){
            $this->file = $file;
        }

        public function add($array){
			if(file_exists('./db/'.$this->file.'.json')){
				$db = json_decode(file_get_contents('./db/'.$this->file.'.json'), true);
			}
			$db[] = $array;
			file_put_contents('./db/'.$this->file.'.json', json_encode($db));
			return true;
		}
		
		public function read(){
			$db = (file_exists('./db/'.$this->file.'.json'))? json_decode(file_get_contents('./db/'.$this->file.'.json'), true) : array();
			return $db;
		}

		public function readWhere($id, $cond){
			if(file_exists('./db/'.$this->file.'.json')){
				$db = json_decode(file_get_contents('./db/'.$this->file.'.json'), true);$newDb=[];
				foreach($db as $dt){
					if($dt[$this->file.'_id'] .$cond. $id){
						$newDb = $dt;
						break;
					}
				}
			}
			return $newDb;
		}

		public function verify($field, $data){
			if(file_exists('./db/'.$this->file.'.json')){
				$db = json_decode(file_get_contents('./db/'.$this->file.'.json'), true);$newDb=[];
				foreach($db as $dt){
					if($dt[$field] == $data){
						$newDb = $dt; break;
					}
				}
			}
			return $newDb;
		}

		public function delete($id){
			$db = json_decode(file_get_contents('./db/'.$this->file.'.json'), true);$f=$this->file;
			$data = array_filter($db, function ($dt) use ($f, $id){
				return ($dt[$this->file.'_id'] != $id);
			});
			file_put_contents('./db/'.$this->file.'.json', json_encode($data));
		}

		public function update($id, $array){
			$db = json_decode(file_get_contents('./db/'.$this->file.'.json'), true);
			$newDb = [];
			foreach($db as $dt){
				if($dt[$this->file.'_id'] == $id){
					foreach($array as $key => $value){
						$dt = array_replace($dt, array_fill_keys([$key],$value));
					}
				}
				$newDb[] = $dt;
			}
			file_put_contents('./db/'.$this->file.'.json', json_encode($newDb));
		}
    }