<?php
class myDB{
    public $conn;
    public function __construct($dbname) {
        $this->conn = dbConnect($dbname);
    }
    public function check_user($login,$pass){
        try {
            $smtp = $this->conn->prepare("SELECT user_id FROM pap_user WHERE user_login=:login AND user_pass=:pass");
            $smtp->bindParam(":login",$login);
            $smtp->bindParam(":pass",$pass);
            $smtp->execute();
            if($smtp->rowCount()>0){
                return $smtp->fetch(PDO::FETCH_ASSOC)['user_id'];
            } else {
                return false;
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_meta($tb,$field,$id,$arrkey=null){
        try {
            $key = "";
            if(isset($arrkey)){
                $key .= " AND meta_key IN $arrkey";
            }
            $stmt = $this->conn->prepare("SELECT meta_key,meta_value FROM $tb WHERE $field=:id $key");
            $stmt->bindParam(":id",$id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function update_meta($tb,$field,$id,$meta){
        try {
            $stmt0 = $this->conn->prepare("SELECT * FROM $tb WHERE $field=:id AND meta_key=:key");
            $stmt0->bindParam(":id",$id);
            $stmt0->bindParam(":key",$key);
            $stmt = $this->conn->prepare("UPDATE $tb SET meta_value=:val WHERE $field=:id AND meta_key=:key");
            $stmt->bindParam(":id",$id);
            $stmt->bindParam(":key",$key);
            $stmt->bindParam(":val",$val);
            $stmt1 = $this->conn->prepare("INSERT INTO $tb VALUES (null,:id,:key,:val)");
            $stmt1->bindParam(":id",$id);
            $stmt1->bindParam(":key",$key);
            $stmt1->bindParam(":val",$val);
            foreach($meta AS $key=>$val){
                $stmt0->execute();
                if($stmt0->rowCount()>0){
                    //update
                    $stmt->execute();
                } else {
                    //add
                    $stmt1->execute();
                }
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_repass($email){
        try{
            date_default_timezone_set("Asia/Bangkok");
            $endt = date_add(date_create(null),  date_interval_create_from_date_string("23 hours 59 minutes 59 seconds"));
            $end = date_format($endt,"Y-m-d H:i:s");
            $stmt = $this->conn->prepare("SELECT user_id,user_added FROM pap_user WHERE user_email=:email");
            $stmt->bindParam(":email",$email);
            $stmt->execute();
            if($stmt->rowCount()>0){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $id = $row['user_id'];
                $p = substr(md5($row['user_added']),0,7);
                $arrmeta = [
                    "rq" => $p,
                    "rq_expired" => $end
                ];
                $this->update_meta("pap_usermeta","user_id",$id,$arrmeta);
                return $p;
            } else {
                return false;
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
            return false;
        }
    }
    public function checkr($r){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
um.user_id,
um1.meta_value AS expired
FROM pap_usermeta AS um
LEFT JOIN pap_usermeta AS um1 ON um1.user_id=um.user_id AND um1.meta_key='rq_expired'
WHERE um.meta_value=:r AND um.meta_key='rq'
END_OF_TEXT;
           $stmt = $this->conn->prepare($sql);
           $stmt->bindParam(":r",$r);
           $stmt->execute();
           if($stmt->rowCount()>0){
               $row = $stmt->fetch(PDO::FETCH_ASSOC);
               $uid = $row['user_id'];
               $expt = date_create($row['expired']);
               $now = date_create(null,timezone_open("GMT"));
               if($now<$expt){
                   return $uid;
               } else {
                   return false;
               }
           } else {
               return false;
           }
        } catch (Exception $ex) {
           db_error(__METHOD__, $ex);
           return false;
       }
    }
    public function insert_data($tb,$arr){
        try {
            $n = sizeof($arr);
            $prep = "";
            for($i=0;$i<$n;$i++){
                $prep .= ":val$i";
                $prep .= ($i==$n-1?"":",");
            }
            $stmt = $this->conn->prepare("INSERT INTO $tb VALUES($prep)");
            for($i=0;$i<$n;$i++){
                $stmt->bindParam(":val$i",$arr[$i]);
            }
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function update_data($tb,$field,$id,$arrinfo){
        try {
            $sql = gen_sql($arrinfo,",","param");
            $stmt = $this->conn->prepare("UPDATE $tb SET $sql[0] WHERE $field=:id");
            $stmt->bindParam(":id",$id);
            foreach($sql[1] AS $k => $v){
                $stmt->bindParam(":$k",$sql[1][$k]);
            }
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_dup($tb,$field,$val,$ex_itself=null){
        try {
            $ex = (isset($ex_itself)?"AND $ex_itself":"");
            $stmt = $this->conn->prepare("SELECT $field FROM $tb WHERE $field=:val $ex");
            $stmt->bindParam(":val",$val);
            $stmt->execute();
            if($stmt->rowCount()>0){
                return true;
            } else {
                return false;
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_info($tb,$field,$id){
        try {
            $stmt = $this->conn->prepare("SELECT * FROM $tb WHERE $field=:id");
            $stmt->bindParam(":id",$id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_infos($tb,$field,$id){
        try {
            $stmt = $this->conn->prepare("SELECT * FROM $tb WHERE $field=:id");
            $stmt->bindParam(":id",$id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_keypair($tb,$key,$val,$where=""){
        try {
            $stmt = $this->conn->prepare("SELECT $key,$val FROM $tb $where");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function delete_data($tb,$col,$val){
        try {
            $stmt = $this->conn->prepare("DELETE FROM $tb WHERE $col=:val");
            $stmt->bindParam(":val",$val);
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_mm_arr($tb,$tg,$col,$val){
        try {
            $stmt = $this->conn->prepare("SELECT $tg FROM $tb WHERE $col=:val");
            $stmt->bindParam(":val",$val);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN,0);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    
}

