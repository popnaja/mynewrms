<?php
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
function cal_quote($info,$comps){
    global $op_comp_type;
    global $op_print_toplate;
    __autoload("pappdo");
    $db = new PAPdb(DB_PAP);
    
    $processes = $db->get_keypair("pap_process", "process_id", "process_name");
    //calculate

    $units = unit_cal($info, $comps);
    $res['ออกแบบ'] = array();
    $res['ทำเพลต'] = array();
    $res['กระดาษ'] = array();
    $res['พิมพ์'] = array();
    $res['หลังพิมพ์'] = array();
    $res['แพ็ค'] = array();
    $res['ขนส่ง'] = array();
    $res['อื่นๆ'] = array();
  
    $ex = explode(",",$info['exclude']);
    $num = count($units);
    foreach($op_comp_type AS $k=>$v){
        $run[$k] = 0;
    }
    $coat2 = (isset($info['coat2'])?json_decode($info['coat2'],true):0);
    $coatpage = (isset($info['coatpage'])?json_decode($info['coatpage'],true):0);
    for($i=0;$i<$num;$i++){
        $unit = $units[$i];
        //var_dump($unit);
        if($unit['type']==9){
            $overall = $unit;
            //prepress
            $pp = explode(",",$info['prepress']);
            foreach($pp as $pid){
                if($pid>0){
                    $pcost = new_pcost($pid, $unit);
                    array_push($res['ออกแบบ'],array_merge(array($processes[$pid]),$pcost));
                }
            }

            //packing
            $pack = explode(",",$info['packing']);
            foreach($pack as $pro){
                $pro = explode(";",$pro);
                $pid = $pro[0];
                if($pid>0){
                    $meta = $db->get_meta("pap_process_meta", "process_id", $pid);
                    $cost = json_decode($meta['cost'],true);
                    if(isset($pro[1])){
                        $tunit = $unit;
                        $tunit[$cost[0]['vunit']]=$pro[1];
                    }
                    $pcost = new_pcost($pid, $tunit);
                    array_push($res['แพ็ค'],array_merge(array($processes[$pid]),$pcost));
                }
            }
            //shipping
            $ship = explode(",",$info['shipping']);
            foreach($ship as $pro){
                $pro = explode(";",$pro);
                $pid = $pro[0];
                if($pid>0){
                    $meta = $db->get_meta("pap_process_meta", "process_id", $pid);
                    $cost = json_decode($meta['cost'],true);
                    if(isset($pro[1])){
                        $tunit = $unit;
                        $tunit[$cost[0]['vunit']]=$pro[1];
                    }
                    $pcost = new_pcost($pid, $tunit);
                    array_push($res['แพ็ค'],array_merge(array($processes[$pid]),$pcost));
                }
            }
            continue;
        }
        //var_dump($unit['round']);
        $com = $comps[$i];
        //name
        $type = $unit['type'];
        $run[$type]++;
        $cname = $op_comp_type[$unit['type']].($run[$type]>1?" ($run[$type])":"");

        //ทำเพลต
        if(!in_array("1",$ex)){
            $temp = array();
            foreach($unit['finfo'] as $key=>$val){
                $tunit = $val+$unit;
                //var_dump($tunit);
                $fid = $val['frameid'];
                $pcost = new_pcost($fid,$tunit);
                array_push($res['ทำเพลต'],array_merge(array($cname." ".str_replace("ทำเพลต","",$processes[$fid])),$pcost));
            }
        }
        //paper
        $pinfo = $db->get_info("pap_mat","mat_id",$unit['paper_id']);
        $size = $db->get_keypair("pap_option", "op_id", "op_value", "WHERE op_type='paper_size'");
        $weight = $db->get_keypair("pap_option","op_id","op_name","WHERE op_type='paper_weight'");
        $lot = $pinfo['mat_order_lot_size'];
        $lots = ceil($unit['sheet']/$unit['paper_cut']/$lot);
        $rims = $lots*$lot/500;
        if(!in_array("2",$ex)){
            $c_per_rim = round(500*$pinfo['mat_std_cost'],2);
        } else {
            $c_per_rim = 0;
        }
        $wh = json_decode($size[$pinfo['mat_size']],true);
        $paperinfo = array(
            "width" => $wh['width'],
            "length" => $wh['length'],
            "weight" => $weight[$pinfo['mat_weight']],
            "amount" => $rims
        );
        array_push($res['กระดาษ'],array("$cname<br/>".$wh['width']."x".$wh['length'],$rims,$c_per_rim,json_encode($paperinfo),$rims*$c_per_rim));
        
        //printing
        //var_dump($unit);
        if(count($unit['frame'])>1){
            $color = explode("/",$unit['color']);
            foreach($unit['round'] as $k=>$v){
                $kinfo = explode(",",$v[0]);
                $round = $v[1]/ceil($kinfo[0]);
            }
            $tunit = $unit;
            $tunit['round'] = $round;
            $pcost = new_pcost($unit['print_id'], $tunit);
            array_push($res['พิมพ์'],array("$cname นอก ($color[0] สี)",$round,$pcost[1],$pcost[2],$pcost[3]));
            $pcost = new_pcost($unit['print_id2'], $tunit);
            array_push($res['พิมพ์'],array("$cname ใน ($color[1] สี)",$round,$pcost[1],$pcost[2],$pcost[3]));
        } else {
            foreach($unit['round'] as $k=>$v){
                $kinfo = explode(",",$v[0]);
                $round = $v[1]/ceil($kinfo[0]);
                $tunit = $unit;
                $tunit['round'] = $round;
                $pcost = new_pcost($unit['print_id'], $tunit);
                $frameinfo = json_decode($pcost[2],true)+array("frame"=>$kinfo[0]);
                $check = ($kinfo[0]==0.75?"quote-red":"");
                array_push($res['พิมพ์'],array("$cname $kinfo[1]","<span class='$check'>$kinfo[0] กรอบ</span> ($round รอบ/กรอบ)",$pcost[1],json_encode($frameinfo),$pcost[3]*ceil($kinfo[0])));
            }
            
        }
        
        //update unit['sheet'] for coating and post press
        $unit['sheet'] = $lots*$lot;
        //coating เคลือบคิดเต็มริม
        if($com['comp_coating']>0){
            $cost = new_pcost($com['comp_coating'],$unit);
            $cpage = (is_array($coatpage)&&$coatpage[$i]>0?$coatpage[$i]." หน้า":"");
            array_push($res['หลังพิมพ์'],array_merge(array($processes[$com['comp_coating']]." $cname ".$cpage),$cost));
        }
        //coat2
        if(is_array($coat2)&&$coat2[$i]>0){
            $cost = new_pcost($coat2[$i],$unit);
            array_push($res['หลังพิมพ์'],array_merge(array($processes[$coat2[$i]]." $cname(ด้านใน)"),$cost));
        }
        //post process หลังงานพิมพ์คิดเต็มริม
        $post = explode(",",$com['comp_postpress']);
        foreach($post as $pro){
            $pro = explode(";",$pro);
            $pid = $pro[0];
            if($pid>0){
                $meta = $db->get_meta("pap_process_meta", "process_id", $pid);
                $cost = json_decode($meta['cost'],true);
                if(isset($pro[1])){
                    $tunit = $unit;
                    $tunit[$cost[0]['vunit']]=$pro[1];
                }
                $pcost = new_pcost($pid, $tunit);
                array_push($res['หลังพิมพ์'],array_merge(array($processes[$pid]." ".$cname),$pcost));
            }
        }
    }
    //folding
    $fpro= $db->get_mm_arr("pap_process", "process_id", "process_cat_id", 7);   //cat id 7 = พับ
    $folding_id = (isset($info['folding'])?$info['folding']:$fpro[0]);

    //ถ้า เป็นหนังสือ cat_id=10 แผ่นพับ cat_id=11 มีการพับ
    if(in_array($info['cat_id'],array(10,11,69))||isset($info['folding'])){
        if(isset($overall['sinfo'])){
            foreach($overall['sinfo'] as $fid=>$set){
                $pcost = new_pcost($fid, array("set"=>$set));
                array_push($res['หลังพิมพ์'],array_merge(array($processes[$fid]),$pcost));
            }
        } else if($folding_id>0) {
            $pcost = new_pcost($folding_id, $overall);
            array_push($res['หลังพิมพ์'],array_merge(array($processes[$folding_id]),$pcost));
        }
    }

    //collecting
    //ถ้า เป็นหนังสือ มีการเก็บ + เข้าเล่ม
    $cpro= $db->get_mm_arr("pap_process", "process_id", "process_cat_id", 8);   //cat id 8 = เก็บเล่ม
    $collect_id = $cpro[0];
    if(in_array($info['cat_id'],array(10,69))){
        $pcost = new_pcost($collect_id, $overall);
        array_push($res['หลังพิมพ์'],array_merge(array($processes[$collect_id]),$pcost));

        //binding
        $pcost = new_pcost($info['binding_id'], $overall);
        array_push($res['หลังพิมพ์'],array_merge(array($processes[$info['binding_id']]),$pcost));
    }
    //อื่นๆ
    if(isset($info['other_price'])){
        $other = json_decode($info['other_price'],true);
        foreach($other as $k=>$oinfo){
            array_push($res['อื่นๆ'],array($oinfo[0],0,0,$oinfo[1]));
        }
    }
    return $res;
}
function print_cost($process_id,$amount){
    global $db;
    $meta = $db->get_meta("pap_process_meta","process_id",$process_id);
    $cost = json_decode($meta['cost'],true);
    foreach($cost AS $value){
        if($value['cost']>0){
            $cinfo = max($value['cost']*$amount,$value['min']);
            $cost_per_u = $value['cost'];
        }
    }
    return array($cinfo,$cost_per_u);
}
function new_pcost($pid,$arrinfo){
    global $db;
    global $op_unit;
    $meta = $db->get_meta("pap_process_meta","process_id",$pid);
    $cost = json_decode($meta['cost'],true);
    foreach($cost AS $k=>$value){
        $amount = $arrinfo[$value['vunit']];
        if($value['cond']!="0"){
            $check = $arrinfo[$value['cond']];
            if($check>=$value['btw']&&$check<=($value['to']>0?$value['to']:INF)){
                $res = cost_formular($value,$amount,$arrinfo);
                array_unshift($res,$amount);
                break;
            }
        } else {
            $res = cost_formular($value,$amount,$arrinfo);
            array_unshift($res,$amount);
        }
    }
    if(!isset($res)){
        echo "Recheck cost formular of process id ".$pid;
        $res = cost_formular($cost[0],$arrinfo[$cost[0]['vunit']],$arrinfo);
        array_unshift($res,$arrinfo[$cost[0]['vunit']]);
    }
    return $res;
}
function cost_formular($value,$amount,$arrinfo){
    $fcost = (isset($value['fcost'])?$value['fcost']:0);
    if(isset($value['formular'])&&$value['formular']!=""){
        $for = $value['formular'];
        foreach($arrinfo as $k=>$v){
            if(!is_array($v)){
                $for = str_replace($k,$v,$for);
            }
        }
        $cinfo = max($value['min'],calculate_string($for));
        $cost_per_u = $value['formular'];
    } else {
        $cinfo = max($fcost+$value['cost']*$amount,$value['min']);
        $cost_per_u = $value['cost'];
    }
    return array($cost_per_u,json_encode(array("amount"=>$amount)+$value),$cinfo);
}
function calculate_string( $mathString )    {
    $mathString = trim($mathString);     // trim white spaces
    // remove any non-numbers chars; exception for math operators
    $mathString = preg_replace ('/[^0-9\\+\\-\\*\\/\\(\\)\\. ]/', '', $mathString);
    $compute = create_function("", "return (" . $mathString . ");" );
    return 0 + $compute();
}
function plate_div($num){
    $res[0] = floor($num/2)*2;
    $res[1] = floor($num % 2);
    $t = $num - floor($num);
    $res[2] = ceil($t*4)/4;
    return $res;
}
function unit_cal($quote,$comps){
    __autoload("pappdo");
    $db = new PAPdb(DB_PAP);
    include_once("p-option.php");
    global $op_print_color,$op_print_toplate,$op_set_id;
    $set_id = $op_set_id;
    $n = count($comps);
    $amount = $quote['amount'];
    $res = array();
    $c = 0;
    $coatpage = (isset($quote['coatpage'])?json_decode($quote['coatpage']):0);
    foreach($comps as $k=>$comp){
        $color = $op_print_color[$comp['comp_print_id']];
        $allo = $res[$k]['allo'] = $comp['comp_paper_allowance'];
        $paper_lay = $comp['comp_paper_lay'];
        $paper_cut = $comp['comp_paper_cut'];
        $res[$k]['type'] = $comp['comp_type'];
        $page = $res[$k]['page'] = $comp['comp_page'];
        $pinfo = $db->get_info("pap_mat", "mat_id", $comp['comp_paper_id']);
        $size = $db->get_info("pap_option","op_id",$pinfo['mat_size']);
        $sinfo = json_decode($size['op_value'],true);
        
        $res[$k]['paper_id'] = $comp['comp_paper_id'];
        $res[$k]['paper_lay'] = $paper_lay;
        $res[$k]['paper_cut'] = $paper_cut;
        $res[$k]['print_id'] = $comp['comp_print_id'];
        $res[$k]['print_id2'] = $comp['comp_print2'];
        $res[$k]["piece"] = $amount;
        
        $type = $comp['comp_type'];
        if($type==2||$type==6){                   //เนื้อใน
            $frame = $page/$paper_lay;
            $res[$k]['finfo'] = array();
            $res[$k]['sinfo'] = array();
            if($quote['cat_id']==69){   //case สมุด
                $res[$k]['frame'] = array($comp['comp_print_id']=>1);
                $res[$k]['sheet'] = $amount*$page/2/$paper_lay+$allo;
                $div = (is_int($paper_lay/8)&&$paper_lay/8>=2?$paper_lay/8:1);
                $res[$k]['cut'] = $div/2*$res[$k]['sheet'];
                $res[$k]['set'] = ($amount*ceil($page/2/$paper_lay)+$allo)*$div;
                $name = "1,กลับใน,$color/$color,".$res[$k]['sheet'];
                $res[$k]['round'] = array(array($name,$res[$k]['sheet']*2));
                $res[$k]['color'] = $color."/$color";
                array_push($res[$k]['finfo'],array(
                    "frameid" => $op_print_toplate[$comp['comp_print_id']],
                    "frame" => 1,
                    "round" => $res[$k]['sheet']*2
                ));
                array_push($res[$k]['sinfo'],array(
                    "foldid" => $set_id[$paper_lay],
                    "set" => $res[$k]['sheet']
                ));
            } else {                    //case หนังสือทั่วไป
                $res[$k]['frame'] = array($comp['comp_print_id']=>ceil($frame));
                $set = plate_div($frame);
                $i = 1;
                $sheet = 0;
                $cut = 0;
                $yok = 0;
                $res[$k]['color'] = $color."/$color";
                $res[$k]['round'] = array();
                foreach($set as $key=>$s){
                    if($s>0){
                        if($key==0){
                            $sheet += $temp = ($amount+$allo)*$s/2;
                            $round = $tsheet = $amount+$allo;
                            $name = "$s,กลับนอก,$color/$color,".$tsheet;
                            array_push($res[$k]['round'],array($name,$s*$round));
                            $div = (is_int($paper_lay/8)&&$paper_lay/8>=2?$paper_lay/8:1);
                            $cut += ($div>1?$div/2*$temp:0);
                            
                            //check folding
                            if(isset($set_id[$paper_lay*2])){
                                $sid = $set_id[$paper_lay*2];
                                $set = $tsheet;
                            } else if(isset($set_id[$paper_lay])){
                                $sid = $set_id[$paper_lay];
                                $set = $tsheet*2;
                            } else if(isset($set_id[$paper_lay/2])){
                                $sid = $set_id[$paper_lay/2];
                                $set = $tsheet*4;
                            }
                            array_push($res[$k]['finfo'],array(
                                "frameid" => $op_print_toplate[$comp['comp_print_id']],
                                "frame" => $s,
                                "round" => $round
                            ));
                            array_push($res[$k]['sinfo'],array(
                                "foldid" => $sid,
                                "set" => $set*$s/2
                            ));
                            $yok += $set*$s/2;
                        } else {
                            $ss = ($s>0.5?1:$s);
                            $sheet += $tsheet = $amount*$ss/2+$allo;
                            $round = $tsheet*2;
                            $name = "$s,กลับใน,$color/$color,".$tsheet;
                            array_push($res[$k]['round'],array($name,$round));
                            $div = (is_int($paper_lay*$s/2/8)&&$paper_lay*$s/2/8>=2?$paper_lay*$s/2/8:1);
                            $cut += $div*($amount+ceil($allo*$s*$paper_lay));
                            $i++;
                            array_push($res[$k]['finfo'],array(
                                "frameid" => $op_print_toplate[$comp['comp_print_id']],
                                "frame" => 1,
                                "round" => $round
                            ));
                            //check folding
                            $st = $s*$paper_lay; //remainding page
                            if(isset($set_id[$st])){
                                $yok += $amount+$allo;
                                array_push($res[$k]['sinfo'],array(
                                    "foldid" => $set_id[$st],
                                    "set" => $amount+$allo
                                ));
                            } else {
                                $setcomp = array();
                                while($st>0){
                                    foreach($set_id as $fpage=>$sid){
                                        while($st/$fpage>=1){
                                            $fid = $set_id[$fpage];
                                            if(!isset($setcomp[$fid])){
                                                $setcomp[$fid] = $amount+$allo;
                                            } else {
                                                $setcomp[$fid] += $amount+$allo;
                                            }
                                            $st -= $fpage;
                                        }
                                    }
                                }
                                foreach($setcomp as $fid=>$set){
                                    $yok += $set;
                                    array_push($res[$k]['sinfo'],array(
                                        "foldid" => $fid,
                                        "set" => $set
                                    ));
                                }
                            }
                        }
                    }
                }
                $res[$k]['sheet'] = $sheet;
                $res[$k]['cut'] = $cut;
                $res[$k]['set'] = $yok;
            }
        } else {            //อื่นๆ ปก ใบพาด แจ็คเก็ด
            $piece = $page;
            $sheet = $res[$k]["sheet"] = $amount*$piece/$paper_lay+$allo;
            $res[$k]['cut'] = $sheet*$paper_lay;
            $res[$k]['set'] = $sheet*$paper_lay;
            $frame = $page*($comp['comp_print2']>0?2:1)/$paper_lay;
            $res[$k]['frame'] = array($comp['comp_print_id']=>ceil($frame));
            $res[$k]['finfo'] = array();
            if($comp['comp_print2']==0){            //พิมพ์ด้านเดียว
                $name = "$frame,หน้าเดียว,$color/0,".$res[$k]['sheet'];
                $res[$k]['round'] = array(array($name,$res[$k]['sheet']));
                $res[$k]['color'] = $color."/0";
                array_push($res[$k]['finfo'],array(
                    "frameid" => $op_print_toplate[$comp['comp_print_id']],
                    "frame" => ceil($frame),
                    "round" => $sheet
                ));
            } else if($comp['comp_print2']==$comp['comp_print_id']){     //พิมพ์สองด้านสีเดียวกัน
                $name = "$frame,กลับใน,$color/$color,".$res[$k]['sheet'];
                $res[$k]['round'] = array(array($name,$res[$k]['sheet']*2));
                $res[$k]['color'] = $color."/".$color;
                array_push($res[$k]['finfo'],array(
                    "frameid" => $op_print_toplate[$comp['comp_print_id']],
                    "frame" => ceil($frame),
                    "round" => $sheet*2
                ));
            } else {                                //case พิมพ์ 2 ด้าน สี่ไม่เหมือน เช่น 4/1
                $res[$k]['frame'][$comp['comp_print2']] = 1;
                $color2 = $op_print_color[$comp['comp_print2']];
                $name = "2,กลับนอก,$color/$color2,".$res[$k]['sheet'];
                $res[$k]['round'] = array(array($name,$res[$k]['sheet']));
                $res[$k]['color'] = $color."/".$color2;
                array_push($res[$k]['finfo'],array(
                    "frameid" => $op_print_toplate[$comp['comp_print_id']],
                    "frame" => ceil($page/$paper_lay),
                    "round" => $sheet
                ));
                array_push($res[$k]['finfo'],array(
                    "frameid" => $op_print_toplate[$comp['comp_print2']],
                    "frame" => ceil($page/$paper_lay),
                    "round" => $sheet
                ));
            }
        }
        $res[$k]['ff'] = $frame;
        //คำนวณพื้นที่เคลือบ
        if($type==2||$type==6){
            $cpage = (is_array($coatpage)?$coatpage[$c]:0);
            $csheet = ceil($amount*$cpage/$paper_lay);
            $res[$k]['in2'] = $csheet*$sinfo['width']*$sinfo['length'];
        } else {
            $res[$k]['in2'] = $res[$k]['sheet']*$sinfo['width']*$sinfo['length'];
        }
        $c++;
    }
    //collect total unit info
    $tinfo = array(
        "type" => 9,
        "allpage" => $quote['page_cover']*2+$quote['page_inside'],
        "page" => $quote['page_inside'],
        "km" => $quote['distance'],
        "location" => (isset($quote['location'])?$quote['location']:0),
        "piece" => $quote['amount'],
        "set" => 0,
        "frame" => 0,
        "kong" => 0,
        "minallo" => INF
    );
    //yok//ถ้าไสกาว binding_id=1 ยกไม่นับปก
    for($i=0;$i<count($res);$i++){
        $unit = $res[$i];
        //frame
        $tinfo['frame'] += ceil($unit['ff']);
        //kong
        if($unit['ff']<=1){
            $tinfo['kong'] += 1;
        } else {
            $div = plate_div($unit['ff']);
            $tinfo['kong'] += $div[0]/2 + $div[1] + ceil($div[2]);
        }
        //min allo
        $tinfo['minallo'] = min($tinfo['minallo'],$unit['allo']);
        //พับ
        if(isset($unit['sinfo'])){
            foreach($unit['sinfo'] as $val){
                if($val['foldid']>0){
                    if(isset($tinfo['sinfo'][$val['foldid']])){
                        $tinfo['sinfo'][$val['foldid']] += $val['set'];
                    } else {
                        $tinfo['sinfo'][$val['foldid']] = $val['set'];
                    }
                }
            }
        }
    }
    $tinfo['set'] = $tinfo['kong']*($amount+$tinfo['minallo'])-($quote['binding_id']==1?($amount+$tinfo['minallo']):0);
    array_push($res,$tinfo);
    return $res;
}
