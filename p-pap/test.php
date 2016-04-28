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
/* update process meta
$pid = $db->get_keypair("pap_process", "process_id", "process_id");
foreach($pid as $k=>$v){
    $db->update_meta("pap_process_meta", "process_id", $v, array("pc_show"=>1));
    echo $v;
}
 * 
 */