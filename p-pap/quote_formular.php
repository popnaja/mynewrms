<?php
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
function cal_quote($info,$comps){
    global $op_comp_type;
    global $op_print_toplate;
    __autoload("pappdo");
    $db = new PAPdb(DB_PAP);
    
    $amount = $info['amount'];
    $processes = $db->get_keypair("pap_process", "process_id", "process_name");
    //calculate

    $units = unit_cal($info, $comps);
    //var_dump($units);
    $res['ออกแบบ'] = array();
    $res['ทำเพลต'] = array();
    $res['กระดาษ'] = array();
    $res['พิมพ์'] = array();
    $res['หลังพิมพ์'] = array();
    $res['แพ็ค'] = array();
    $res['ขนส่ง'] = array();
  
    $ex = explode(",",$info['exclude']);
    $num = count($units);
    foreach($op_comp_type AS $k=>$v){
        $run[$k] = 0;
    }
    for($i=0;$i<$num;$i++){
        $unit = $units[$i];
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
            foreach($pack as $pid){
                if($pid>0){
                    $pcost = new_pcost($pid, $unit);
                    array_push($res['แพ็ค'],array_merge(array($processes[$pid]),$pcost));
                }
            }
            //shipping
            $ship = explode(",",$info['shipping']);
            foreach($ship as $pid){
                if($pid>0){
                    $pcost = new_pcost($pid, $unit);
                    array_push($res['ขนส่ง'],array_merge(array($processes[$pid]),$pcost));
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
            foreach($unit['frame'] as $pid=>$vol){
                $pcost = new_pcost($op_print_toplate[$pid],array("frame"=>$vol));
                array_push($res['ทำเพลต'],array_merge(array("Plate ".$cname),$pcost));
            }
        }
        //paper
        $pinfo = $db->get_info("pap_mat","mat_id",$unit['paper_id']);
        $lot = $pinfo['mat_order_lot_size'];
        $lots = ceil($unit['sheet']/$unit['paper_cut']/$lot);
        $rims = $lots*$lot/500;
        if(!in_array("2",$ex)){
            $c_per_rim = round(500*$pinfo['mat_std_cost'],2);
        } else {
            $c_per_rim = 0;
        }
        array_push($res['กระดาษ'],array("กระดาษ $cname",$rims,$c_per_rim,$rims*$c_per_rim));
        
        //printing
        //var_dump($unit);
        if(count($unit['frame'])>1){
            $color = explode("/",$unit['color']);
            foreach($unit['round'] as $k=>$v){
                $kinfo = explode(",",$v[0]);
                $round = $v[1]/ceil($kinfo[0]);
            }
            $pcost = print_cost($unit['print_id'], $round);
            array_push($res['พิมพ์'],array("พิมพ์ $cname นอก ($color[0] สี)",$round,$pcost[0],$pcost[0]));
            $pcost = print_cost($unit['print_id2'], $round);
            array_push($res['พิมพ์'],array("พิมพ์ $cname ใน ($color[1] สี)",$round,$pcost[0],$pcost[0]));
        } else {
            foreach($unit['round'] as $k=>$v){
                $kinfo = explode(",",$v[0]);
                $round = $v[1]/ceil($kinfo[0]);
                $pcost = print_cost($unit['print_id'], $round);
                array_push($res['พิมพ์'],array("พิมพ์ $cname $kinfo[1]","$kinfo[0] กรอบ ($round รอบ/กรอบ)",$pcost[0],$pcost[0]*ceil($kinfo[0])));
            }
            
        }
        
        //update unit['sheet'] for coating and post press
        $unit['sheet'] = $lots*$lot;
        //coating เคลือบคิดเต็มริม
        if($com['comp_coating']>0){
            $cost = new_pcost($com['comp_coating'],$unit);
            array_push($res['หลังพิมพ์'],array_merge(array("เคลือบ $cname"),$cost));
        }
        //post process หลังงานพิมพ์คิดเต็มริม
        $post = explode(",",$com['comp_postpress']);
        foreach($post as $pid){
            if($pid>0){
                $pcost = new_pcost($pid, $unit);
                array_push($res['หลังพิมพ์'],array_merge(array($processes[$pid]." ".$cname),$pcost));
            }
        }
    }
    //folding
    if($info['cat_id']==11){
        $folding_id = $info['folding'];
    } else {
        $folding_id = 8;
    }
    //ถ้า เป็นหนังสือ cat_id=10 แผ่นพับ cat_id=11 มีการพับ
    if(in_array($info['cat_id'],array(10,11,69))){
        $pcost = new_pcost($folding_id, $overall);
        array_push($res['หลังพิมพ์'],array_merge(array($processes[$folding_id]),$pcost));
    }

    //collecting
    //ถ้า เป็นหนังสือ มีการเก็บ + เข้าเล่ม
    $collect_id = 9;
    if(in_array($info['cat_id'],array(10,69))){
        $pcost = new_pcost($collect_id, $overall);
        array_push($res['หลังพิมพ์'],array_merge(array($processes[$collect_id]),$pcost));

        //binding
        $pcost = new_pcost($info['binding_id'], $overall);
        array_push($res['หลังพิมพ์'],array_merge(array($processes[$info['binding_id']]),$pcost));
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
    $info = $db->get_info("pap_process","process_id",$pid);
    $cost = json_decode($meta['cost'],true);
    $amount = $arrinfo[$info['process_unit']];
    foreach($cost AS $k=>$value){
        $fcost = (isset($value['fcost'])?$value['fcost']:0);
        if(count($cost)>1){
            $check = $arrinfo[$value['cond']];
            if($check>=$value['btw']&&$check<=($value['to']>0?$value['to']:INF)){
                $cinfo = max($fcost+$value['cost']*$amount,$value['min']);
                $cost_per_u = $value['cost'];
                break;
            }
        } else {
            $cinfo = max($fcost+$value['cost']*$amount,$value['min']);
            $cost_per_u = $value['cost'];
        }
    }
    return array($amount,$cost_per_u,$cinfo);
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
    global $op_print_color;
    
    $n = count($comps);
    $amount = $quote['amount'];
    $res = array();
    foreach($comps as $k=>$comp){
        $color = $op_print_color[$comp['comp_print_id']];
        $allo = $res[$k]['allo'] = $comp['comp_paper_allowance'];
        $paper_lay = $comp['comp_paper_lay'];
        $paper_cut = $comp['comp_paper_cut'];
        $res[$k]['type'] = $comp['comp_type'];
        $page = $res[$k]['page'] = $comp['comp_page'];
        
        
        
        $res[$k]['paper_id'] = $comp['comp_paper_id'];
        $res[$k]['paper_lay'] = $paper_lay;
        $res[$k]['paper_cut'] = $paper_cut;
        $res[$k]['print_id'] = $comp['comp_print_id'];
        $res[$k]['print_id2'] = $comp['comp_print2'];
        
        
        $type = $comp['comp_type'];
        if($type==2){                   //เนื้อใน
            $frame = $page/$paper_lay;
            if($quote['cat_id']==69){   //case สมุด
                $res[$k]['frame'] = array($comp['comp_print_id']=>1);
                $res[$k]['sheet'] = $amount*$page/2/$paper_lay+$allo;
                $div = (is_int($paper_lay/8)&&$paper_lay/8>=2?$paper_lay/8:1);
                $res[$k]['cut'] = $div/2*$res[$k]['sheet'];
                $res[$k]['set'] = ($amount*ceil($page/2/$paper_lay)+$allo)*$div;
                $name = "1,กลับใน,$color/$color,".$res[$k]['sheet'];
                $res[$k]['round'] = array(array($name,$res[$k]['sheet']*2));
                $res[$k]['color'] = $color."/$color";
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
                            $yok += $div*$temp;
                        } else {
                            $ss = ($s>0.5?1:$s);
                            $sheet += $tsheet = $amount*$ss/2+$allo;
                            $round = $tsheet*2;
                            $name = "$s,กลับใน,$color/$color,".$tsheet;
                            array_push($res[$k]['round'],array($name,$round));
                            $div = (is_int($paper_lay*$s/2/8)&&$paper_lay*$s/2/8>=2?$paper_lay*$s/2/8:1);
                            $cut += $div*($amount+$allo);
                            $yok += $div*($amount+$allo);
                            $i++;
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
            if($comp['comp_print2']==0){            //พิมพ์ด้านเดียว
                $name = "$frame,หน้าเดียว,$color/0,".$res[$k]['sheet'];
                $res[$k]['round'] = array(array($name,$res[$k]['sheet']));
                $res[$k]['color'] = $color."/0";    //พิมพ์สองด้านสีเดียวกัน
            } else if($comp['comp_print2']==$comp['comp_print_id']){
                $name = "$frame,กลับใน,$color/$color,".$res[$k]['sheet'];
                $res[$k]['round'] = array(array($name,$res[$k]['sheet']*2));
                $res[$k]['color'] = $color."/".$color;
            } else {                                //case พิมพ์ 2 ด้าน สี่ไม่เหมือน เช่น 4/1
                $res[$k]['frame'][$comp['comp_print2']] = 1;
                $color2 = $op_print_color[$comp['comp_print2']];
                $name = "2,กลับนอก,$color/$color2,".$res[$k]['sheet'];
                $res[$k]['round'] = array(array($name,$res[$k]['sheet']));
                $res[$k]['color'] = $color."/".$color2;
            }
        }
        $res[$k]['ff'] = $frame;
    }
    //collect total unit info
    $tinfo = array(
        "type" => 9,
        "allpage" => $quote['page_cover']*2+$quote['page_inside'],
        "page" => $quote['page_inside'],
        "km" => $quote['distance'],
        "piece" => $quote['amount'],
        "set" => 0,
        "frame" => 0
    );
    //yok//ถ้าไสกาว binding_id=1 ยกไม่นับปก
    for($i=0;$i<count($res);$i++){
        $unit = $res[$i];
        $set = (in_array($unit['type'],array(1,2,3))?$unit['set']:0);
        if($quote['binding_id']==1){
            $tinfo['set'] += ($unit['type']==1?0:$set);
        } else {
            $tinfo['set'] += $set;
        }
        $tinfo['frame'] += ceil($unit['ff']);
    }
    array_push($res,$tinfo);
    return $res;
}
