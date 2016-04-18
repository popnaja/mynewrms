<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
include_once(dirname(dirname(__FILE__))."/p-admin/email_function.php");

__autoloada("table");
__autoload("pdo_tb");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$tb = new mytable();
$tbpdo = new tbPDO();
$head = array("วันที่","บันทึก");
$rec = $tbpdo->view_note(4,9,1);
$uinfo = $db->get_info("pap_user","user_id",1);
$cinfo = $db->get_info("pap_customer","customer_id",9);
$ct = "<h1 style='font-size:18px'>รายงานบันทึกการติดต่อลูกค้า ".$cinfo['customer_code']." : ".$cinfo['customer_name']."</h1>"
            . "<p style='font-size:16px;color:#444;'>โดย ".$uinfo['user_login']
            . $tb->show_email_table($head,$rec,"tb-note");
$body = note_email("รายงานบันทึกการติดต่อลูกค้า D00001 : บจก สี่", $ct);
echo $body;