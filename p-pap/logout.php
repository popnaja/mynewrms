<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$meta = array(
    "l_status" => ""
);
$db->update_meta("pap_usermeta","user_id",$_SESSION['upap'][0], $meta);
session_unset();
header("location:".PAP);
exit();

