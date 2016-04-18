<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
__autoload("pappdo");
__autoloada("phpcsv");
if(!$_POST){
    header("location:".ROOTS);
}
$req = filter_input(INPUT_POST,'request',FILTER_SANITIZE_STRING);
$up = new phpcsv();
$db = new PAPdb(DB_PAP);
if($req=="upload_paper"){
    /*
     * 0 = type
     * 1 = size
     * 2 = weight
     * 3 = lot size
     * 4 = cost
     * 5 = lead time
     * 
     */
    $dir = "upload/";
    $fileurl = $up->move_file($_FILES['csv-input'], $dir);
    if($fileurl==false){
        $_SESSION['error'] = "Upload file Error";
        header("Location:".$_POST['redirect']);
        exit();
    } else {
        $paper_type = $db->get_keypair("pap_option", "op_name", "op_id","WHERE op_type='paper_type'");
        $paper_size = $db->get_keypair("pap_option", "op_name", "op_id","WHERE op_type='paper_size'");
        $paper_weight = $db->get_keypair("pap_option", "op_name", "op_id","WHERE op_type='paper_weight'");
        $file = fopen($fileurl,"r");
        while(!feof($file)){
            $row = fgetcsv($file);
            //var_dump($row);
            //check type
            $type = "paper_type";
            if(!$db->check_optdup($type,$row[0])){
                $t_id = $db->insert_data("pap_option", array(null,$type,$row[0],""));
            } else {
                $t_id = $paper_type[$row[0]];
            }
            //check size
            $type = "paper_size";
            if(!$db->check_optdup($type,$row[1])){
                $size = explode("x",$row[1]);
                $val = json_encode(array("width"=>$size[0],"length"=>$size[1]));
                $db->insert_data("pap_option", array(null,$type,$row[1],$val));
            } else {
                $s_id = $paper_size[$row[1]];
            }
            //check weight
            $type = "paper_weight";
            if(!$db->check_optdup($type,$row[2])){
                $db->insert_data("pap_option", array(null,$type,$row[2],""));
            } else {
                $w_id = $paper_weight[$row[2]];
            }
            
            //add mat
            $field = "CONCAT(mat_type,mat_size,mat_weight)";
            $val = $t_id.$s_id.$w_id;
            $psize = explode("x",$row[1]);
            $pweight = $row[2];
            $cost_per_u = round($psize[0]*$psize[1]*$pweight*$row[4]/(3100*500),5);
            $name = $row[0]." ".$row[1]." ".$row[2]."g";
            $cat = 8;
            if($db->check_dup("pap_mat", $field, $val)){
                $info = $db->get_info("pap_mat", $field, $val);
                $mid = $info['mat_id'];
                //update
                $arrinfo = array(
                    "mat_name" => $name,
                    "mat_unit" => "แผ่น",
                    "mat_cat_id" => $cat,
                    "mat_type" => $t_id,
                    "mat_size" => $s_id,
                    "mat_weight" => $w_id,
                    "mat_order_lot_size" => $row[3],
                    "mat_std_cost" => $cost_per_u,
                    "mat_std_leadtime" => $row[5]
                );
                $db->update_data("pap_mat", "mat_id", $mid, $arrinfo);

                //mat meta
                $meta = array(
                    "paper_cost_base" => "baht/kg",
                    "paper_cost" => $row[4]
                );
                $db->update_meta("pap_matmeta", "mat_id", $mid, $meta);
            } else {
                //add
                $mid = $db->insert_data("pap_mat", array(null,"",$name,"แผ่น",$cat,$t_id,$s_id,$w_id,$row[3],$cost_per_u,$row[5]));
                //mat meta
                $meta = array(
                    "paper_cost_base" => "baht/kg",
                    "paper_cost" => $row[4]
                );
                $db->update_meta("pap_matmeta", "mat_id", $mid, $meta);
            }
        }
        fclose($file);
        $_SESSION['message'] = "Upload completed";
        header("Location:".$_POST['redirect']);
        exit();
    }
} else if($req=="upload_customer_group"){
    /*
     * 0 = name
     * 1 = code
     * 2 = description
     * 
     */
    $dir = "upload/";
    $fileurl = $up->move_file($_FILES['csv-input'], $dir);
    if($fileurl==false){
        $_SESSION['error'] = "Upload file Error";
    } else {
        $file = fopen($fileurl,"r");
        $tax = "customer";
        while(!feof($file)){
            $row = fgetcsv($file);
            var_dump($row);
            //insert data
            $tid = $db->insert_data("pap_term", array(null,$row[0],$row[1],$row[2]));
            //add tax
            $db->insert_data("pap_term_tax", array(null,$tid,$tax,0,$tid,0,0));
        }
        fclose($file);
        $_SESSION['message'] = "Upload completed";
    }
    header("Location:".$_POST['redirect']);
    exit();
} else if($req=="upload_customer"){
    /*
     * 12 = ct_name
     * 13 = ct_email
     * 14 = ct_tel
     * 15 = cus_cat
     * 16 = cus_status
     */
    $dir = "upload/";
    $fileurl = $up->move_file($_FILES['csv-input'], $dir);
    if($fileurl==false){
        $_SESSION['error'] = "Upload file Error";
    } else {
        $file = fopen($fileurl,"r");
        $tax = "customer";
        while(!feof($file)){
            $row = fgetcsv($file);
            $code = $db->check_cus_code($row[15]);
            $arrinfo = array(null,$code);
            for($i=0;$i<12;$i++){
                array_push($arrinfo,$row[$i]);
            }
            array_push($arrinfo,pap_now(),$row[16]);
            $cid = $db->insert_data("pap_customer",$arrinfo);
            
            //add cat
            $db->insert_data("pap_customer_cat", array($row[15],$cid));

            //add contact
            $db->insert_data("pap_contact",array(null,$cid,1,$row[12],$row[13],$row[14],""));
        }
        fclose($file);
        $_SESSION['message'] = "Upload completed";
    }
    //header("Location:".$_POST['redirect']);
    exit();
}