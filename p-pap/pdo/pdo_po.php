<?php
class pdo_po{
    private $conn;
    public function __construct() {
        $this->conn = dbConnect(DB_PAP);
    }
    public function view_po_deli($dyid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
deli.ref AS รหัสรับเข้า ,po.po_code AS อ้างอิง,user.user_login AS ผู้รับเข้า,DATE_FORMAT(deli.deliveried,'%d-%b') AS วันที่,deli.remark AS หมายเหตุ
FROM pap_mat_delivery AS deli
LEFT JOIN pap_mat_po AS po ON po.po_id=deli.po_id
LEFT JOIn pap_user AS user ON user.user_id=deli.user_id
WHERE deli.id=:dyid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":dyid",$dyid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_ppo_deli($dyid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
deli.ref AS รหัสรับเข้า ,po.po_code AS อ้างอิง,user.user_login AS ผู้รับเข้า,DATE_FORMAT(deli.deliveried,'%d-%b') AS วันที่,deli.remark AS หมายเหตุ
FROM pap_wip_delivery AS deli
LEFT JOIN pap_process_po AS po ON po.po_id=deli.po_id
LEFT JOIn pap_user AS user ON user.user_id=deli.user_id
WHERE deli.id=:dyid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":dyid",$dyid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_po_deli_dt($dyid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
@no:=@no+1 AS no,
mat.mat_name,CONCAT(job.order_no,":",quo.name),ddt.qty,ddt.stk_location
FROM pap_mat_delivery AS deli
LEFT JOIN pap_mat_delivery_dt AS ddt ON ddt.delivery_id=deli.id
LEFT JOIN pap_mat_po_detail AS podt ON podt.id=ddt.dt_id
LEFT JOIN pap_mat AS mat ON mat.mat_id=podt.mat_id
LEFT JOIN pap_order AS job ON job.order_id=podt.order_ref
LEFT JOIN pap_quotation AS quo ON quo.quote_id=job.quote_id
WHERE deli.id=:dyid;
END_OF_TEXT;
            $stmt = $this->conn->prepare("SET @no=0");
            $stmt->execute();
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":dyid",$dyid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_ppo_deli_dt($dyid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
@no:=@no+1 AS no,
CONCAT(comp.name," : ",cpro.name),CONCAT(job.order_no,":",quo.name),ddt.qty,ddt.stk_location
FROM pap_wip_delivery AS deli
LEFT JOIN pap_wip_delivery_dt AS ddt ON ddt.delivery_id=deli.id
LEFT JOIN pap_pro_po_dt AS podt ON podt.id=ddt.dt_id
LEFT JOIN pap_comp_process AS cpro ON cpro.id=podt.cpro_id
LEFT JOIN pap_order_comp AS comp ON comp.id=cpro.comp_id
LEFT JOIN pap_order AS job ON job.order_id=comp.order_id
LEFT JOIN pap_quotation AS quo ON quo.quote_id=job.quote_id
WHERE deli.id=:dyid;
END_OF_TEXT;
            $stmt = $this->conn->prepare("SET @no=0");
            $stmt->execute();
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":dyid",$dyid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function outsource_info($cproid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
CONCAT("(",comp.name,") ",cpro.name) AS name,CONCAT(job.order_no,":",quo.name) AS jobname
FROM pap_comp_process AS cpro
LEFT JOIN pap_order_comp AS comp ON comp.id=cpro.comp_id
LEFT JOIN pap_order AS job ON job.order_id=comp.order_id
LEFT JOIN pap_quotation AS quo ON quo.quote_id=job.quote_id
WHERE cpro.id=:cproid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":cproid",$cproid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_pro_po($auth,$op,$status=null,$mm=null,$s=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE po.po_id>0";
            $filter .= (isset($status)&&$status>0?" AND po.po_status=$status":"");
            $filter .= (isset($mm)?" AND DATE_FORMAT(po_created,'%m%Y')='$mm'":"");
            $filter .= (isset($s)?" AND po_code LIKE '%$s%'":"");
            if(is_null($s)&&is_null($status)&&is_null($mm)){
                $filter .= " AND po_deliveried IS NULL = 1";
            }
            if($auth>1){
                $edit .= <<<END_OF_TEXT
CONCAT("<a href='outsource.php?poid=",po.po_id,"' title='Edit' class='icon-page-edit'></a>"),
END_OF_TEXT;
            }

            $sql = <<<END_OF_TEXT
SELECT
$edit
CONCAT("<a href='outsource.php?action=print&poid=",po.po_id,"' title='View' target='_blank'>",po.po_code,"</a>"),
CONCAT(sup.code,":",sup.name),
DATE_FORMAT(po_created,'%d-%b'),
po.po_status AS status
FROM pap_process_po AS po
LEFT JOIN pap_supplier AS sup ON sup.id=po.supplier_id
$filter
ORDER BY po.po_created ASC
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
                $res[$k]['status'] = $op[$v['status']];
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_purchase($auth,$op,$status=null,$s=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE po.order_id>0";
            $filter .= (isset($status)&&$status>0?" AND po.status=$status":"");
            $filter .= (isset($s)?" AND CONCAT(po.order_no,':',pq.name) LIKE '%$s%'":"");
            if(is_null($s)&&is_null($status)){
                $filter .= " AND po.paper_plan IS NULL = 1";
            }
            $sql = <<<END_OF_TEXT
SELECT
CONCAT("<a href='order.php?action=print&oid=",po.order_id,"' title='View' target='_blank'>",order_no,":<br/>",pq.name,"</a>") AS jname,
cus.customer_name,
FORMAT(pq.amount,0) AS amount,
DATE_FORMAT(pq.plan_delivery,'%d-%b') AS due,
po.status,
po.order_id
FROM pap_order AS po
LEFT JOIN pap_quotation AS pq on pq.quote_id=po.quote_id
LEFT JOIN pap_customer AS cus ON cus.customer_id=pq.customer_id
$filter
ORDER BY pq.plan_delivery ASC
$lim_sql
END_OF_TEXT;
            $sql2 = <<<END_OF_TEXT
SELECT
mat.mat_id,
mat.mat_name,
SUM(com.paper_use) AS rim,
po.poid,po.pocode,po.tt
FROM pap_order_comp AS com
LEFT JOIN pap_mat AS mat ON mat_id=paper_id
LEFT JOIN (
	SELECT
	mat_id,SUM(mat_qty) AS tt,GROUP_CONCAT(po.po_id) AS poid,GROUP_CONCAT(po_code) AS pocode
	FROM pap_mat_po_detail AS dt
	LEFT JOIN pap_mat_po AS po ON po.po_id=dt.po_id
	WHERE order_ref=:oid
	GROUP BY mat_id
) AS po ON po.mat_id=com.paper_id
WHERE com.order_id=:oid AND paper_id IS NOT NULL
GROUP BY com.paper_id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt1 = $this->conn->prepare($sql2);
            $stmt1->bindParam(":oid",$oid);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $res1 = array();
            $j=0;
            foreach($res as $k=>$v){
                $key = $v['jname'].";".$v['customer_name'].";".$v['amount'].";".$v['due'].";".$op[$v['status']];
                $oid = $v['order_id'];
                $stmt1->execute();
                while($row = $stmt1->fetch(PDO::FETCH_ASSOC)){
                    //check current po
                    $tpoid = explode(",",$row['poid']);
                    $tpo_code = explode(",",$row['pocode']);
                    $po = "<div class='paper_po'>";
                    for($i=0;$i<count($tpoid);$i++){
                        $po .= ($i==0?"":" , ")."<a href='paper.php?poid=".$tpoid[$i]."' title='Edit'>".$tpo_code[$i]."</a>";
                    }
                    $po .= "</div>";

                    $remain = $row['rim']-$row['tt'];
                    if($remain > 0){
                        //show checkbox
                        $data = $oid.",".$row['mat_id'].",".$remain;
                        $paper = "<div class='paper_order'>"
                                . "<input id='po_$j' type='checkbox' value='$data' name='po_paper[]' />"
                                . "<label for='po_$j'>".$row['mat_name']."<br/>(".$remain." ริม)</label>"
                                . $po
                                . "</div>";
                    } else {
                        $paper = "<div class='paper_order'>"
                                . "<p>".$row['mat_name']."<br/>(".$row['rim']." ริม)</p>"
                                . $po
                                . "</div><!-- .paper_order -->";
                    }
                    if(isset($res1[$key])){
                        array_push($res1[$key],array($paper));
                    } else {
                        $res1[$key] = array(array($paper));
                    }
                    $j++;
                }
            }
            return $res1;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_outsource($auth,$op,$status=null,$s=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE job.status BETWEEN 8 AND 69";
            $filter .= (isset($status)&&$status>0?" AND job.status=$status":"");
            $filter .= (isset($s)?" AND CONCAT(job.order_no,':',pq.name) LIKE '%$s%'":"");
            if(is_null($s)&&is_null($status)){
                $filter .= " AND job.status<69";
            }
            $sql = <<<END_OF_TEXT
SELECT
CONCAT("<a href='order.php?action=print&oid=",job.order_id,"' title='View' target='_blank'>",order_no,":<br/>",pq.name,"</a>") AS jname,
CONCAT_WS(":",order_no,pq.name) AS jobn,
cus.customer_name,
FORMAT(pq.amount,0) AS amount,
DATE_FORMAT(pq.plan_delivery,'%d-%b') AS due,
job.status,
job.order_id
FROM pap_order AS job
LEFT JOIN pap_quotation AS pq on pq.quote_id=job.quote_id
LEFT JOIN pap_customer AS cus ON cus.customer_id=pq.customer_id
$filter
ORDER BY pq.plan_delivery ASC
$lim_sql
END_OF_TEXT;
            $sql1 = <<<END_OF_TEXT
SELECT
comp.order_id,
cpro.id,cpro.name AS pname,cpro.volume,comp.name AS cname,cpro.process_id,pro.process_unit,
GROUP_CONCAT(po.po_id) AS poid,
GROUP_CONCAT(po.po_code) AS pocode
FROM pap_order_comp AS comp
LEFT JOIN pap_comp_process AS cpro ON cpro.comp_id=comp.id
LEFT JOIN pap_process AS pro ON pro.process_id=cpro.process_id
LEFT JOIN pap_pro_po_dt AS podt ON podt.cpro_id=cpro.id
LEFT JOIN pap_process_po AS po ON po.po_id=podt.po_id
WHERE pro.process_source=1 AND comp.order_id=:oid
GROUP BY cpro.id
ORDER BY cpro.id ASC
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
            $res1 = array();
            $j=0;
            foreach($res as $k=>$v){
                $key = $v['jname'].";".$v['customer_name'].";".$v['amount'].";".$v['due'].";".$op[$v['status']];
                $oid = $v['order_id'];
                $stmt1->execute();
                while($row = $stmt1->fetch(PDO::FETCH_ASSOC)){
                    //check current po
                    $tpoid = explode(",",$row['poid']);
                    $tpo_code = explode(",",$row['pocode']);
                    $po = "<div class='current-po'>";
                    for($i=0;$i<count($tpoid);$i++){
                        $po .= ($i==0?"":" , ")."<a href='outsource.php?poid=".$tpoid[$i]."' title='Edit'>".$tpo_code[$i]."</a>";
                    }
                    $po .= "</div><!-- .current-po -->";

                    $pname = $row['pname'];
                    $cname = $row['cname'];
                    $val = array($row['id'],$row['process_id'],$row['process_unit'],$row['volume']);
                    $os = "<div class='process_os'>"
                        . "<input id='pro_$j' type='checkbox' name='process[]' value='".implode(",",$val)."'/>"
                        . "<label for='pro_$j'>($cname) $pname</label>"
                        . $po
                        . "</div><!-- .process_os -->";
                    if(isset($res1[$key])){
                        array_push($res1[$key],array($os));
                    } else {
                        $res1[$key] = array(array($os));
                    }
                    $j++;
                }
            }
            return $res1;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_mat_receive($poid){
        try {
            __autoloada("form");
            $form = new myform();
            $sql = <<<END_OF_TEXT
SELECT
mat.mat_name,dt.mat_qty,IFNULL(SUM(ddt.qty),0) AS rcqty,
dt.order_ref,dt.id AS dtid
FROM pap_mat_po_detail AS dt
LEFT JOIN pap_mat AS mat ON mat.mat_id=dt.mat_id
LEFT JOIN pap_mat_delivery_dt AS ddt ON ddt.dt_id=dt.id
WHERE dt.po_id=:poid
GROUP BY dt.po_id,dt.order_ref,dt.mat_id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":poid",$poid);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $i=0;
            foreach($res as $k=>$v){
                $rem = $v['mat_qty']-$v['rcqty'];
                if($rem>0){
                    $rec = $form->show_num("receive_$i", $rem, 0.01, "", "", "", "label-inline readonly", "min='0' max='$rem' readonly", "receive[]")
                            . $form->show_hidden("oid_$i","oid[]",$v['order_ref'])
                            . $form->show_hidden("dtid_$i","dtid[]",$v['dtid']);
                    $loc = $form->show_text("loc_$i", "loc[]", "", "", "", "", "label-inline");
                } else {
                    $rec = "";
                    $loc = "";
                }
                array_push($res[$k],$rec,$loc);
                unset($res[$k]['dtid']);
                unset($res[$k]['rcqty']);
                unset($res[$k]['order_ref']);
                $i++;
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_process_rc($poid){
        try {
            __autoloada("form");
            $form = new myform();
            $sql = <<<END_OF_TEXT
SELECT
CONCAT(comp.name,":",cpro.name),dt.qty,IFNULL(SUM(ddt.qty),0) AS rcqty,
dt.id,cpro.comp_id,cpro.process_id,comp.order_id
FROM pap_pro_po_dt AS dt
LEFT JOIN pap_comp_process AS cpro ON cpro.id=dt.cpro_id
LEFT JOIN pap_order_comp AS comp ON comp.id=cpro.comp_id
LEFT JOIN pap_wip_delivery_dt AS ddt ON ddt.dt_id=dt.id
WHERE dt.po_id=:poid
GROUP BY dt.po_id,dt.cpro_id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":poid",$poid);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $i=0;
            foreach($res as $k=>$v){
                $rem = $v['qty']-$v['rcqty'];
                if($rem>0){
                    $rec = $form->show_num("receive_$i", $rem, 0.01, "", "", "", "label-inline readonly", "min='0' max='$rem' readonly", "receive[]")
                        . $form->show_hidden("dtid_$i","dtid[]",$v['id'])
                        . $form->show_hidden("rem_$i","rem[]",$rem)
                        . $form->show_hidden("compid_$i","compid[]",$v['comp_id'])
                        . $form->show_hidden("pid_$i","pid[]",$v['process_id'])
                        . $form->show_hidden("oid_$i","oid[]",$v['order_id']);
                    $loc = $form->show_text("loc_$i", "loc[]", "", "", "", "", "label-inline");
                } else {
                    $rec = "";
                    $loc = "";
                }
                array_push($res[$k],$rec,$loc);
                unset($res[$k]['id']);
                unset($res[$k]['rcqty']);
                unset($res[$k]['comp_id']);
                unset($res[$k]['process_id']);
                unset($res[$k]['order_id']);
                $i++;
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_po_rc($auth,$op,$status=null,$mm=null,$s=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE po.po_status IN (3,4,5)";
            $filter .= (isset($status)&&$status>0?" AND po.po_status=$status":"");
            $filter .= (isset($mm)?" AND DATE_FORMAT(po_created,'%m%Y')='$mm'":"");
            $filter .= (isset($s)?" AND po_code LIKE '%$s%'":"");
            if(is_null($s)&&is_null($status)&&is_null($mm)){
                $filter .= " AND po_deliveried IS NULL = 1";
            }
            if($auth>1){
                $edit = <<<END_OF_TEXT
IF(po.po_status>=5,"",CONCAT("<a href='mat_received.php?poid=",po.po_id,"' title='รับเข้า' class='icon-plus-square'></a>")),
END_OF_TEXT;
            }
            $sql = <<<END_OF_TEXT
SELECT
$edit
CONCAT("<a href='mat_received.php?action=print&poid=",po.po_id,"' title='View' target='_blank'>",po.po_code,"</a>"),
CONCAT(sup.code,":",sup.name),
DATE_FORMAT(po_created,'%d-%b'),
po.po_status AS status,
GROUP_CONCAT(dy.id) AS dyid,
GROUP_CONCAT(dy.ref) AS dyref
FROM pap_mat_po AS po
LEFT JOIN pap_supplier AS sup ON sup.id=po.supplier_id
LEFT JOIN pap_mat_delivery AS dy ON dy.po_id=po.po_id
$filter
GROUP BY po.po_id
ORDER BY po.po_created ASC
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
                $res[$k]['status'] = $op[$v['status']];
                //show delivery ref
                $dyid = explode(",",$v['dyid']);
                $dyref = explode(",",$v['dyref']);
                $dyinfo = "";
                for($i=0;$i<count($dyid);$i++){
                    $dyinfo .= ($i>0?", ":"")
                            . "<a href='mat_received.php?dyid=$dyid[$i]' title='View' target='_blank'>$dyref[$i]</a>";
                }
                array_push($res[$k],$dyinfo);
                unset($res[$k]['dyid']);
                unset($res[$k]['dyref']);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_ppo_rc($auth,$op,$status=null,$mm=null,$s=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE po.po_status IN (3,4,5)";
            $filter .= (isset($status)&&$status>0?" AND po.po_status=$status":"");
            $filter .= (isset($mm)?" AND DATE_FORMAT(po_created,'%m%Y')='$mm'":"");
            $filter .= (isset($s)?" AND po_code LIKE '%$s%'":"");
            if(is_null($s)&&is_null($status)&&is_null($mm)){
                $filter .= " AND po_deliveried IS NULL = 1";
            }
            if($auth>1){
                $edit = <<<END_OF_TEXT
IF(po.po_status=5,"",CONCAT("<a href='outsource_rc.php?poid=",po.po_id,"' title='รับเข้า' class='icon-plus-square'></a>")),
END_OF_TEXT;
            }
            $sql = <<<END_OF_TEXT
SELECT
$edit
CONCAT("<a href='outsource_rc.php?action=print&poid=",po.po_id,"' title='View' target='_blank'>",po.po_code,"</a>"),
CONCAT(sup.code,":",sup.name),
DATE_FORMAT(po_created,'%d-%b'),
po.po_status AS status,
GROUP_CONCAT(dy.id) AS dyid,
GROUP_CONCAT(dy.ref) AS dyref
FROM pap_process_po AS po
LEFT JOIN pap_supplier AS sup ON sup.id=po.supplier_id
LEFT JOIN pap_wip_delivery AS dy ON dy.po_id=po.po_id
$filter
GROUP BY po.po_id
ORDER BY po.po_created ASC
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
                $res[$k]['status'] = $op[$v['status']];
                //show delivery ref
                $dyid = explode(",",$v['dyid']);
                $dyref = explode(",",$v['dyref']);
                $dyinfo = "";
                for($i=0;$i<count($dyid);$i++){
                    $dyinfo .= ($i>0?", ":"")
                            . "<a href='outsource_rc.php?dyid=$dyid[$i]' title='View' target='_blank'>$dyref[$i]</a>";
                }
                array_push($res[$k],$dyinfo);
                unset($res[$k]['dyid']);
                unset($res[$k]['dyref']);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_po($auth,$op,$status=null,$mm=null,$s=null,$page=null,$perpage=null){
        try {
            $edit = "";
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE po.po_id>0";
            $filter .= (isset($status)&&$status>0?" AND po.po_status=$status":"");
            $filter .= (isset($mm)?" AND DATE_FORMAT(po_created,'%m%Y')='$mm'":"");
            $filter .= (isset($s)?" AND po_code LIKE '%$s%'":"");
            if(is_null($s)&&is_null($status)&&is_null($mm)){
                $filter .= " AND po_deliveried IS NULL = 1";
            }
            if($auth>1){
                $edit .= <<<END_OF_TEXT
CONCAT("<a href='paper.php?poid=",po.po_id,"' title='Edit' class='icon-page-edit'></a>"),
END_OF_TEXT;
            }

            $sql = <<<END_OF_TEXT
SELECT
$edit
CONCAT("<a href='paper.php?action=print&poid=",po.po_id,"' title='View' target='_blank'>",po.po_code,"</a>"),
CONCAT(sup.code,":",sup.name),
DATE_FORMAT(po_created,'%d-%b'),
po.po_status AS status
FROM pap_mat_po AS po
LEFT JOIN pap_supplier AS sup ON sup.id=po.supplier_id
$filter
ORDER BY po.po_created ASC
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
                $res[$k]['status'] = $op[$v['status']];
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
}
