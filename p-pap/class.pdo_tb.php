<?php
class tbPDO{
    private $conn;
    public function __construct() {
        $this->conn = dbConnect(DB_PAP);
    }
    public function view_option($type,$auth=3){
        try {
            $edit = "";
            $value_sql = ($type=="role_auth"?"":",op_value");
            $sort = ($type=="paper_weight"?"CAST(op_name AS DECIMAL) ASC":"op_name ASC");
            if($auth>1){
                $href = ($type=="role_auth"?"role.php?opid=":"pap_option.php?type=$type&opid=");
                $edit = <<<END_OF_TEXT
                        CONCAT("<a href='$href",op_id,"' title='Edit' class='icon-page-edit'></a>"),
END_OF_TEXT;
            }
            $sql = <<<END_OF_TEXT
                    SELECT
                    $edit
                    op_name
                    $value_sql
                    FROM pap_option
                    WHERE op_type=:type
                    ORDER BY $sort
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":type",$type);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_user($auth,$cat=null,$s=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE pap_user.user_id>0";
            $filter .= (isset($cat)?" AND um.meta_value=$cat":"");
            $filter .= (isset($s)?" AND user_login LIKE '%$s%'":"");
            if($auth>1){
                $edit .= <<<END_OF_TEXT
                        CONCAT("<a href='user.php?uid=",pap_user.user_id,"' title='Edit' class='icon-page-edit'></a>"),
END_OF_TEXT;
            } else {
                $edit .= <<<END_OF_TEXT
                        CONCAT("<span class='icon-page-edit'></span>"),
END_OF_TEXT;
            }
            $sql = <<<END_OF_TEXT
SELECT
$edit
user_login,
po.op_name,
user_added
FROM pap_user
LEFT JOIN pap_usermeta AS um ON um.user_id=pap_user.user_id AND meta_key='user_auth'
LEFT JOIN pap_option AS po ON po.op_id=meta_value
$filter
ORDER BY po.op_name ASC , user_login ASC
$lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_process_cat(){
        try {
            $sql = <<<END_OF_TEXT
                    SELECT
                    CONCAT("<a href='process_cat.php?pcid=",id,"' title='Edit' class='icon-page-edit'></a>"),
                    name
                    FROM pap_process_cat
                    ORDER BY id ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_process($auth=3,$cat=null,$source=null,$s=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE process_id>0";
            $filter .= (isset($cat)?" AND process_cat_id=$cat":"");
            $filter .= (isset($source)?" AND process_source=$source":"");
            $filter .= (isset($s)?" AND process_name LIKE '%$s%'":"");
            if($auth>1){
                $edit = <<<END_OF_TEXT
CONCAT("<a href='process.php?pid=",process_id,"' title='Edit' class='icon-page-edit'></a>"),
END_OF_TEXT;
            } else {
                $edit .= <<<END_OF_TEXT
                        CONCAT("<span class='icon-page-edit'></span>"),
END_OF_TEXT;
            }
            $sql = <<<END_OF_TEXT
                    SELECT
                    $edit
                    process_name,
                    pc.name,
                    process_unit,
                    IF(process_source='1','ผลิตเอง','สั่งผลิต'),
                    process_setup_min,
                    process_cap,
                    process_std_leadtime_hour
                    FROM pap_process
                    LEFT JOIN pap_process_cat AS pc ON pc.id=process_cat_id
                    $filter
                    ORDER BY pc.id ASC , process_name ASC
                    $lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_jobsize($auth,$s=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = (isset($s)?"WHERE CONCAT(size_name,'(',size_width,'x',size_height,')') LIKE '%$s%'":"");
            if($auth>1){
                $edit .= <<<END_OF_TEXT
                        CONCAT("<a href='lay.php?sid=",size_id,"' title='Edit' class='icon-page-edit'></a>"),
END_OF_TEXT;
            } else {
                $edit .= <<<END_OF_TEXT
                        CONCAT("<span class='icon-page-edit'></span>"),
END_OF_TEXT;
            }
            $sql = <<<END_OF_TEXT
                    SELECT
                    $edit
                    size_name,
                    CONCAT(size_width,'x',size_height),
                    op.op_name AS cover_paper,
                    cover_lay,
                    op1.op_name AS inside_paper,
                    inside_lay
                    FROM pap_size
                    LEFT JOIN pap_option AS op ON op.op_id=cover_paper
                    LEFT JOIN pap_option AS op1 ON op1.op_id=inside_paper
                    $filter
                    ORDER BY size_name ASC
                    $lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_customer($auth,$op,$cat=null,$status=null,$sid=null,$s=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE cus.customer_id>0";
            $filter .= (isset($cat)?" AND lineage LIKE '$cat%'":"");
            $filter .= (isset($status)&&$status>0?" AND customer_status=$status":"");
            $filter .= (isset($s)?" AND CONCAT(customer_code,'-',customer_name) LIKE '%$s%'":"");
            switch ($sid){
                case "0" :
                    $filter .= "";
                    break;
                case null :
                    $filter .= "";
                    break;
                case "-1" :
                    $filter .= " AND uu.user_id IS NULL";
                    break;
                default :
                    $filter .= " AND sc.user_id=$sid";
            }
            if($auth>=2){
                $edit = <<<END_OF_TEXT
CONCAT("<a href='customer.php?cid=",cus.customer_id,"' title='Edit' class='icon-page-edit'></a>"),
CONCAT("<a href='customer.php?cid=",cus.customer_id,"&action=note' title='Note' class='icon-comment-discussion'></a>"),
END_OF_TEXT;
            }
            /*
            if($auth>=3){
                $edit .= <<<END_OF_TEXT
IFNULL(uu.user_login,"-") AS user,
END_OF_TEXT;
            }
             *
             */
            $sql = <<<END_OF_TEXT
SELECT
$edit
CONCAT("<a href='customer.php?action=view&cid=",cus.customer_id,"' title='View'>",customer_code,":<br/>",customer_name,"</a>"),
CONCAT(REPEAT(' -  ',deep),tm.name),
CONCAT("<a href='mailto:",customer_email,"' title'Email'>",customer_email,"</a>"),
CONCAT("<a href='tel:",customer_tel,"' title'Call'>",customer_tel,"</a>"),
customer_status
FROM pap_customer AS cus
LEFT JOIN pap_sale_cus as sc ON sc.cus_id=cus.customer_id
LEFT JOIN pap_user AS uu ON uu.user_id=sc.user_id
LEFT JOIN pap_customer_cat AS cc ON cc.customer_id=cus.customer_id
LEFT JOIN pap_term_tax AS tx ON tx.id=cc.tax_id
LEFT JOIN pap_term AS tm ON tm.id=tx.term_id
$filter
ORDER BY customer_added DESC
$lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($res as $k=>$v){
                $res[$k]["customer_status"] = $op[$v['customer_status']];
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_supplier($auth,$cat=null,$s=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE sup.id>0";
            $filter .= (isset($cat)?" AND lineage LIKE :lineage":"");
            $filter .= (isset($s)?" AND CONCAT(code,'-',sup.name) LIKE '%$s%'":"");
            if($auth>1){
                $edit .= <<<END_OF_TEXT
                        CONCAT("<a href='supplier.php?sid=",sup.id,"' title='Edit' class='icon-page-edit'></a>"),
END_OF_TEXT;
            } else {
                $edit .= <<<END_OF_TEXT
                        CONCAT("<span class='icon-page-edit'></span>"),
END_OF_TEXT;
            }
            
            $sql = <<<END_OF_TEXT
                    SELECT
                    $edit
                    code,sup.name,CONCAT(REPEAT(' -  ',deep),tm.name),
                    CONCAT("<a href='mailto:",email,"' title'Email'>",email,"</a>"),
                    CONCAT("<a href='tel:",tel,"' title'Call'>",tel,"</a>")
                    FROM pap_supplier AS sup
                    LEFT JOIN pap_supplier_cat AS sc ON supplier_id=sup.id
                    LEFT JOIN pap_term_tax AS tx ON tx.id=sc.tax_id
                    LEFT JOIn pap_term AS tm ON tm.id=tx.term_id
                    $filter
                    ORDER BY added DESC
                    $lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            if(isset($cat)){
                $stmt1 = $this->conn->prepare("SELECT lineage FROM pap_term_tax WHERE id=:cat");
                $stmt1->bindParam(":cat",$cat);
                $stmt1->execute();
                $row = $stmt1->fetch(PDO::FETCH_ASSOC);
                $lineage = $row['lineage']."%";
                $stmt->bindParam(":lineage",$lineage);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_quote($auth,$op,$type=null,$status=null,$mm=null,$sid=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE quo.quote_id>0";
            $filter .= (isset($type)?" AND cat_id=$type":"");
            $filter .= (isset($status)&&$status>0?" AND status=$status":"");
            $filter .= (isset($mm)?" AND DATE_FORMAT(created,'%m%Y')='$mm'":"");
            $filter .= (isset($sid)?" AND sale.user_id=$sid":"");
            if(is_null($type)&&is_null($status)&&is_null($mm)){
                $filter .= " AND finished IS NULL = 1";
            }
            if($auth>1){
                $edit = <<<END_OF_TEXT
                        CONCAT("<a href='quotation.php?qid=",quo.quote_id,"' title='Edit' class='icon-page-edit'></a>"),
END_OF_TEXT;
            }
            $sale = "";
            if($auth>3){
                $sale = ",user.user_login";
            }
            $sql = <<<END_OF_TEXT
SELECT
$edit
CONCAT("<a href='quotation.php?action=print&qid=",quo.quote_id,"' title='Print' class='icon-print' target='_blank'></a>"),
CONCAT(quote_no,"<br/>",name),
cus.customer_name,
FORMAT(q_price,0),
FORMAT(meta2.meta_value,0) AS nego,
CONCAT(size_name,' (',size_height,'x',size_width,')') AS size,
meta.meta_value AS pages,
FORMAT(amount,0),
DATE_FORMAT(created,'%d-%b') AS dcreated,
quo.status AS status,
meta1.meta_value AS qsign
$sale
FROM pap_quotation AS quo
LEFT JOIN pap_option AS op ON op.op_id=cat_id
LEFT JOIN pap_size ON size_id=job_size_id
LEFT JOIN pap_quote_meta AS meta ON meta.quote_id=quo.quote_id AND meta.meta_key ="page_inside"
LEFT JOIN pap_quote_meta AS meta1 ON meta1.quote_id=quo.quote_id AND meta1.meta_key ="quote_sign"
LEFT JOIN pap_quote_meta AS meta2 ON meta2.quote_id=quo.quote_id AND meta2.meta_key ="n_price"
LEFT JOIn pap_customer AS cus ON cus.customer_id=quo.customer_id
LEFT JOIN pap_sale_cus AS sale ON sale.cus_id=cus.customer_id
LEFT JOIN pap_user AS user ON user.user_id=sale.user_id
$filter
ORDER BY created ASC
$lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($res as $k=>$v){
                $view = "";
                if(strlen($v['qsign'])>0){
                    $qsign = substr(ROOTS,0,-1).$v['qsign'];
                    $view = ($v['status']==9?"<a href='$qsign' title='View Doc' class='icon-search' target='_blank'></a>":"");
                }
                $res[$k]['status'] = $op[$v['status']].$view;
                unset($res[$k]['qsign']);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_order_ga($auth,$st_code,$st_name,$status=null,$s=null,$page=null,$perpage=null){
        try {
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE po.status<=7";
            $filter .= (isset($status)&&$status>0?" AND po.status=$status":"");
            $filter .= (isset($s)?" AND CONCAT(po.order_no,':',pq.name) LIKE '%$s%'":"");
            if(is_null($s)&&is_null($status)){
                $filter .= " AND po.status<7";
            }
            $sql = <<<END_OF_TEXT
SELECT
po.order_id,
meta1.meta_value AS qsign,      
CONCAT("<a href='order.php?action=print&oid=",po.order_id,"' title='View' target='_blank'>",order_no,":<br/>",pq.name,"</a>"),
cus.customer_name,
FORMAT(amount,0),
DATE_FORMAT(pq.plan_delivery,'%d-%b') AS due,
IF(ISNULL(plate_plan),"",DATE_FORMAT(plate_plan,'%e-%b')),
po.status,
IF(ISNULL(plate_plan),1,IF(ISNULL(plate_received),IF(now()>plate_plan,5,2),IF(plate_received>plate_plan,5,4))) AS stc
FROM pap_order AS po
LEFT JOIN pap_quotation AS pq on pq.quote_id=po.quote_id
LEFT JOIN pap_customer AS cus ON cus.customer_id=pq.customer_id
LEFT JOIN pap_quote_meta AS meta ON meta.quote_id=po.quote_id AND meta.meta_key ="page_inside"
LEFT JOIN pap_quote_meta AS meta1 ON meta1.quote_id=po.quote_id AND meta1.meta_key ="quote_sign"
$filter
ORDER BY pq.plan_delivery ASC
$lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($res as $k=>$v){
                $oid = $v['order_id'];
                $status = $v['status'];
                $res[$k]['stc'] = $st_code[$v['stc']];
                $res[$k]['status'] = "<a href='' title='เปลี่ยนสถานะ' oid='$oid' status='$status' class='icon-page-edit edit-status'></a>"
                        . $st_name[$status];
                if($auth>1){
                    $res[$k]['order_id'] = "<a href='order.php?oid=$oid' title='Edit' class='icon-page-edit'></a>";
                } else {
                    $res[$k]['order_id'] = "";
                }
                if(strlen($v['qsign'])>0){
                    $qsign = substr(ROOTS,0,-1).$v['qsign'];
                    $res[$k]['qsign'] = "<a href='$qsign' title='View Doc' class='icon-search' target='_blank'></a>";
                }
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_order($auth,$op,$status=null,$s=null,$page=null,$perpage=null){
        try {
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE po.quote_id>0";
            $filter .= (isset($status)&&$status>0?" AND po.status=$status":"");
            $filter .= (isset($s)?" AND CONCAT(po.order_no,':',pq.name) LIKE '%$s%'":"");
            if(is_null($s)&&is_null($status)){
                $filter .= " AND prod_finished IS NULL = 1 AND po.status<69";
            }
            $sql = <<<END_OF_TEXT
SELECT
po.order_id,
CONCAT("<a href='order.php?action=print&oid=",po.order_id,"' title='View' target='_blank'>",order_no,": <br/>",pq.name,"</a>"),
cus.customer_name,
meta.meta_value AS pages,FORMAT(amount,0),
DATE_FORMAT(pq.plan_delivery,'%d-%b') AS due,
IF(ISNULL(plate_plan),1,IF(ISNULL(plate_received),IF(now()>plate_plan,5,2),IF(plate_received>plate_plan,5,4))) AS plate,
IF(ISNULL(paper_plan),1,IF(ISNULL(paper_received),IF(now()>paper_plan,5,2),IF(paper_received>paper_plan,5,4))) AS paper,
po.prod_plan AS plan
FROM pap_order AS po
LEFT JOIN pap_quotation AS pq on pq.quote_id=po.quote_id
LEFT JOIN pap_customer AS cus ON cus.customer_id=pq.customer_id
LEFT JOIN pap_quote_meta AS meta ON meta.quote_id=po.quote_id AND meta.meta_key ="page_inside"
$filter
ORDER BY pq.plan_delivery ASC
$lim_sql
END_OF_TEXT;
            $sql1 = <<<END_OF_TEXT
SELECT
SUM(IFNULL(ddt.qty,0)) AS deli
FROM pap_order_comp AS com
LEFT JOIN pap_mat_po_detail AS dt ON dt.mat_id=com.paper_id AND dt.order_ref=com.order_id
LEFT JOIN pap_mat_delivery_dt AS ddt ON ddt.dt_id=dt.id
WHERE com.order_id=:oid AND paper_id IS NOT NULL
GROUP BY com.order_id
END_OF_TEXT;
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->bindParam(":oid",$oid);
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($res as $k=>$v){
                $oid = $v['order_id'];
                $paper = $v['paper'];
                if($v['paper']>1&&$v['paper']<>4){
                    $stmt1->execute();
                    $deli = $stmt1->fetch(PDO::FETCH_ASSOC)['deli'];
                    if((float)$deli>0){
                        $paper = 3;
                    }
                }
                $res[$k]['plate'] = $op[$v['plate']];
                $res[$k]['paper'] = $op[$paper];
                if(is_null($v['plan'])){
                    $res[$k]['plan'] = "<a href='production.php?action=addplan&oid=$oid' class='icon-plus-square' title='วางแผน'></a>";
                } else {
                    $res[$k]['plan'] = "<a href='production.php?action=addplan&oid=$oid' class='icon-page-edit' title='ปรับแผน'></a>";
                }
                if($auth>1){
                    $res[$k]['order_id'] = "<a href='order.php?oid=$oid' title='Edit' class='icon-page-edit'></a>";
                } else {
                    $res[$k]['order_id'] = "";
                }
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_mat($auth,$cat=null,$s=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE mat_id>0";
            $filter .= (isset($cat)?" AND mat_cat_id=$cat":"");
            $filter .= (isset($s)?" AND mat_name LIKE '%$s%'":"");
            if($auth>1){
                $edit .= <<<END_OF_TEXT
                        CONCAT("<a href='mat.php?mid=",mat_id,"' title='Edit' class='icon-page-edit'></a>"),
END_OF_TEXT;
            } else {
                $edit .= <<<END_OF_TEXT
                        CONCAT("<span class='icon-page-edit'></span>"),
END_OF_TEXT;
            }
            $sql = <<<END_OF_TEXT
SELECT
$edit
op.op_name AS type,
mat_name,mat_unit,mat_order_lot_size,
FORMAT(mat_std_cost,2),
mat_std_leadtime
FROM pap_mat
LEFT JOIN pap_option AS op ON op.op_id=mat_cat_id
$filter
ORDER BY op.op_name ASC, op.op_name ASC
$lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_note($auth,$cid,$uid){
        try {
            if($auth>2){
                $wh = "WHERE customer_id=:cid";
            } else {
                $wh = "WHERE user_id=:uid AND customer_id=:cid";
            }
            $sql = <<<END_OF_TEXT
                    SELECT
                    CONCAT("<span class='note-edit' ninfo='",crm_id,";",crm_date,";",crm_detail,"'>",DATE_FORMAT(crm_date,"%d-%b-%Y"),"</span>"),
                    crm_detail
                    FROM pap_crm
                    LEFT JOIN pap_user AS pu on pu.user_id=pap_crm.user_id
                    $wh
                    ORDER BY crm_date DESC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":cid",$cid);
            if($auth>2){
            } else {
                $stmt->bindParam(":uid",$uid);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_machine($auth,$cat=null,$s=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE ma.id>0";
            $filter .= (isset($cat)?" AND pc.process_cat_id=$cat":"");
            $filter .= (isset($s)?" AND ma.name LIKE '%$s%' OR pc.process_name LIKE '%$s%'":"");
            if($auth>1){
                $edit = <<<END_OF_TEXT
CONCAT("<a href='machine.php?mid=",ma.id,"' title='Edit' class='icon-page-edit'></a>"),
END_OF_TEXT;
            } else {
                $edit .= <<<END_OF_TEXT
CONCAT("<span class='icon-page-edit'></span>"),
END_OF_TEXT;
            }
            $sql = <<<END_OF_TEXT
SELECT
$edit
ma.name,
pc.process_name,
pc.process_unit,
ma.setup_min,ma.cap,
uu.users
FROM pap_machine AS ma
LEFT JOIN pap_process AS pc ON pc.process_id=ma.process_id
LEFT JOIN (
    SELECT
    mach_id,GROUP_CONCAT(user_login) AS users
    FROM pap_mach_user AS mu
    LEFT JOIN pap_user AS pu ON pu.user_id=mu.user_id
    GROUP BY mach_id
) AS uu ON uu.mach_id=ma.id
$filter
ORDER BY pc.process_cat_id ASC, ma.name ASC
$lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_cus_ad($cid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
CONCAT("<a href='shipping_address.php?adid=",id,"' title='Edit' class='icon-page-edit'></a>"),
CONCAT(name,"<br/>",address)
FROM pap_cus_ad
WHERE customer_id=:cid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":cid",$cid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_NUM);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_job_mdeli($auth,$op,$mm=null,$status=null,$s=null,$page=null,$perpage=null){
        try {
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE dt.order_id=''";
            $filter .= (isset($mm)?" AND DATE_FORMAT(deli.date,'%y%m')='$mm'":"");
            $filter .= (isset($status)&&$status>0?" AND deli.status=$status":"");
            $filter .= (isset($s)?" AND job_name LIKE '%$s%'":"");
            if(is_null($mm)&&is_null($status)&&is_null($s)){
                $filter .= " AND deli.status<79";
            }
            $sql = <<<END_OF_TEXT
SELECT
dt.job_name,cus.customer_name,
FORMAT(dt.qty,0) AS qty,FORMAT(dt.price,2),
deli.id,
deli.no,
deli.status,
GROUP_CONCAT(CONCAT(tdeli.id,":",tdeli.no) ORDER BY tdeli.no ASC) AS tid,
dt.qty AS dqty,
tdt.tqty
FROM pap_delivery_dt AS dt
LEFT JOIN pap_delivery AS deli ON deli.id=dt.deli_id
LEFT JOIN pap_customer AS cus ON cus.customer_id=dt.customer_id
LEFT JOIN pap_temp_deli AS tdeli ON tdeli.deli_id=deli.id
LEFT JOIN (
    SELECT
    job_name,SUM(qty) AS tqty
    FROM pap_temp_dt
    WHERE order_id=0
    GROUP BY job_name
) AS tdt ON tdt.job_name=dt.job_name
$filter
GROUP BY dt.job_name
ORDER BY deli.date ASC
$lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($res as $k=>$v){
                $did = $v['id'];
                $res[$k]['no'] = "<a href='mdelivery.php?action=edit&did=$did' title='Edit' class='icon-page-edit'></a>"
                        . "<a href='mdelivery.php?action=print&did=$did' title='Print' target='_blank'>".$v['no']."</a>";
                $res[$k]['status'] = $op[$v['status']];
                //temp deli
                $atid = explode(",",$v['tid']);
                $tdeli = "";
                for($i=0;$i<count($atid);$i++){
                    if($atid[$i]!=""){
                        $data = explode(":",$atid[$i]);
                        $tdid = $data[0];
                        $tno = $data[1];
                        if($tdid>0){
                            $tdeli .= "<a href='mdelivery.php?action=edit&tdid=$tdid' title='Edit' class='icon-page-edit'></a>"
                            . "<a href='mdelivery.php?action=print&tdid=$tdid' title='Print' target='_blank'>".$tno."</a>"
                                    . "<br/>";
                        }
                    }
                }
                if($v['dqty']!=$v['tqty']){
                    $tdeli .= "<a href='mdelivery.php?action=addtdeli&did=$did' title='Add' class='icon-plus-square'></a>";
                }
                $res[$k]['tid'] = $tdeli;
                unset($res[$k]['dqty']);
                unset($res[$k]['tqty']);
                unset($res[$k]['id']);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_job_deli($auth,$op,$mm=null,$status=null,$s=null,$page=null,$perpage=null){
        include_once("class.pappdo.php");
        $db = new PAPdb(DB_PAP);
        try {
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE po.status BETWEEN 69 AND 79";
            $filter .= (isset($mm)?" AND DATE_FORMAT(deli.date,'%y%m')='$mm'":"");
            $filter .= (isset($status)&&$status>0?" AND po.status=$status":"");
            $filter .= (isset($s)?" AND CONCAT(po.order_no,':',quo.name) LIKE '%$s%'":"");
            if(is_null($s)&&is_null($status)&&is_null($mm)){
                $filter .= " AND po.status IN (69,70)";
            }
            $sql = <<<END_OF_TEXT
SELECT
CONCAT("<a href='order.php?action=print&oid=",po.order_id,"' title='View' target='_blank'>",order_no,":",quo.name,"</a>"),
CONCAT(cus.customer_code,":<br/>",cus.customer_name),
DATE_FORMAT(quo.plan_delivery,'%d-%b') AS due,
FORMAT(quo.amount,0),
po.order_id,
GROUP_CONCAT(tdeli.id) AS gtid,
GROUP_CONCAT(tdeli.no) AS gtno,
dt.deli_id,
deli.no,
po.status
FROM pap_order AS po
LEFT JOIN pap_quotation AS quo on quo.quote_id=po.quote_id
LEFT JOIN pap_customer AS cus ON cus.customer_id=quo.customer_id
LEFT JOIN pap_delivery_dt AS dt ON dt.order_id=po.order_id
LEFT JOIN pap_delivery AS deli ON deli.id=dt.deli_id
LEFT JOIN pap_temp_deli AS tdeli ON tdeli.deli_id=deli.id
$filter
GROUP BY po.order_id
ORDER BY quo.plan_delivery ASC, tdeli.id ASC
$lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($res as $k=>$v){
                $oid = $v['order_id'];
                $did = (int)$v['deli_id'];
                $deli_no = $v['no'];
                $oinfo = $db->get_job_remain_deli($oid);
                $remain = $oinfo['amount']-$oinfo['deli'];
                $arrtdid = explode(",",$v['gtid']);
                $arrtno = explode(",",$v['gtno']);
                $arrdoc = array_combine($arrtdid,$arrtno);
                asort($arrdoc);
                $doc = "";
                if(is_null($v['deli_id'])){
                    $str_oid = $oid;
                    $deli = "";
                    $mix = "<input type='checkbox' value='$oid' name='oid[]' />";
                    $doc = "";
                } else {
                    $stmt1 = $this->conn->query("SELECT GROUP_CONCAT(order_id) FROM pap_delivery_dt WHERE deli_id=$did GROUP BY deli_id");
                    $tt = $stmt1->fetch(PDO::FETCH_NUM);
                    $str_oid = $tt[0];
                    $deli = ($auth>1?"<a href='delivery.php?action=edit&did=$did&oid=$str_oid' title='แก้ไขใบแจ้งหนี้' class='icon-page-edit'></a>":"")
                        . "<a href='delivery.php?action=print&did=$did' title='Print' target='_blank'>$deli_no</a>";
                    $mix = "";
                    $i=0;
                    foreach($arrdoc as $key=>$val){
                        if($key>0){
                            $doc .= ($i==0?"":"<br/>")
                                . "<a href='delivery.php?action=edit&tdid=$key&oid=$str_oid' title='แก้ไขใบส่งของ' class='icon-page-edit'></a>"
                                . "<a href='delivery.php?action=print&tdid=$key' title='Print' target='_blank'>$val</a>";
                            $i++;
                        }
                    }
                }
                if($remain>0){
                    $add = "<a href='delivery.php?action=add&oid=$str_oid' title='สร้างใบส่งของ' class='icon-plus-square'></a><br/>".$op[$v['status']];
                } else {
                    $add = $op[$v['status']];
                }
                unset($res[$k]['order_id']);
                unset($res[$k]['deli_id']);
                unset($res[$k]['no']);
                unset($res[$k]['gtid']);
                unset($res[$k]['gtno']);
                unset($res[$k]['status']);
                array_push($res[$k],$mix,$add,$deli,$doc);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_job_status($auth,$op,$due=null,$status=null,$s=null,$page=null,$perpage=null,$allrec=false){
        try {
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE po.status BETWEEN 7 AND 69";
            $filter .= (isset($due)?" AND quo.plan_delivery='$due'":"");
            $filter .= (isset($status)&&$status>0?" AND po.status=$status":"");
            $filter .= (isset($s)?" AND CONCAT(po.order_no,':',quo.name) LIKE '%$s%'":"");
            if(is_null($s)&&is_null($status)&&is_null($due)){
                $filter .= " AND po.status < 69";
            }
            $sql = <<<END_OF_TEXT
SELECT
CONCAT("<a href='status.php?action=edit&oid=",po.order_id,"' title='Update' class='icon-page-edit'></a>") AS up,
CONCAT("<a href='order.php?action=print&oid=",po.order_id,"' title='View' target='_blank'>",order_no,":",quo.name,"</a>") AS name,
DATE_FORMAT(quo.plan_delivery,'%d-%b') AS due,
po.status AS ostatus,
com.name AS comp,IFNULL(cat.name,"") AS status,
com.id AS compid,
po.order_id AS oid
FROM pap_order AS po
LEFT JOIN pap_quotation AS quo on quo.quote_id=po.quote_id
LEFT JOIN pap_customer AS cus ON cus.customer_id=quo.customer_id
LEFT JOIN pap_order_comp AS com ON com.order_id=po.order_id
LEFT JOIN pap_process_cat AS cat ON cat.id=com.status
$filter
ORDER BY quo.plan_delivery ASC, com.id ASC
$lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            if($stmt->rowCount()>0){
                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if($allrec){
                    return $res;
                } else {
                    foreach($res as $k=>$v){
                        $oid = $v['oid'];
                        $main_status = "<a href='' class='icon-page-edit edit-main-status' title='Update Status' oid='$oid'></a>";
                        $key = $v['up'].",".$v['name'].",".$v['due'].",".$op[$v['ostatus']].$main_status;
                        $status = "<a href='' class='edit-comp-status icon-page-edit' title='Update' oid='$oid' compid='".$v['compid']."'></a>".$v['status'];
                        if(isset($res1[$key])){
                            array_push($res1[$key],array($v['comp'],$status));
                        } else {
                            $res1[$key] = array(array($v['comp'],$status));
                        }
                    }
                    return $res1;
                }
            } else {
                return array();
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_cus_credit($auth,$cat=null,$s=null,$page=null,$perpage=null){
        try {
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE cus.customer_id>0";
            $filter .= (isset($cat)?" AND tx.lineage like '$cat%'":"");
            $filter .= (isset($s)?" AND CONCAT(cus.customer_code,'-',cus.customer_name) LIKE '%$s%'":"");
            $sql = <<<END_OF_TEXT
SELECT
cus.customer_code,cus.customer_name,customer_credit_amount AS credit,
CONCAT(REPEAT(' -  ',deep),tm.name) AS term,
IFNULL(sum(quo1.q_price),0) AS printing,
IFNULL(finish.price-finish.paid,0) AS remain
FROM pap_customer AS cus
LEFT JOIN pap_quotation AS quo ON quo.customer_id=cus.customer_id
LEFT JOIN pap_order AS job ON job.quote_id=quo.quote_id AND job.status<79
LEFT JOIN pap_quotation AS quo1 ON quo1.quote_id=job.quote_id
LEFT JOIN (
	SELECT
	quo.customer_id,SUM(quo.q_price) AS price,SUM(job.paid) AS paid
	FROM pap_order AS job
	LEFT JOIN pap_quotation AS quo ON quo.quote_id=job.quote_id
	WHERE job.status=79
	GROUP BY customer_id
) AS finish ON finish.customer_id=cus.customer_id
LEFT JOIN pap_customer_cat AS cc ON cc.customer_id=cus.customer_id
LEFT JOIN pap_term_tax AS tx ON tx.id=cc.tax_id
LEFT JOIN pap_term AS tm ON tm.id=tx.term_id
$filter
GROUP BY cus.customer_id
$lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            $res = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $name = $row['customer_code'].":".$row['customer_name'];
                array_push($res,array($name,$row['term'],number_format($row['credit'],2),number_format($row['printing'],2),number_format($row['remain'],2)));
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
}
