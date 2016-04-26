<?php
__autoloada("pdo");
class PAPdb extends myDB{
    public function __construct($dbname) {
        parent::__construct($dbname);
    }
    public function pap_log($uid,$function,$info){
        try {
            $this->insert_data("pap_log", array(null,$uid,$function,$info,pap_now()));
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_optdup($type,$name,$opid=0){
        try {
            $stmt = $this->conn->prepare("SELECT op_id FROM pap_option WHERE op_type=:type AND op_name=:name AND op_id<>:id");
            $stmt->bindParam(":type",$type);
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":id",$opid);
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
    public function update_option($type,$arr){
        try {
            $stmt0 = $this->conn->prepare("SELECT * FROM pap_option WHERE op_type=:type AND op_name=:name");
            $stmt0->bindParam(":type",$type);
            $stmt0->bindParam(":name",$name);
            $stmt = $this->conn->prepare("UPDATE pap_option SET op_value=:val WHERE op_type=:type AND op_name=:name");
            $stmt->bindParam(":type",$type);
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":val",$val);
            $stmt1 = $this->conn->prepare("INSERT INTO pap_option VALUES (null,:type,:name,:val)");
            $stmt1->bindParam(":type",$type);
            $stmt1->bindParam(":name",$name);
            $stmt1->bindParam(":val",$val);
            foreach($arr AS $name=>$val){
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
    public function get_comp($qid,$iscover=null){
        try {
            $whsql = (isset($iscover)?($iscover?"AND comp_type='0'":"AND comp_type='1'"):"");
            $sql = <<<END_OF_TEXT
SELECT pap_quote_comp.*,
po.op_name AS weight,
pm.meta_value AS coating
FROM pap_quote_comp 
LEFT JOIN pap_option AS po ON po.op_id=comp_paper_weight
LEFT JOIN pap_process_meta AS pm ON pm.process_id=comp_coating AND pm.meta_key='cost'
WHERE quote_id=:qid $whsql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":qid",$qid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_print_comp($qid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
comp_type,
po1.op_name AS paper,
po.op_name AS weight,
pc1.process_name AS color,
pc.process_name AS coating,
cc.comp_postpress
FROM pap_quote_comp AS cc
LEFT JOIN pap_option AS po ON po.op_id=comp_paper_weight
LEFT JOIN pap_option AS po1 ON po1.op_id=comp_paper_type
LEFT JOIN pap_process AS pc ON pc.process_id=comp_coating
LEFT JOIN pap_process AS pc1 ON pc1.process_id=comp_print_id
WHERE quote_id=:qid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":qid",$qid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_paper_keypair($field,$size=null,$type=null){
        try {
            $size_sql = (isset($size)?"AND mat_size=$size":"");
            $type_sql = (isset($type)?" AND mat_type=$type":"");
            $order = ($field=="mat_weight"?"ORDER BY CAST(po.op_name AS UNSIGNED) ASC":"");
            $sql = <<<END_OF_TEXT
                    SELECT 
                    $field, po.op_name
                    FROM pap_mat 
                    LEFT JOIN pap_option AS po ON po.op_id=$field
                    WHERE mat_cat_id=8 $size_sql $type_sql
                    GROUP BY $field
                    $order
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_type_weight($cid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
mat_type,GROUP_CONCAT(mat_weight)
FROM pap_mat
WHERE mat_cat_id=:cid
GROUP BY mat_type
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":cid",$cid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_paper($type,$size,$weight){
        try {
            $sql = <<<END_OF_TEXT
SELECT *
FROM pap_mat
WHERE mat_size=:size AND mat_type=:type AND mat_weight=:weight
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":size",$size);
            $stmt->bindParam(":type",$type);
            $stmt->bindParam(":weight",$weight);
            $stmt->execute();
            if($stmt->rowCount()>0){
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt2 = $this->conn->prepare("SELECT op_type,op_name FROM pap_option WHERE op_id IN ($type,$size,$weight)");
                $stmt2->execute();
                $t = $stmt2->fetchAll(PDO::FETCH_KEY_PAIR);
                $res = $t['paper_type']." ขนาด ".$t['paper_size']." ".$t['paper_weight']."gram";
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_layinfo($sid){
        try {
            $sql = <<<END_OF_TEXT
SELECT pap_size.*,
po.op_name AS csize,
po.op_value AS pcover_size,
po1.op_name AS isize,
po1.op_value AS pinside_size
FROM pap_size
LEFT JOIN pap_option as po ON po.op_id=cover_paper
LEFT JOIN pap_option as po1 ON po1.op_id=inside_paper              
WHERE size_id=:sid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":sid",$sid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_quote_kv($field){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
                    $field,po.op_name
                    FROM pap_quotation
                    LEFT JOIN pap_option AS po ON po.op_id=$field
                    GROUP BY $field
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_quote_month($format="%m%Y"){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
                    DATE_FORMAT(created,'$format'),DATE_FORMAT(created,'%b-%Y')
                    FROM pap_quotation
                    GROUP BY DATE_FORMAT(created,'%b-%Y')
                    ORDER BY created DESC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_quote_info($qid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
pq.*,
op.op_name AS cat,
CONCAT(size_name,' (',size_height,'x',size_width,')') AS size,
amount,
user.user_login,sale.user_id
FROM pap_quotation AS pq
LEFT JOIN pap_option AS op ON op.op_id=pq.cat_id
LEFT JOIN pap_size ON size_id=job_size_id
LEFT JOIN pap_sale_cus AS sale ON sale.cus_id=pq.customer_id
LEFT JOIN pap_user AS user ON user.user_id=sale.user_id
WHERE quote_id=:qid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":qid",$qid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_quote_allinfo($qid){
        try {
$sql = <<<END_OF_TEXT
SELECT 
pq.*,
cus.customer_name
FROM pap_quotation AS pq
LEFT JOIN pap_customer AS cus ON cus.customer_id=pq.customer_id
WHERE quote_id=:qid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":qid",$qid);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            //meta
            $res += $this->get_meta("pap_quote_meta","quote_id",$qid);
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
        
    }
    public function check_cus_code($taxid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
pt.slug,CAST(REPLACE(customer_code,:slug,'') AS UNSIGNED)
FROM pap_customer AS cus
LEFT JOIN pap_customer_cat AS cat ON cat.customer_id=cus.customer_id
LEFT JOIN pap_term AS pt ON pt.id=cat.tax_id
WHERE pt.slug=:slug
ORDER BY CAST(REPLACE(customer_code,:slug,'') AS UNSIGNED) DESC
LIMIT 1
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":slug",$slug);
            
            $digit = $this->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
            $term = $this->get_info("pap_term","id",$taxid);
            $slug = $term['slug'];
            $stmt->execute();
            if($stmt->rowcount()>0){
                $info = $stmt->fetch(PDO::FETCH_NUM);
                $code = $info[1]+1;
                $res = $slug.sprintf("%0".$digit['c_digit']."s",$code);
            } else {
                $res = $slug.sprintf("%0".$digit['c_digit']."s",1);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_contact($cid,$cat=null){
        try {
            $cat_sql = (isset($cat)?"AND contact_cat=:ct_cat":"");
            $sql = <<<END_OF_TEXT
SELECT 
CONCAT("<span class='cus-ct' ctinfo='",contact_id,";",contact_name,";",contact_email,";",contact_tel,";",contact_remark,"'>",contact_name,"</span>")contact_name,
contact_tel
FROM pap_contact
WHERE customer_id=:cid $cat_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":cid",$cid);
            (isset($cat)?$stmt->bindParam(":ct_cat",$cat):"");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_sup_ct($sid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
CONCAT("<span class='sup-ct' ctinfo='",id,";",name,";",email,";",tel,";",remark,"'>",name,"</span>"),
tel
FROM pap_supplier_ct
WHERE supplier_id=:sid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":sid",$sid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_process_keypair(){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
process_cat_id,
process_id,
process_name
FROM pap_process
WHERE process_id NOT IN (26,37,38,39)
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $cat = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_NUM);
            foreach($cat as $k=>$v){
                $res[$k] = array();
                foreach($v as $vv){
                    $res[$k][$vv[0]] = $vv[1];
                }
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
/*=======================================================   CHECK CODE   =================================================================*/
    public function check_supplier_code($taxid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
pt.slug,CAST(REPLACE(sup.code,:slug,'') AS UNSIGNED)
FROM pap_supplier AS sup
LEFT JOIN pap_supplier_cat AS cat ON cat.supplier_id=sup.id
LEFT JOIN pap_term AS pt ON pt.id=cat.tax_id
WHERE pt.slug=:slug
ORDER BY CAST(REPLACE(sup.code,:slug,'') AS UNSIGNED) DESC
LIMIT 1
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":slug",$slug);
            
            $digit = $this->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
            $term = $this->get_info("pap_term","id",$taxid);
            $slug = $term['slug'];
            $stmt->execute();
            if($stmt->rowcount()>0){
                $info = $stmt->fetch(PDO::FETCH_NUM);
                $code = $info[1]+1;
                $res = $slug.sprintf("%0".$digit['s_digit']."s",$code);
            } else {
                $res = $slug.sprintf("%0".$digit['s_digit']."s",1);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_deli_code($ddate){
        try {
            $cinfo = $this->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
            $qr = explode(",",$cinfo['rno_deli']);
            $pre = $qr[0];
            $date = $qr[1];
            $digit = $qr[2];
            $conn = $qr[3];
            $sql = <<<END_OF_TEXT
SELECT 
no,DATE_FORMAT(date,"$date") AS date
FROM pap_delivery
WHERE DATE_FORMAT(date,"$date")=DATE_FORMAT('$ddate',"$date")
ORDER BY id DESC
LIMIT 1
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            if($stmt->rowCount()>0){
                $info = $stmt->fetch(PDO::FETCH_ASSOC);
                $qrun = $pre.$conn.$info['date'].$conn;
                $next = (int)str_replace($qrun,"",$info['no'])+1;
                $res = $qrun.sprintf("%0".$digit."s",$next);
            } else {
                $month = $this->conn->query("SELECT DATE_FORMAT('$ddate','$date')");
                $tmonth = $month->fetch(PDO::FETCH_NUM);
                $res = $pre.$conn.$tmonth[0].$conn.sprintf("%0".$digit."s",1);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_bill_code($ddate){
        try {
            $cinfo = $this->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
            $qr = explode(",",$cinfo['rno_bill']);
            $pre = $qr[0];
            $date = $qr[1];
            $digit = $qr[2];
            $conn = $qr[3];
            $sql = <<<END_OF_TEXT
SELECT 
no,DATE_FORMAT(date,"$date") AS date
FROM pap_pbill
WHERE DATE_FORMAT(date,"$date")=DATE_FORMAT('$ddate',"$date")
ORDER BY id DESC
LIMIT 1
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            if($stmt->rowCount()>0){
                $info = $stmt->fetch(PDO::FETCH_ASSOC);
                $qrun = $pre.$conn.$info['date'].$conn;
                $next = (int)str_replace($qrun,"",$info['no'])+1;
                $res = $qrun.sprintf("%0".$digit."s",$next);
            } else {
                $month = $this->conn->query("SELECT DATE_FORMAT('$ddate','$date')");
                $tmonth = $month->fetch(PDO::FETCH_NUM);
                $res = $pre.$conn.$tmonth[0].$conn.sprintf("%0".$digit."s",1);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_inv_code($ddate){
        try {
            $cinfo = $this->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
            $qr = explode(",",$cinfo['rno_invoice']);
            $pre = $qr[0];
            $date = $qr[1];
            $digit = $qr[2];
            $conn = $qr[3];
            $sql = <<<END_OF_TEXT
SELECT 
no,DATE_FORMAT(date,"$date") AS date
FROM pap_invoice
WHERE DATE_FORMAT(date,"$date")=DATE_FORMAT('$ddate',"$date")
ORDER BY id DESC
LIMIT 1
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            if($stmt->rowCount()>0){
                $info = $stmt->fetch(PDO::FETCH_ASSOC);
                $qrun = $pre.$conn.$info['date'].$conn;
                $next = (int)str_replace($qrun,"",$info['no'])+1;
                $res = $qrun.sprintf("%0".$digit."s",$next);
            } else {
                $month = $this->conn->query("SELECT DATE_FORMAT('$ddate','$date')");
                $tmonth = $month->fetch(PDO::FETCH_NUM);
                $res = $pre.$conn.$tmonth[0].$conn.sprintf("%0".$digit."s",1);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_rc_code($ddate){
        try {
            $cinfo = $this->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
            $qr = explode(",",$cinfo['rno_rc']);
            $pre = $qr[0];
            $date = $qr[1];
            $digit = $qr[2];
            $conn = $qr[3];
            $sql = <<<END_OF_TEXT
SELECT 
no,DATE_FORMAT(date,"$date") AS date
FROM pap_rc
WHERE DATE_FORMAT(date,"$date")=DATE_FORMAT('$ddate',"$date")
ORDER BY id DESC
LIMIT 1
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            if($stmt->rowCount()>0){
                $info = $stmt->fetch(PDO::FETCH_ASSOC);
                $qrun = $pre.$conn.$info['date'].$conn;
                $next = (int)str_replace($qrun,"",$info['no'])+1;
                $res = $qrun.sprintf("%0".$digit."s",$next);
            } else {
                $month = $this->conn->query("SELECT DATE_FORMAT('$ddate','$date')");
                $tmonth = $month->fetch(PDO::FETCH_NUM);
                $res = $pre.$conn.$tmonth[0].$conn.sprintf("%0".$digit."s",1);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_quote_no(){
        try {
            $cinfo = $this->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
            $qr = explode(",",$cinfo['rno_quote']);
            $pre = $qr[0];
            $date = $qr[1];
            $digit = $qr[2];
            $conn = $qr[3];
            $sql = <<<END_OF_TEXT
SELECT 
                    quote_no,DATE_FORMAT(created,"$date") AS date
                    FROM pap_quotation
                    WHERE DATE_FORMAT(created,"$date")=DATE_FORMAT(now(),"$date")
                    ORDER BY quote_id DESC
                    LIMIT 1
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            if($stmt->rowCount()>0){
                $info = $stmt->fetch(PDO::FETCH_ASSOC);
                $qrun = $pre.$conn.$info['date'].$conn;
                $next = (int)str_replace($qrun,"",$info['quote_no'])+1;
                $res = $qrun.sprintf("%0".$digit."s",$next);
            } else {
                $month = $this->conn->query("SELECT DATE_FORMAT(now(),'$date')");
                $tmonth = $month->fetch(PDO::FETCH_NUM);
                $res = $pre.$conn.$tmonth[0].$conn.sprintf("%0".$digit."s",1);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_matpo_no(){
        try {
            $cinfo = $this->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
            $qr = explode(",",$cinfo['rno_matpo']);
            $pre = $qr[0];
            $date = $qr[1];
            $digit = $qr[2];
            $conn = $qr[3];
            $sql = <<<END_OF_TEXT
SELECT 
po_code,DATE_FORMAT(po_created,"$date") AS date
FROM pap_mat_po
WHERE DATE_FORMAT(po_created,"$date")=DATE_FORMAT(now(),"$date")
ORDER BY po_id DESC
LIMIT 1
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            if($stmt->rowCount()>0){
                $info = $stmt->fetch(PDO::FETCH_ASSOC);
                $qrun = $pre.$conn.$info['date'].$conn;
                $next = (int)str_replace($qrun,"",$info['po_code'])+1;
                $res = $qrun.sprintf("%0".$digit."s",$next);
            } else {
                $month = $this->conn->query("SELECT DATE_FORMAT(now(),'$date')");
                $tmonth = $month->fetch(PDO::FETCH_NUM);
                $res = $pre.$conn.$tmonth[0].$conn.sprintf("%0".$digit."s",1);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_processpo_no(){
        try {
            $cinfo = $this->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
            $qr = explode(",",$cinfo['rno_prodpo']);
            $pre = $qr[0];
            $date = $qr[1];
            $digit = $qr[2];
            $conn = $qr[3];
            $sql = <<<END_OF_TEXT
SELECT 
po_code,DATE_FORMAT(po_created,"$date") AS date
FROM pap_process_po
WHERE DATE_FORMAT(po_created,"$date")=DATE_FORMAT(now(),"$date")
ORDER BY po_id DESC
LIMIT 1
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            if($stmt->rowCount()>0){
                $info = $stmt->fetch(PDO::FETCH_ASSOC);
                $qrun = $pre.$conn.$info['date'].$conn;
                $next = (int)str_replace($qrun,"",$info['po_code'])+1;
                $res = $qrun.sprintf("%0".$digit."s",$next);
            } else {
                $month = $this->conn->query("SELECT DATE_FORMAT(now(),'$date')");
                $tmonth = $month->fetch(PDO::FETCH_NUM);
                $res = $pre.$conn.$tmonth[0].$conn.sprintf("%0".$digit."s",1);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_order_no(){
        try {
            $cinfo = $this->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
            $qr = explode(",",$cinfo['rno_order']);
            $pre = $qr[0];
            $date = $qr[1];
            $digit = $qr[2];
            $conn = $qr[3];
            $sql = <<<END_OF_TEXT
SELECT 
                    order_no,DATE_FORMAT(created,"$date") AS date
                    FROM pap_order
                    WHERE DATE_FORMAT(created,"$date")=DATE_FORMAT(now(),"$date")
                    ORDER BY order_id DESC
                    LIMIT 1
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            if($stmt->rowCount()>0){
                $info = $stmt->fetch(PDO::FETCH_ASSOC);
                $qrun = $pre.$conn.$info['date'].$conn;
                $next = (int)str_replace($qrun,"",$info['order_no'])+1;
                $res = $qrun.sprintf("%0".$digit."s",$next);
            } else {
                $month = $this->conn->query("SELECT DATE_FORMAT(now(),'$date')");
                $tmonth = $month->fetch(PDO::FETCH_NUM);
                $res = $pre.$conn.$tmonth[0].$conn.sprintf("%0".$digit."s",1);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
/*=======================================================      FIND      =================================================================*/
    public function find_customer($find,$uid=null){
        try {
            $f = "%".$find."%";
            $user = (isset($uid)?" AND sale.user_id=$uid":"");
            $sql = <<<END_OF_TEXT
SELECT 
CONCAT(customer_code,'-',customer_name),customer_id 
FROM pap_customer AS cus
LEFT JOIN pap_sale_cus AS sale ON sale.cus_id=customer_id
WHERE CONCAT(customer_code,'-',customer_name) like :find
$user
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":find",$f);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function find_size($find){
        try {
            $f = "%".$find."%";
            $sql = <<<END_OF_TEXT
SELECT 
CONCAT(size_name,"(",size_height,"x",size_width,")"),size_id
FROM pap_size
WHERE CONCAT(size_name,"(",size_height,"x",size_width,")") like :find
ORDER BY size_height ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":find",$f);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function find_job($find){
        try {
            $f = "%".$find."%";
            $stmt = $this->conn->prepare("SELECT CONCAT(order_no,':',qq.name),order_id FROM pap_order AS od LEFT JOIN pap_quotation AS qq ON qq.quote_id=od.quote_id WHERE CONCAT(order_no,':',qq.name) like :find");
            $stmt->bindParam(":find",$f);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function find_uemail($find){
        try {
            $f = "%".$find."%";
            $stmt = $this->conn->prepare("SELECT CONCAT(user_login,':',user_email),user_email FROM pap_user WHERE CONCAT(user_login,':',user_email) like :find");
            $stmt->bindParam(":find",$f);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
     public function find_quote($find){
        try {
            $f = "%".$find."%";
            $stmt = $this->conn->prepare("SELECT CONCAT(quote_no,':',name),quote_id FROM pap_quotation WHERE CONCAT(quote_no,':',name) like :find");
            $stmt->bindParam(":find",$f);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function find_mat($find){
        try {
            $f = "%".$find."%";
            $stmt = $this->conn->prepare("SELECT mat_name,mat_id FROM pap_mat WHERE mat_name like :find");
            $stmt->bindParam(":find",$f);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function find_process($find){
        try {
            $f = "%".$find."%";
            $stmt = $this->conn->prepare("SELECT process_name,process_id FROM pap_process WHERE process_source=1 AND process_name like :find");
            $stmt->bindParam(":find",$f);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function find_cproid($find){
        try {
            $f = "%".$find."%";
            $sql = <<<END_OF_TEXT
SELECT 
CONCAT("(",comp.name,") ",cpro.name,";",job.order_no,":",quo.name),cpro.id
FROM pap_comp_process AS cpro
LEFT JOIN pap_order_comp AS comp ON comp.id=cpro.comp_id
LEFT JOIN pap_order AS job ON job.order_id=comp.order_id
LEFT JOIN pap_quotation AS quo ON quo.quote_id=job.quote_id
LEFT JOIN pap_process AS pro ON pro.process_id=cpro.process_id
WHERE process_source=1 AND CONCAT(job.order_no,":",quo.name) like :find
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":find",$f);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function filter_paper($sid){
        try {
            __autoloada("form");
            $form = new myform();
            $sql = <<<END_OF_TEXT
SELECT 
cover_paper,po.op_name AS cover_size,
inside_paper,po1.op_name AS inside_size
FROM pap_size
LEFT JOIN pap_option AS po ON po.op_id=cover_paper
LEFT JOIN pap_option AS po1 ON po1.op_id=inside_paper
WHERE size_id=:sid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":sid",$sid);
            $stmt->execute();
            $size = $stmt->fetch(PDO::FETCH_ASSOC);
            $res[0] = array($size['cover_size'],$size['inside_size']);
            $res[3] = array($size['cover_paper'],$size['inside_paper']);
            //cover
            $c_ptype = array("0"=>"--กระดาษ--") + $this->get_paper_keypair("mat_type", $size['cover_paper']);
            $res[1] = $form->show_select("paper_type",$c_ptype,"label-3070","กระดาษ",null,"","paper_type[]");
            //inside 
            $i_ptype = array("0"=>"--กระดาษ--") + $this->get_paper_keypair("mat_type", $size['inside_paper']);
            $res[2] = $form->show_select("paper_type_n",$i_ptype,"label-3070 in_ptype","กระดาษ",null,"","paper_type[]");
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_userpair($type=null){
        try {
            $tsql = (isset($type)?"WHERE um.meta_value=:type":"");
            $sql = <<<END_OF_TEXT
SELECT
                    uu.user_id,user_login
                    FROM pap_user AS uu
                    LEFT JOIN pap_usermeta AS um ON um.user_id=uu.user_id AND um.meta_key='user_auth'
                    $tsql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($type)){
                $stmt->bindParam(":type",$type);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function add_comp_process($data){
        try {
            $sql = <<<END_OF_TEXT
INSERT INTO `pap_comp_process`(`id`, `comp_id`, `process_id`, `name`, `volume`, `est_time_hour`, `machine_id`, `result`, `plan_start`, `plan_end`, `start`, `end`, `remark`) VALUES (null,:cid,:pid,:name,:vol,:time,null,null,null,null,null,null,null)
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":cid",$cid);
            $stmt->bindParam(":pid",$pid);
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":vol",$vol);
            $stmt->bindParam(":time",$time);
            foreach($data as $k=>$v){
                $cid = $v[0];
                $pid = $v[1];
                $name = $v[2];
                $vol = $v[3];
                $time = $v[4];
                $stmt->execute();
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function update_mat_cost($sid,$amid,$acost){
        try {
            $stmt = $this->conn->prepare("DELETE FROM pap_mat_cost WHERE supplier_id=:sid AND mat_id=:mid");
            $stmt->bindParam(":sid",$sid);
            $stmt->bindParam(":mid",$mid);
            $stmt1 = $this->conn->prepare("INSERT INTO pap_mat_cost VALUES(:mid,:sid,:cost)");
            $stmt1->bindParam(":sid",$sid);
            $stmt1->bindParam(":mid",$mid);
            $stmt1->bindParam(":cost",$cost);
            for($i=0;$i<count($amid);$i++){
                $mid = $amid[$i];
                $stmt->execute();
                $cost = $acost[$i];
                $stmt1->execute();
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function porder_info($oid,$mid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
CONCAT(po.order_no,":",quo.name)
FROM pap_order AS po
LEFT JOIN pap_quotation AS quo ON quo.quote_id=po.quote_id
WHERE order_id=:oid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NUM)){
                $res['order'] = $row[0];
            }
            $sql = <<<END_OF_TEXT
SELECT 
mat_name,mat_std_cost
FROM pap_mat
WHERE mat_id=:mid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":mid",$mid);
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NUM)){
                $res['mname'] = $row[0];
                $res['mcost'] = $row[1];
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_req_vs_delivery($oid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
GREATEST(com.paper_use-IFNULL(SUM(ddt.qty),0),0) AS rem
FROM pap_order_comp AS com
LEFT JOIN pap_mat_po_detail AS dt ON dt.mat_id=com.paper_id AND dt.order_ref=com.order_id
LEFT JOIN pap_mat_delivery_dt AS ddt ON ddt.dt_id=dt.id
WHERE com.order_id=:oid AND paper_id IS NOT NULL
GROUP BY com.order_id,com.paper_id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            $remain = $stmt->fetchAll(PDO::FETCH_COLUMN,0);
            if(array_sum($remain)==0){
                //all paper deliveried  => update order paper_delivery date
                $info = $this->get_info("pap_order", "order_id", $oid);
                if($info['status']==5){
                    $this->update_data("pap_order", "order_id", $oid, array("paper_received"=>pap_now(),"status"=>8));
                } else {
                    $this->update_data("pap_order", "order_id", $oid, array("paper_received"=>pap_now()));
                }
            } else {
                $this->update_data("pap_order", "order_id", $oid, array("paper_received"=>null));
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function find_paper_plan($oid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
DATE_FORMAT(MAX(po.po_delivery_plan),'%Y-%m-%d')
FROM pap_mat_po_detail AS dt
LEFT JOIN pap_mat_po AS po ON po.po_id=dt.po_id
WHERE order_ref=:oid
GROUP BY order_ref
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_NUM)[0];
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_po_vs_delivery($poid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
IFNULL(SUM(ddt.qty),0) AS dqty,mat_qty-IFNULL(SUM(ddt.qty),0) AS rem
FROM pap_mat_po_detail AS dt
LEFT JOIN pap_mat_delivery_dt AS ddt ON ddt.dt_id=dt.id
WHERE dt.po_id=:poid
GROUP BY dt.id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":poid",$poid);
            $stmt->execute();
            $sum_deli = 0;
            $sum_rem = 0;
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $sum_deli += $row['dqty'];
                $sum_rem += $row['rem'];
            }
            if($sum_deli==0){
                //update po status-> 3 สั่งซื้อแล้ว
                $this->update_data("pap_mat_po", "po_id", $poid, array("po_status"=>3,"po_deliveried"=>null));
            } else {
                if($sum_rem==0){
                    //deliveried
                    $this->update_data("pap_mat_po", "po_id", $poid, array("po_status"=>5,"po_deliveried"=>pap_now()));
                } else {
                    //partial deliveried
                    $this->update_data("pap_mat_po", "po_id", $poid, array("po_status"=>4,"po_deliveried"=>null));
                } 
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_ppo_vs_delivery($poid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
IFNULL(SUM(ddt.qty),0) AS dqty,dt.qty-IFNULL(SUM(ddt.qty),0) AS rem
FROM pap_pro_po_dt AS dt
LEFT JOIN pap_wip_delivery_dt AS ddt ON ddt.dt_id=dt.id
WHERE dt.po_id=:poid
GROUP BY dt.id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":poid",$poid);
            $stmt->execute();
            $sum_deli = 0;
            $sum_rem = 0;
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $sum_deli += $row['dqty'];
                $sum_rem += $row['rem'];
            }
            if($sum_deli==0){
                //update po status-> 3 สั่งซื้อแล้ว
                $this->update_data("pap_process_po", "po_id", $poid, array("po_status"=>3,"po_deliveried"=>null));
            } else {
                if($sum_rem==0){
                    //deliveried
                    $this->update_data("pap_process_po", "po_id", $poid, array("po_status"=>5,"po_deliveried"=>pap_now()));
                } else {
                    //partial deliveried
                    $this->update_data("pap_process_po", "po_id", $poid, array("po_status"=>4,"po_deliveried"=>null));
                } 
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_job_due(){
        try {
            $sql = <<<END_OF_TEXT
SELECT DISTINCT
quo.plan_delivery,DATE_FORMAT(quo.plan_delivery,'%d-%b')
FROM pap_order AS job
LEFT JOIN pap_quotation AS quo ON quo.quote_id=job.quote_id
WHERE job.status BETWEEN 1 AND 11
ORDER BY quo.plan_delivery ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_job_remain_deli($oid,$tdid=null){
        try {
            $exclude = (isset($tdid)?" AND deli.temp_deli_id<>$tdid":"");
            $sql = <<<END_OF_TEXT
SELECT 
job.order_no,pq.name,pq.amount,IFNULL(SUM(deli.qty),0) AS deli,
pq.customer_id
FROM pap_order AS job
LEFT JOIN pap_quotation AS pq ON pq.quote_id=job.quote_id
LEFT JOIN pap_temp_dt AS deli ON deli.order_id=job.order_id $exclude
WHERE job.order_id=:oid
GROUP BY job.order_id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_job_in_deli($arroid){
        try {
            $oid = implode(",",$arroid);
            $sql = <<<END_OF_TEXT
SELECT 
deli_id
FROM pap_delivery_dt
WHERE order_id IN ($oid) 
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
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
    public function check_next_tdeli_code($oid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
deli.no AS delino,tdeli.no AS tno,deli.id
FROM pap_delivery_dt AS dt
LEFT JOIN pap_delivery AS deli ON deli.id=dt.deli_id
LEFT JOIN pap_temp_deli AS tdeli ON tdeli.deli_id=deli.id
WHERE dt.order_id=:oid
ORDER BY tdeli.id DESC
LIMIT 1
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            $temp = $stmt->fetch(PDO::FETCH_ASSOC);
            $lastcode = $temp['tno'];
            $maincode = $temp['delino'];
            $next = (int)str_replace($maincode."-","",$lastcode)+1;
            return array($temp['id'],$maincode."-".$next);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_acid_from_oid($stroid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
GROUP_CONCAT(customer_id)
FROM pap_order AS job
LEFT JOIN pap_quotation AS quo ON quo.quote_id=job.quote_id
WHERE order_id IN ($stroid)
GROUP BY customer_id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_NUM)[0];
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_job_schedule($year,$month){
        try {
            $mm = new DateTime($year.$month."01",new DateTimeZone("Asia/Bangkok"));
            $mm->sub(new DateInterval("P1M"));
            $st = $mm->format("Ym");
            $mm->add(new DateInterval("P2M"));
            $en = $mm->format("Ym");
            $sql = <<<END_OF_TEXT
SELECT
DATE_FORMAT(quo.plan_delivery,"%Y%m%d") AS date,
COUNT(job.order_id) AS num,
GROUP_CONCAT(job.order_id) AS id,
GROUP_CONCAT(CONCAT(job.order_no,":",quo.name)) AS name
FROM pap_order AS job
LEFT JOIN pap_quotation AS quo ON quo.quote_id=job.quote_id
WHERE DATE_FORMAT(quo.plan_delivery,"%Y%m") BETWEEN :st AND :en
GROUP BY DATE_FORMAT(quo.plan_delivery,"%Y%m%d")
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":st",$st);
            $stmt->bindParam(":en",$en);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $res1 = array();
            foreach($res as $k=>$v){
                $res1[$v['date']] = array($v['num'],$v['id'],$v['name']);
            }
            return $res1;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_job_status($oid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
comp.name AS comp,cat.name AS process,
cpro.name AS detail,FORMAT(cpro.volume,0),cpro.est_time_hour,
IF(cpro.machine_id=0,"จ้างผลิต",IFNULL(mach.name,"")),
start,end,
result,remark,
cpro.id AS cproid
FROM pap_order_comp AS comp 
LEFT JOIN pap_comp_process AS cpro ON cpro.comp_id=comp.id
LEFT JOIN pap_process AS pro ON pro.process_id=cpro.process_id
LEFT JOIN pap_process_cat AS cat ON cat.id=pro.process_cat_id
LEFT JOIN pap_machine AS mach ON mach.id=cpro.machine_id
WHERE comp.order_id=:oid AND process_cat_id BETWEEN 3 AND 11
ORDER BY cpro.comp_id ASC, process_cat_id ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($res as $k=>$v){
                unset($res[$k]['comp']);
                unset($res[$k]['cproid']);
                $cproid = $v['cproid'];
                $start = (isset($v['start'])?  thai_dt($v['start']):"");
                $end = (isset($v['end'])?  thai_dt($v['end']):"");
                $res[$k]['start'] = "<a href='' title='Update' class='status-start icon-page-edit' cproid='$cproid'></a><br/>$start";
                $res[$k]['end'] = "<a href='' title='Update' class='status-end icon-page-edit' cproid='$cproid'></a><br/>$end";
                if(isset($res1[$v['comp']])){
                    array_push($res1[$v['comp']],$res[$k]);
                } else {
                    $res1[$v['comp']] = array($res[$k]);
                }
            }
            return $res1;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_job_comp_process($oid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
comp.name AS comp,comp.id,cpro.id AS cproid,cat.name AS process,
cpro.name AS detail,FORMAT(cpro.volume,0),FORMAT(cpro.est_time_hour,2),
IF(cpro.machine_id IS NOT NULL,
    CONCAT("<a href='' title='ปรับแผน' class='myplan-edit' type='",comp.type,"' pid='",cpro.process_id,"' source='",pro.process_source,"' info='",CONCAT_WS(";",cpro.name,cpro.volume,cpro.est_time_hour,cpro.id,cpro.plan_start),"'>",IF(cpro.machine_id=0,'สั่งผลิต',mach.name),"</a>"),
    CONCAT("<a href='' title='ใส่แผน' class='myplan-edit icon-plus-square' type='",comp.type,"' pid='",cpro.process_id,"' source='",pro.process_source,"' info='",CONCAT_WS(";",cpro.name,cpro.volume,cpro.est_time_hour,cpro.id),"'></a>")
),
plan_start,plan_end
FROM pap_order_comp AS comp 
LEFT JOIN pap_comp_process AS cpro ON cpro.comp_id=comp.id
LEFT JOIN pap_process AS pro ON pro.process_id=cpro.process_id
LEFT JOIN pap_process_cat AS cat ON cat.id=pro.process_cat_id
LEFT JOIN pap_machine AS mach ON mach.id=cpro.machine_id
WHERE comp.order_id=:oid AND process_cat_id BETWEEN 3 AND 11
ORDER BY cpro.comp_id ASC, process_cat_id ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($res as $k=>$v){
                unset($res[$k]['comp']);
                unset($res[$k]['id']);
                unset($res[$k]['cproid']);
                $res[$k]['plan_start'] = (is_null($v['plan_start'])?"":thai_dt($v['plan_start']));
                $res[$k]['plan_end'] = (is_null($v['plan_end'])?"":thai_dt($v['plan_end']));
                $res[$k]['process'] = "<a href='production.php?oid=$oid&cproid=".$v['cproid']."' title='Edit' class='icon-page-edit'></a>".$v['process'];
                $key = $v['comp']."<br/><a href='production.php?action=add&oid=$oid&compid=".$v['id']."' title='Add process' class='icon-plus-square'></a>";
                if(isset($res1[$key])){
                    array_push($res1[$key],$res[$k]);
                } else {
                    $res1[$key] = array($res[$k]);
                }
            }
            return $res1;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_mach($acat=null){
        try {
            $filter = (isset($acat)?"WHERE cat.id IN ($acat)":"");
            $sql = <<<END_OF_TEXT
SELECT 
mach.id,mach.name
FROM pap_machine AS mach
LEFT JOIN pap_process AS pro ON pro.process_id=mach.process_id
LEFT JOIN pap_process_cat AS cat ON pro.process_cat_id=cat.id
$filter
ORDER BY cat.id ASC, mach.name ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return array(0=>"สั่งผลิต") + $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_schedule($date,$acat=null){
        try {
            //$filter = "WHERE cpro.machine_id IS NOT NULL";
            //$filter .= (isset($acat)?" AND cat.id IN ($acat)":"");
            $st = new DateTime($date,new DateTimeZone("Asia/Bangkok"));
            $start = $st->format("Y-m-d H:i:s");
            $en = new DateTime($date,new DateTimeZone("Asia/Bangkok"));
            $en->add(new DateInterval("P3D"));
            $end = $en->format("Y-m-d H:i:s");
            $sql = <<<END_OF_TEXT
SELECT 
cpro.machine_id AS mcid,com.order_id,cpro.id,
IF(plan_start<:st,est_time_hour-ROUND(TIMESTAMPDIFF(MINUTE,plan_start,:st)/60,2),est_time_hour) AS est_time_hour,
IF(plan_start<:st,:st,plan_start) AS plan_start,
CONCAT_WS("<br/>",job.order_no,quo.name,com.name,cpro.name) AS name
FROM pap_comp_process as cpro
LEFT JOIN pap_order_comp AS com ON com.id=cpro.comp_id
LEFT JOIN pap_order AS job ON job.order_id=com.order_id
LEFT JOIN pap_quotation AS quo ON quo.quote_id=job.quote_id
WHERE cpro.machine_id IS NOT NULL AND plan_end>:st AND plan_start<:en
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":st",$start);
            $stmt->bindParam(":en",$end);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $res1 = array();
            foreach($res as $k=>$v){
                if(!isset($res1[$v['mcid']])){
                    $res1[$v['mcid']] = array();
                }
                array_push($res1[$v['mcid']],array($v['plan_start'],$v['order_id'],$v['id'],$v['est_time_hour'],$v['name']));
            }
            return $res1;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_schedule($mid,$st,$en,$cpid=null){
        try {
            $filter = (isset($cpid)?" AND id<>$cpid":"");
            $sql = <<<END_OF_TEXT
SELECT 
id
FROM pap_comp_process 
WHERE machine_id=:mid $filter AND plan_end>:st AND plan_start<:en
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":mid",$mid);
            $stmt->bindParam(":st",$st);
            $stmt->bindParam(":en",$en);
            $stmt->execute();
            if($stmt->rowCount()>0){
                return false;
            } else {
                return true;
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_comp_schedule($cpid,$st,$en){
        try {

            $sql = <<<END_OF_TEXT
SELECT 
cpro.id,cpro1.id
FROM pap_comp_process AS cpro
LEFT JOIN pap_comp_process AS cpro1 ON cpro1.comp_id=cpro.comp_id
WHERE cpro.id=:cpid AND cpro1.id<>:cpid AND cpro1.process_id<>cpro.process_id AND cpro1.plan_end>:st AND cpro1.plan_start<:en
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":cpid",$cpid);
            $stmt->bindParam(":pid",$pid);
            $stmt->bindParam(":st",$st);
            $stmt->bindParam(":en",$en);
            $stmt->execute();
            if($stmt->rowCount()>0){
                return false;
            } else {
                return true;
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_mcomp_schedule($oid,$st,$en,$type){
        try {
            if($type==9){
                $where = "WHERE com.order_id=:oid AND com.type<>9 AND cpro.plan_end>:st AND cpro.plan_start<:en";
            } else {
                $where = "WHERE com.order_id=:oid AND com.type=9 AND cpro.plan_end>:st AND cpro.plan_start<:en";
            }
            $sql = <<<END_OF_TEXT
SELECT 
cpro.id,com.id AS compid,com.type
FROM pap_comp_process AS cpro
LEFT JOIN pap_order_comp AS com ON com.id=cpro.comp_id
$where
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->bindParam(":st",$st);
            $stmt->bindParam(":en",$en);
            $stmt->execute();
            if($stmt->rowCount()>0){
                return false;
            } else {
                return true;
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_comps_recal($compid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
comp.*,quo.amount,quo.cat_id
FROM pap_order_comp AS comp
LEFT JOIN pap_order AS job ON job.order_id=comp.order_id
LEFT JOIn pap_quotation AS quo ON quo.quote_id=job.quote_id
WHERE comp.id=:compid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":compid",$compid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_max_comps_status($oid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
MAX(comp.status)
FROM pap_order_comp AS comp
WHERE order_id=:oid
GROUP BY order_id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_NUM)[0];
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_last_comps_status($oid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
MAX(pro.process_cat_id)
FROM pap_order_comp AS comp
LEFT JOIN pap_comp_process AS cpro ON cpro.comp_id=comp.id
LEFT JOIN pap_process AS pro ON pro.process_id=cpro.process_id
WHERE order_id=:oid
GROUP BY order_id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_NUM)[0];
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_req_vs_po($oid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
GREATEST(com.paper_use-IFNULL(SUM(mpo.mat_qty),0),0) AS rem
FROM pap_order_comp AS com
LEFT JOIN pap_mat_po_detail AS mpo ON mpo.order_ref=com.order_id AND mpo.mat_id=com.paper_id
WHERE com.order_id=:oid AND paper_id IS NOT NULL
GROUP BY com.order_id,com.paper_id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            $remain = $stmt->fetchAll(PDO::FETCH_COLUMN,0);
            if(array_sum($remain)==0){
                //all planed  => update order paper_plan date
                $paper_plan = $this->find_paper_plan($oid);
                $this->update_data("pap_order", "order_id", $oid, array("paper_plan"=>$paper_plan));
            } else {
                $this->update_data("pap_order", "order_id", $oid, array("paper_plan"=>null));
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_job_price($oid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
CONCAT(job.order_no,":",quo.name) AS jname,quo.q_price,meta.meta_value AS discount,
quo.credit,quo.customer_id,quo.cat_id
FROM pap_order AS job
LEFT JOIN pap_quotation AS quo ON quo.quote_id=job.quote_id
LEFT JOIN pap_quote_meta AS meta ON meta.quote_id=job.quote_id AND meta_key='discount'
WHERE job.order_id=:oid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_mach_info($mid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
mach.name AS เครื่องจักร,
mach.cap AS กำลังการผลิต,
mach.setup_min AS เวลาตั้งเครื่อง,
user_login AS ผู้ควบคุม
FROM pap_machine AS mach
LEFT JOIN pap_mach_user AS mu ON mu.mach_id=mach.id
LEFT JOIN pap_user AS user ON user.user_id=mu.user_id
WHERE mach.id = :mid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":mid",$mid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_job_result_by_mach($mid,$date){
        try {
            $filter = " AND DATE_FORMAT(plan_start,'%Y-%m-%d')=:date";
            $sql = <<<END_OF_TEXT
SELECT
CONCAT(order_no,":",quo.name),
cpro.name,cpro.volume,cpro.est_time_hour,plan_start,plan_end,start,end,result,cpro.remark,
cpro.id AS cproid
FROM pap_comp_process AS cpro
LEFT JOIN pap_order_comp AS comp ON comp.id=cpro.comp_id
LEFT JOIN pap_order AS job ON job.order_id=comp.order_id
LEFT JOIN pap_quotation AS quo ON quo.quote_id=job.quote_id
WHERE machine_id=:mid $filter
ORDER BY plan_start ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":mid",$mid);
            $stmt->bindParam(":date",$date);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($res as $k=>$v){
                unset($res[$k]['cproid']);
                $cproid = $v['cproid'];
                $res[$k]['plan_start'] = thai_dt($v['plan_start']);
                $res[$k]['plan_end'] = thai_dt($v['plan_end']);
                $start = (isset($v['start'])?  thai_dt($v['start']):"");
                $end = (isset($v['end'])?  thai_dt($v['end']):"");
                $res[$k]['start'] = "<a href='' title='Update' class='status-start icon-page-edit' cproid='$cproid'></a><br/>$start";
                $res[$k]['end'] = "<a href='' title='Update' class='status-end icon-page-edit' cproid='$cproid'></a><br/>$end";
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_paper_size(){
        try {
            $sql = <<<END_OF_TEXT
SELECT op_id,op_name,op_value AS psize FROM pap_option WHERE op_type='paper_size'
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
}

