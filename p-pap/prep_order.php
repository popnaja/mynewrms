<?php
include_once("p-option.php");
function prep_order($qid){
    global $op_comp_type;
    __autoload("pappdo");
    include_once("quote_formular.php");
    $db = new PAPdb(DB_PAP);
    $data = array();
    $process_name = $db->get_keypair("pap_process", "process_id", "process_name");
    if($db->check_dup("pap_order","quote_id",$qid)){
        $db->delete_data("pap_order", "quote_id", $qid);
        //return false;
    } 
    $comps = $db->get_comp($qid);
    $info = $db->get_info("pap_quotation","quote_id",$qid)+$db->get_meta("pap_quote_meta","quote_id",$qid);
    $catinfo = $db->get_info("pap_option", "op_id", $info['cat_id']);
    
    //$order_no = $db->check_order_no();
    $oid = $db->insert_data("pap_order", array(null,$info['quote_no'],$qid,1,pap_now(),null,null,null,null,null,null,null,null,null,null,null,"",""));
    //check component
    
    $plist = explode(",",$catinfo['op_value']);
    $exclude = explode(",",$info['exclude']);
    $unit = unit_cal($info,$comps);
    $n = count($comps);
    $amount = $info['amount'];
    //loop component
    $name = null;
    $in=1;
    foreach($op_comp_type AS $k=>$v){
        $run[$k] = 0;
    }
    //coat2
    $coat2 = json_decode($info['coat2'],true);
    foreach($unit AS $ku=>$u){
        if($u['type']==9){
            $tt = $u;
            continue;
        }
        $comp = $comps[$ku];
        $c2 = $coat2[$ku];
        //name
        $type = $u['type'];
        $run[$type]++;
        $cname = $op_comp_type[$u['type']].($run[$type]>1?" ($run[$type])":"");
        $pinfo = $db->get_info("pap_mat","mat_id",$u['paper_id']);
        $pz = $db->get_info("pap_option","op_id",$pinfo['mat_size']);
        if($u['paper_cut']==2){
            $tz = json_decode($pz['op_value'],true);
            $printsize = number_format($tz['length']/2,2)."x".number_format($tz['width'],2);
        } else {
            $printsize = $pz['op_name'];
        }
        $sheet = ceil($u['sheet']/$pinfo['mat_order_lot_size'])*$pinfo['mat_order_lot_size'];
        $paper_use_lot = round(ceil($u['sheet']/$u['paper_cut']/$pinfo['mat_order_lot_size'])*$pinfo['mat_order_lot_size']/500,2);
        $comp_id = $db->insert_data("pap_order_comp", array(null,$type,$cname,$oid,$u['paper_id'],$paper_use_lot,$u['paper_lay'],$u['paper_cut'],$printsize,$u['page'],$u['allo'],""));
        
        foreach($plist as $k => $v){
            $process = $db->get_mm_arr("pap_process", "process_id", "process_cat_id", $v);
            switch ($v){
                case 1: //ออกแบบ
                    /*
                    if(!in_array(1,$exclude)){
                        foreach($u['frame'] as $key=>$value){
                            $pid = $op_print_toplate[$key];
                            add_data($data,$pid,$name,$value,$comp_id);
                        }
                    }
                     * 
                     */
                    break;
                case 2: //เจียน
                    $pid = $process[0];
                    add_data($data,$pid,$name,$sheet,$comp_id);
                    break;
                case 3: //พิมพ์
                    $pid = $u['print_id'];
                    foreach($u['round'] AS $key=>$value){
                        add_data($data,$pid,$value[0],$value[1],$comp_id);
                    }
                    break;
                case 4: //เคลือบ
                    $coat = explode(",",$comp['comp_coating']);
                    foreach($coat as $pid){
                        if($pid>0){
                            add_data($data,$pid,$name,$sheet,$comp_id);
                        }
                    }
                    if($c2>0){
                        $coat2_name = $process_name[$c2]."(ด้านหลัง)";
                        add_data($data,$c2,$coat2_name,$sheet,$comp_id);
                    }
                    break;
                case 5: //ไดตัท
                    $post = explode(",",$comp['comp_postpress']);
                    foreach($post as $pdata){
                        $pdata = explode(";",$pdata);
                        $pid = $pdata[0];
                        if($pid>0){
                            add_data($data,$pid,$name,$sheet,$comp_id);
                        }
                    }
                    break;
                case 6: //ตัด
                    if($u['cut']>0){
                        $pid = $process[0];
                        add_data($data,$pid,$name,$u['cut'],$comp_id);
                    }
                    break;
                case 7: //พับ
                    $pid = ($type==3&&$info['folding']>0?$info['folding']:$process[0]);
                    if($type==1){
                        //ปก ไม่พับ
                    } else if(in_array($info['cat_id'],array(11,12,13))){ //ใบปลิว โปสเตอร์ แผ่นพับ
                        if(isset($info['folding'])&&$info['folding']>0){
                            add_data($data,$info['folding'],$name,$u['set'],$comp_id);
                        }
                    } else {
                        foreach($u['sinfo'] as $kk=>$vv){
                            add_data($data,$vv['foldid'],$name,$vv['set'],$comp_id);
                        }
                    }
                    break;
                default:
            }
        }
    }
    //loop job
    if($n>1){
        $comp_id = $db->insert_data("pap_order_comp", array(null,9,"รวมเล่ม",$oid,null,0,0,0,"",0,0,""));
    }
    foreach($plist as $k=>$v){
        $process = $db->get_mm_arr("pap_process", "process_id", "process_cat_id", $v);
        switch($v){
            case 8: //เก็บ
                $pid = $process[0];
                add_data($data,$pid,$name,$tt['set'],$comp_id);
                break;
            case 9: //เข้าเล่ม
                $pid = $info['binding_id'];
                add_data($data,$pid,$name,$amount,$comp_id);
                break;
            case 10: //ตัดสัน
                $pid = $process[0];
                add_data($data,$pid,$name,$amount,$comp_id);
                break;
            case 11: //แพ็ค
                $pack = explode(",",$info['packing']);
                foreach($pack as $pdata){
                    $pdata = explode(";",$pdata);
                    $pid = $pdata[0];
                    if($pid>0){
                        add_data($data,$pid,$name,$pdata[1],$comp_id);
                    }
                }
                break;
            case 12: //ส่ง
                $ship = explode(",",$info['shipping']);
                foreach($ship as $pdata){
                    $pdata = explode(";",$pdata);
                    $pid = $pdata[0];
                    if($pid>0){
                        add_data($data,$pid,$name,$pdata[1],$comp_id);
                    }
                }
                break;
            default:
        }
    }
    $db->add_comp_process($data);
    return true;
}
function time_cal($pinfo,$setup,$vol){
    if($pinfo['process_source']==1){
        $hour = $pinfo['process_setup_min']*$setup/60+$vol/$pinfo['process_cap'];
        return round($hour,2);
    } else {
        return $pinfo['process_std_leadtime_hour'];
    }
}
function add_data(&$data,$pid,$n,$vol,$cid){
    include_once("class.pappdo.php");
    $db = new PAPdb(DB_PAP);
    $pinfo = $db->get_info("pap_process", "process_id", $pid);

    if(isset($n)){
        $name = $n;
        $tprint = explode(",",$n);
        $setup = ceil($tprint[0]);
    } else {
        $setup = 1;
        $name = $pinfo['process_name'];
    }
    $time = time_cal($pinfo,$setup,$vol);
    array_push($data,array($cid,$pid,$name,$vol,$time));
}
function recal_process($compid){
    global $op_print_toplate,$op_print_color;
    __autoload("pappdo");
    include_once("quote_formular.php");
    $db = new PAPdb(DB_PAP);
    $process_name = $db->get_keypair("pap_process", "process_id", "process_name");
    $info = $db->get_comps_recal($compid);
    $qinfo = $db->get_info("pap_quotation", "quote_id", $info['quote_id'])+$db->get_meta("pap_quote_meta", "quote_id", $info['quote_id']);
    $order_comp = $db->get_infos("pap_order_comp", "order_id", $info['order_id']);
    $comps = $db->get_comp($info['quote_id']);
    $coat2 = json_decode($qinfo['coat2'],true);
    for($i=0;$i<count($comps);$i++){
        if($order_comp[$i]['id']==$compid){
            $comp = $comps[$i];
            $c2 = $coat2[$i];
            break;
        }
    }
    $color = $op_print_color[$comp['comp_print_id']];
    $amount = $info['amount'];
    $page = $info['page'];
    $paper_lay = $info['paper_lay'];
    $allo = $info['allowance'];
    $frame = $page/$paper_lay;
    $set = plate_div($frame);
    $type = $info['type'];
    $unit = array(
        "sheet" => 0,
        "set" => 0,
        "kong" => 0,
        "cut" => 0
    );
    if($type==2||$type==6){                //เนื้อใน
        $frame = $page/$paper_lay;
        $unit['sinfo'] = array();
        if($info['cat_id']==69){               //case สมุด
            $unit['frame'] = array($op_print_toplate[$comp['comp_print_id']]=>1);
            $unit['sheet'] = $amount*$page/2/$paper_lay+$allo;
            $name = "1,กลับใน,$color/$color,".$unit['sheet'];
            $unit['round'] = array(array($name,$unit['sheet']*2));
            $unit['color'] = $color."/$color";
            //check folding
            $st = $paper_lay*2;
            check_folding($unit,$st, $unit['sheet']);
        } else {                                //case หนังสือทั่วไป
            $unit['frame'] = array($op_print_toplate[$comp['comp_print_id']]=>ceil($frame));
            $unit['round'] = array();
            $unit['color'] = $color."/$color";
            $fdiv = plate_div($frame);
            foreach($fdiv as $key=>$s){
                if($key==0&&$s>0){
                    $unit['sheet'] += ($amount+$allo)*$s/2;
                    $round = $tsheet = $amount+$allo;
                    $name = "$s,กลับนอก,$color/$color,".$tsheet;
                    array_push($unit['round'],array($name,$s*$round));
                    //check folding
                    $st = $paper_lay*2;
                    check_folding($unit,$st,$tsheet,$s/2);
                } else if($s>0) {
                    $ss = ($s>0.5?1:$s);
                    $unit['sheet'] += $tsheet = $amount*$ss/2+$allo;
                    $round = $tsheet*2;
                    $name = "$s,กลับใน,$color/$color,".$tsheet;
                    array_push($unit['round'],array($name,$round));
                    //check folding
                    $tkong = check_folding($unit,$paper_lay*2,$tsheet,1,$s*$paper_lay);
                    $unit['kong'] -= $tkong-1; //แก้จำนวนกองของเศษ
                }
            }
        }
    } else {            //อื่นๆ ปก ใบพาด แจ็คเก็ด
        $piece = $page;
        $unit['kong'] = (in_array($type,array(4,5,7))?0:$piece);
        $sheet = $unit["sheet"] = $amount*$piece/$paper_lay+$allo;
        $unit['cut'] = $sheet*$paper_lay;
        $unit['set'] = $sheet*$paper_lay;
        $frame = $page*($comp['comp_print2']>0?2:1)/$paper_lay;
        $unit['frame'] = array($op_print_toplate[$comp['comp_print_id']]=>ceil($frame));
        if($comp['comp_print2']==0){            //พิมพ์ด้านเดียว
            $name = "$frame,หน้าเดียว,$color/0,".$unit['sheet'];
            $unit['round'] = array(array($name,$unit['sheet']));
            $unit['color'] = $color."/0";
        } else if($comp['comp_print2']==$comp['comp_print_id']){     //พิมพ์สองด้านสีเดียวกัน
            $name = "$frame,กลับใน,$color/$color,".$sheet;
            $unit['round'] = array(array($name,$sheet*2));
            $unit['color'] = $color."/".$color;
        } else {                                //case พิมพ์ 2 ด้าน สี่ไม่เหมือน เช่น 4/1 ทำเหมือน สีเดียวกัน lay รวมไปเลย ประหยัด plate และเวลาเปลี่ยนเพลต
            $color2 = $op_print_color[$comp['comp_print2']];
            $name = "1,กลับใน,$color/$color2,".$sheet;
            $unit['round'] = array(array($name,$sheet*2));
            $unit['color'] = $color."/".$color2;
        }
    }
    $paperinfo = $db->get_info("pap_mat", "mat_id", $info['paper_id']);
    $sheet = ceil($unit['sheet']/$paperinfo['mat_order_lot_size'])*$paperinfo['mat_order_lot_size'];
    $i=0;

    $catinfo = $db->get_info("pap_option", "op_id", $info['cat_id']);
    $plist = explode(",",$catinfo['op_value']);
    $data = array();
    $name = null;
    foreach($plist as $k => $v){
        $process = $db->get_mm_arr("pap_process", "process_id", "process_cat_id", $v);
        switch ($v){
            case 1: //ออกแบบ
                break;
            case 2: //เจียน
                $pid = $process[0];
                add_data($data,$pid,$name,$sheet,$compid);
                break;
            case 3: //พิมพ์
                $pid = $comp['comp_print_id'];
                foreach($unit['round'] AS $key=>$value){
                    add_data($data,$pid,$value[0],$value[1],$compid);
                }
                break;
            case 4: //เคลือบ
                $coat = explode(",",$comp['comp_coating']);
                foreach($coat as $pid){
                    if($pid>0){
                        add_data($data,$pid,$name,$sheet,$compid);
                    }
                }
                if($c2>0){
                    $coat2_name = $process_name[$c2]."(ด้านหลัง)";
                    add_data($data,$c2,$coat2_name,$sheet,$compid);
                }
                break;
            case 5: //ไดตัท
                $post = explode(",",$comp['comp_postpress']);
                foreach($post as $pdata){
                    $pdata = explode(";",$pdata);
                    $pid = $pdata[0];
                    if($pid>0){
                        add_data($data,$pid,$name,$sheet,$compid);
                    }
                }
                break;
            case 6: //ตัด
                if($unit['cut']>0){
                    $pid = $process[0];
                    add_data($data,$pid,$name,$unit['cut'],$compid);
                }
                break;
            case 7: //พับ
                $pid = ($type==3&&$qinfo['folding']>0?$qinfo['folding']:$process[0]);
                if($type==1){
                    //ปก ไม่พับ
                } else if(in_array($info['cat_id'],array(11,12,13))){ //ใบปลิว โปสเตอร์ แผ่นพับ
                    if(isset($qinfo['folding'])&&$qinfo['folding']>0){
                        add_data($data,$qinfo['folding'],$name,$unit['set'],$compid);
                    }
                } else {
                    foreach($unit['sinfo'] as $kk=>$vv){
                        add_data($data,$vv['foldid'],$name,$vv['set'],$compid);
                    }
                }
                break;
            default:
        }
    }
    //del old comp process
    $db->delete_data("pap_comp_process", "comp_id", $compid);
    
    //add new
    $db->add_comp_process($data);
}