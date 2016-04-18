<?php
class myterm{
    private $conn;
    public function __construct($db) {
        $this->conn = dbConnect($db);
    }
    public function get_parent($tax,$lineage=null){
        try{
            $ssql = (isset($lineage)?"AND lineage NOT LIKE :line":"");
            $sql = <<<END_OF_TEXT
SELECT tx.lineage,
CONCAT(REPEAT(' -  ',deep),name)
FROM pap_term AS tm
LEFT JOIN pap_term_tax AS tx ON tx.term_id=tm.id
WHERE tax=:tax
$ssql
ORDER BY lineage ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":tax",$tax);
            if(isset($lineage)){
                $line = $lineage."%";
                $stmt->bindParam(":line",$line);        
            }
            $stmt->execute();
            if($stmt->rowCount()>0){
                return array(0=>"none")+$stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            } else {
                return array(0=>"none");
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_term($auth,$tax){
        try {
            $edit = "";
            if($auth>1){
                $edit = <<<END_OF_TEXT
                        CONCAT("<a href='term.php?tax=$tax&tid=",tm.id,"' title='Edit' class='icon-page-edit'></a>"),
END_OF_TEXT;
            }
            $sql = <<<END_OF_TEXT
SELECT
$edit
CONCAT(REPEAT(' -  ',deep),name),
slug,des
FROM pap_term AS tm
LEFT JOIN pap_term_tax AS tx ON tx.term_id=tm.id
WHERE tax=:tax
ORDER BY lineage ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":tax",$tax);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_terminfo($tax,$tid){
        try {
            $sql = <<<END_OF_TEXT
SELECT name,slug,des,
parent,lineage,deep
FROM pap_term AS tm
LEFT JOIN pap_term_tax AS tx ON tx.term_id=tm.id
WHERE tax=:tax AND tm.id=:tid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":tax",$tax);
            $stmt->bindParam(":tid",$tid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function update_parent($tax,$tid,$old_p,$new_p){
        try {
            $cur = $this->get_terminfo($tax,$tid);
            $find = $cur['lineage'];
            if($new_p==0){
                $diff = -$cur['deep'];
                $line = $tid;
            } else if($old_p==0){
                $new = $this->get_terminfo($tax,$new_p);
                $diff = $new['deep']+1;
                $line = $new['lineage']."-$tid";
            } else {
                $old = $this->get_terminfo($tax,$old_p);
                $new = $this->get_terminfo($tax,$new_p);
                $diff = $new['deep'] - $old['deep'];
                $line = $new['lineage']."-$tid";
            }
            $like = $find."%";
            $sql = <<<END_OF_TEXT
UPDATE pap_term_tax SET deep = deep+:diff, lineage=REPLACE(lineage,:find,:line) WHERE lineage LIKE :like
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":diff",$diff,PDO::PARAM_INT);
            $stmt->bindParam(":find",$find);
            $stmt->bindParam(":line",$line);
            $stmt->bindParam(":like",$like);
            $stmt->execute();
            //update parent
            $stmt1 = $this->conn->prepare("UPDATE pap_term_tax SET parent=:parent WHERE term_id=:tid");
            $stmt1->bindParam(":parent",$new_p);
            $stmt1->bindParam(":tid",$tid);
            $stmt1->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function del_term($tax,$tid){
        try {
            $cur = $this->get_terminfo($tax,$tid);
            $diff = -$cur['deep']-1;
            $find = $cur['lineage']."-";
            $like = $find."%";
            $stmt = $this->conn->prepare("UPDATE pap_term_tax SET deep=deep+:diff , lineage=REPLACE(lineage,:find,'') WHERE lineage LIKE :like");
            $stmt->bindParam(":diff",$diff,PDO::PARAM_INT);
            $stmt->bindParam(":find",$find);
            $stmt->bindParam(":like",$like);
            $stmt->execute();
            //update parent
            $stmt1 = $this->conn->prepare("UPDATE pap_term_tax SET parent=0 WHERE parent=:tid");
            $stmt1->bindParam(":tid",$tid);
            $stmt1->execute();
            //delete rec
            $stmt2 = $this->conn->prepare("DELETE FROM pap_term WHERE id=:tid");
            $stmt2->bindParam(":tid",$tid);
            $stmt2->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
}

