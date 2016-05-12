<?php
include_once("p-option.php");
function prep_order($qid){
    global $op_print_toplate;
    __autoload("pappdo");
    include_once("quote_formular.php");
    $db = new PAPdb(DB_PAP);
    $data = array();
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
    foreach($unit AS $ku=>$u){
        if($u['type']==9){
            $tt = $u;
            continue;
        }
        $comp = $comps[$ku];
        if($n==1){
            $type = 9;
            $cname = "ชิ้นงาน";
        } else {
            $type = $u['type'];
            if($type==0){
                $cname = "ปก";
            } else {
                $cname = "เนื้อใน".($n>2?"($in)":"");
                $in++;
            }
        }
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
                    break;
                case 5: //ไดตัท
                    $post = explode(",",$comp['comp_postpress']);
                    foreach($post as $pid){
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
                    $pid = $process[0];
                    if($type==0){
                        //ปก ไม่พับ
                    } else if($info['cat_id']==12||$info['cat_id']==13){
                        //ใบปลิว โปสเตอร์ไม่พับ
                    } else {
                        add_data($data,$pid,$name,$u['set'],$comp_id);
                    }
                    break;
                default:
            }
        }
    }
    //loop job
    if($n>1){
        $comp_id = $db->insert_data("pap_order_comp", array(null,9,"ชิ้นงาน",$oid,null,0,0,0,"",0,0,""));
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
                foreach($pack as $pid){
                    if($pid>0){
                        add_data($data,$pid,$name,$amount,$comp_id);
                    }
                }
                break;
            case 12: //ส่ง
                $ship = explode(",",$info['shipping']);
                foreach($ship as $pid){
                    if($pid>0){
                        add_data($data,$pid,$name,$info['distance'],$comp_id);
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
    __autoload("pappdo");
    include_once("quote_formular.php");
    $db = new PAPdb(DB_PAP);
    $info = $db->get_comps_recal($compid);
    $process = $db->get_infos("pap_comp_process", "comp_id", $compid);
    
    
    $amount = $info['amount'];
    $page = $info['page'];
    $paper_lay = $info['paper_lay'];
    $allo = $info['allowance'];
    $frame = $page/$paper_lay;
    $set = plate_div($frame);
    //case สมุด
    if($info['cat_id']==69&&$info['type']==1){
        $unit['sheet'] = $amount*$page/2/$paper_lay+$allo;
        $div = (is_int($paper_lay/8)&&$paper_lay/8>=2?$paper_lay/8:1);
        $unit['cut'] = $div/2*$unit['sheet'];
        $unit['set'] = ($amount*ceil($page/2/$paper_lay)+$allo)*$div;
        $name = "1,กลับใน,,".$unit['sheet'];
        $unit['round'] = array(array($name,$unit['sheet']*2));
    } else if($page>1){
        $i = 1;
        $sheet = 0;
        $cut = 0;
        $yok = 0;
        $unit['round'] = array();
        foreach($set as $key=>$s){
            if($s>0){
                if($key==0){
                    $sheet += $temp = ($amount+$allo)*$s/2;
                    $round = $tsheet = $amount+$allo;
                    $name = "$s,กลับนอก,,".$tsheet;
                    array_push($unit['round'],array($name,$s*$round));
                    $div = (is_int($paper_lay/8)&&$paper_lay/8>=2?$paper_lay/8:1);
                    $cut += ($div>1?$div/2*$temp:0);
                    $yok += $div*$temp;
                } else {
                    $ss = ($s>0.5?1:$s);
                    $sheet += $tsheet = $amount*$ss/2+$allo;
                    $round = $tsheet*2;
                    $name = "$s,กลับใน,,".$tsheet;
                    array_push($unit['round'],array($name,$round));
                    $div = (is_int($paper_lay*$s/2/8)&&$paper_lay*$s/2/8>=2?$paper_lay*$s/2/8:1);
                    $cut += $div*($amount+$allo);
                    $yok += $div*($amount+$allo);
                    $i++;
                }
            }
        }
        $unit['sheet'] = $sheet;
        $unit['cut'] = $cut;
        $unit['set'] = $yok;
    } else {
        $unit['sheet'] = $amount*$frame+$allo;
        $name = "$frame,หน้าเดียว,,".$unit['sheet'];
        $unit['round'] = array(array($name,$unit['sheet']));
        $unit['cut'] = $amount+$allo;
        $unit['set'] = $amount+$allo;
    }
    $paperinfo = $db->get_info("pap_mat", "mat_id", $info['paper_id']);
    $sheet = ceil($unit['sheet']/$paperinfo['mat_order_lot_size'])*$paperinfo['mat_order_lot_size'];
    $i=0;
    //var_dump($unit);
    //var_dump($process);
    foreach($process as $k => $v){
        $pinfo = $db->get_info("pap_process", "process_id", $v['process_id']);
        $cat = $pinfo['process_cat_id'];
        $name = explode(",",$v['name']);
        if(count($name)>1){
            $setup = ceil($name[0]);
            $nname = str_replace(",,",",$name[2],",$unit['round'][$i][0]);
            $i++;
        } else {
            $setup = 1;
            $nname = $name[0];
        }
        switch ($cat){
            case 3: //พิมพ์
                $arrinfo = array(
                    "volume" => $unit['round'][$i-1][1],
                    "est_time_hour" => time_cal($pinfo, $setup, $unit['round'][$i-1][1]),
                    "name" => $nname
                );
                $db->update_data("pap_comp_process", "id", $v['id'], $arrinfo);
                //var_dump($arrinfo);
                break;
            case 6: //ตัด
                $arrinfo = array(
                    "volume" => $unit['cut'],
                    "est_time_hour" => time_cal($pinfo, $setup, $unit['cut'])
                );
                $db->update_data("pap_comp_process", "id", $v['id'], $arrinfo);
                //var_dump($arrinfo);
                break;
            case 7: //พับ
                $arrinfo = array(
                    "volume" => $unit['set'],
                    "est_time_hour" => time_cal($pinfo, $setup, $unit['set'])
                );
                $db->update_data("pap_comp_process", "id", $v['id'], $arrinfo);
                //var_dump($arrinfo);
                break;
            default: //2,4,5
                $arrinfo = array(
                    "volume" => $sheet,
                    "est_time_hour" => time_cal($pinfo, $setup, $sheet)
                );
                $db->update_data("pap_comp_process", "id", $v['id'], $arrinfo);
                //var_dump($arrinfo);
        }
    }
}