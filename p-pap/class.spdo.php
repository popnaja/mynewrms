<?php
class sPDO{
    private $conn;
    public function __construct() {
        $this->conn = dbConnect(DB_PAP);
    }
    public function update_paper_name(){
        try {
            $stmt = $this->conn->prepare("SELECT ");
            $sql = <<<END_OF_TEXT
SELECT
mat_id,
CONCAT(po.op_name," (",po1.op_name,") ",po2.op_name,"แกรม")
FROM pap_mat AS mat
LEFT JOIN pap_option AS po ON po.op_id=mat.mat_type AND po.op_type='paper_type'
LEFT JOIN pap_option AS po1 ON po1.op_id=mat.mat_size AND po1.op_type='paper_size'
LEFT JOIN pap_option AS po2 ON po2.op_id=mat.mat_weight AND po2.op_type='paper_weight'
WHERE mat.mat_cat_id=8
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $stmt = $this->conn->prepare("UPDATE pap_mat SET mat_name=:name WHERE mat_id=:mid");
            $stmt->bindParam(":mid",$mid);
            $stmt->bindParam(":name",$name);
            foreach($data as $k=>$v){
                $mid = $k;
                $name = $v;
                $stmt->execute();
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
}

