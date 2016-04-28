<?php
class mytable {
    public function show_table($arrheader,$arrdata,$id="",$ex_script="") {
        $html = "<div id='$id' class='ez-table'>"
                . "<table>"
                . "<tr class='tb-head'>";
        foreach($arrheader as $value){
            $html .= "<th>$value</th>";
        }
        $html .= "</tr>";
        if(isset($arrdata)&&sizeof($arrdata,0)>0){
            foreach($arrdata as $key=>$val){
                if(!is_array($val)){
                    break;
                } else {
                    $html .= "<tr class='tb-data'>";
                    foreach($val as $k => $v){
                        
                        $html .= "<td>".$v."</td>";
                    }
                    $html .= "</tr>";
                }
            }
        } else {
            $len = sizeof($arrheader,0);
            $html .= "<tr><td colspan='$len' class='tb-noinfo'>No information.</td></tr>";
        }
        $html .= "</table>"
                . "<script>$ex_script</script>"
                . "</div><!-- #$id -->\n";
        return $html;
    }
    public function show_vtable($akeyval,$id=""){
        $html = "<div id='$id' class='ez-vtable'>"
                . "<table>";
        foreach($akeyval as $k=>$v){
            $html .= "<tr><th>$k</th><td>$v</td></tr>";
        }
        $html .= "</table>"
                . "</div><!-- #$id -->\n";
        return $html;
    }
    public function show_table_keygroup($arrheader,$arrdata,$id="",$delimited=",") {
        $html = "<div id='$id' class='ez-table'>"
                . "<table>"
                . "<tr class='tb-head'>";
        foreach($arrheader as $value){
            $html .= "<th>$value</th>";
        }
        $html .= "</tr>";
        if(isset($arrdata)&&sizeof($arrdata,0)>0){
            foreach($arrdata as $key=>$val){
                if(!is_array($val)){
                    break;
                } else {
                    $html .= "<tr class='tb-kgroup-st tb-data'>";
                    //group
                    $g = explode($delimited,$key);
                    $row = count($val);
                    foreach($g AS $gg){
                        $html .= "<td class='tb-kgroup' rowspan='$row'>$gg</td>";
                    }
                    $st = 0;
                    foreach($val as $kk=>$vv){
                        $html .= ($st>0?"<tr class='tb-data'>":"");
                        foreach($vv as $vvv){
                            $html .= "<td>".$vvv."</td>";
                        }
                        $html .= "</tr>";
                        $st++;
                    }
                }
            }
        } else {
            $len = sizeof($arrheader,0);
            $html .= "<tr><td colspan='$len' class='tb-noinfo'>No information.</td></tr>";
        }
        $html .= "</table>"
                . "</div><!-- #$id -->\n";
        return $html;
    }
    public function show_email_table($arrheader,$arrdata,$id="",$ex_script="") {
        $html = "<div align='center'>"
                . "<table width='600' style='border-collapse:collapse;border:1px #bbb solid;font-size:15px;'>"
                . "<tr style='background-color:#fbec88;border-bottom:1px solid #bbb;font-size:16px;'>";
        $i=0;
        foreach($arrheader as $value){
            $wid = ($i==0?"width='100'":"");
            $html .= "<th $wid>$value</th>";
            $i++;
        }
        $html .= "</tr>";
        if(isset($arrdata)&&sizeof($arrdata,0)>0){
            foreach($arrdata as $key=>$val){
                if(!is_array($val)){
                    break;
                } else {
                    $html .= "<tr style='border-bottom:1px solid #bbb;'>";
                    foreach($val as $k => $v){
                        $html .= "<td style='padding-top:5px;padding-bottom:3px;'>".$v."</td>";
                    }
                    $html .= "</tr>";
                }
            }
        } else {
            $len = sizeof($arrheader,0);
            $html .= "<tr><td colspan='$len' class='tb-noinfo'>No information.</td></tr>";
        }
        $html .= "</table>"
                . "<script>$ex_script</script>"
                . "</div><!-- #$id -->\n";
        return $html;
    }
    public function show_tb_tt($arrheader,$arrdata,$id="",$numcol,$sumcol,$margin=0){
        $sum = 0;
        $len = sizeof($arrheader,0);
        $html = "<div id='$id' class='ez-table'>"
                . "<table>"
                . "<tr class='tb-head tb-row'>";
        foreach($arrheader as $value){
            $html .= "<th>$value</th>";
        }
        $html .= "</tr>";
        if(isset($arrdata)&&sizeof($arrdata,0)>0){
            foreach($arrdata as $key=>$val){
                if(!is_array($val)){
                    break;
                } else {
                    $html .= "<tr class='tb-data tb-row'>";
                    foreach($val as $k => $v){
                        $act_v = (in_array($k,$numcol)?number_format($v,2):$v);
                        $sum += ($k==$sumcol?$v:0);
                        $html .= "<td>".$act_v."</td>";
                    }
                    $html .= "</tr>";
                }
            }
            $span = $len-1;
            $show_tt = ($margin>0?(1+($margin/100))*$sum:$sum);
            $html .= "<tr class='tb-total'><td colspan='$span'>TOTAL</td><td>".number_format($show_tt,2)."</td></tr>";
        } else {
            
            $html .= "<tr><td colspan='$len' class='td-span5 tb-noinfo'>No information.</td></tr>";
        }
        $html .= "</table>"
                . "</div><!-- #$id -->\n";
        return $html;
    }
    public function show_tb_tax($arrheader,$arrdata,$id="",$numcol,$sumcol,$discount,$tax=0.07){
        $sum = 0;
        $len = sizeof($arrheader,0);
        $html = "<div id='$id' class='ez-table'>"
                . "<table>"
                . "<tr class='tb-head tb-row'>";
        foreach($arrheader as $value){
            $html .= "<th>$value</th>";
        }
        $html .= "</tr>";
        if(isset($arrdata)&&sizeof($arrdata,0)>0){
            foreach($arrdata as $key=>$val){
                if(!is_array($val)){
                    break;
                } else {
                    $html .= "<tr class='tb-data tb-row'>";
                    foreach($val as $k => $v){
                        $act_v = (in_array($k,$numcol)?number_format($v,2):$v);
                        $sum += ($k==$sumcol?$v:0);
                        $html .= "<td>".$act_v."</td>";
                    }
                    $html .= "</tr>";
                }
            }
            $span = $len-1;
            $sum -= $discount;
            $html .= "<tr class='tb-total'><td colspan='$span'>ส่วนลด<br/>Discount</td><td>".number_format($discount,2)."</td></tr>"
                . "<tr class='tb-total'><td colspan='$span'>รวมเงิน<br/>Sub Total</td><td>".number_format($sum,2)."</td></tr>"
                . "<tr class='tb-total'><td colspan='$span'>ภาษีมูลค่าเพิ่ม<br/>Tax</td><td>".number_format($sum*$tax,2)."</td></tr>"
                . "<tr class='tb-gtotal'><td colspan='$span'>รวมทั้งสิ้น<br/>Grand Total</td><td>".number_format($sum*1.07,2)."</td></tr>";
        } else {
            
            $html .= "<tr><td colspan='$len' class='td-span5 tb-noinfo'>No information.</td></tr>";
        }
        $html .= "</table>"
                . "</div><!-- #$id -->\n";
        return $html;
    }
    public function show_tb_wtax($arrheader,$arrdata,$id="",$tax,$discount,$extra=""){
        __autoloada("thai");
        function sub_format($v){
            if((float)$v==0){
                return "-";
            } else {
                return number_format($v,2);
            }
        }
        $sum = 0;
        $len = sizeof($arrheader,0);
        $html = "<div id='$id' class='ez-table'>"
                . "<table>"
                . "<tr class='tb-head tb-row'>";
        foreach($arrheader as $value){
            $html .= "<th>$value</th>";
        }
        $html .= "</tr>";
        if(isset($arrdata)&&sizeof($arrdata,0)>0){
            foreach($arrdata as $key=>$val){
                if(!is_array($val)){
                    break;
                } else {
                    $html .= "<tr class='tb-data tb-row'>";
                    foreach($val as $k => $v){
                        $act_v = (in_array($k,array(2,3,4))?sub_format($v):$v);
                        $sum += ($k==4?$v:0);
                        $html .= "<td>".$act_v."</td>";
                    }
                    $html .= "</tr>";
                }
            }
            $sum -= $discount;
            $thaitt = "(".ThaiBahtConversion(round($sum*(1+$tax),2)).")";
            $html .= "<tr class='tb-discount'><td colspan='2'></td><td colspan='2'>ส่วนลด / Discount</td><td>".number_format($discount,2)."</td></tr>"
                . "<tr class='tb-sum'><td colspan='2' rowspan='3'>$thaitt</td><th colspan='2'>รวมเงิน / Sub Total</td><td>".number_format($sum,2)."</td></tr>"
                . "<tr class='tb-sum'><th colspan='2'>ภาษีมูลค่าเพิ่ม / Tax</td><td>".number_format($sum*$tax,2)."</td></tr>"
                . "<tr class='tb-sum tb-gtt'><th colspan='2'>รวมทั้งสิ้น / Grand Total</td><td>".number_format($sum*(1+$tax),2)."</td></tr>";
        } else {
            
            $html .= "<tr><td colspan='$len' class='td-span5 tb-noinfo'>No information.</td></tr>";
        }
        $html .= "</table>"
                . "</div><!-- #$id -->\n";
        return $html;
    }
    public function show_tb_bill($arrheader,$arrdata,$id=""){
        __autoloada("thai");
        $sum = 0;
        $n = count($arrdata);
        $len = sizeof($arrheader,0);
        $html = "<div id='$id' class='ez-table'>"
                . "<table>"
                . "<tr class='tb-head tb-row'>";
        foreach($arrheader as $value){
            $html .= "<th>$value</th>";
        }
        $html .= "</tr>";
        if(isset($arrdata)&&sizeof($arrdata,0)>0){
            foreach($arrdata as $key=>$val){
                if(!is_array($val)){
                    break;
                } else {
                    $html .= "<tr class='tb-data tb-row'>";
                    foreach($val as $k => $v){
                        $act_v = (in_array($k,array(4))?number_format($v,2):$v);
                        $sum += ($k==4?$v:0);
                        $html .= "<td>".$act_v."</td>";
                    }
                    $html .= "</tr>";
                }
            }
            $total = number_format($sum,2);
            $thaitt = "(".ThaiBahtConversion(round($sum,2)).")";
            $html .= "<tr class='tb-sum'><td colspan='".($len-3)."'>จำนวน $n รายการ <br/>$thaitt</td><th colspan='2'>รวมทั้งสิ้น<br/>Grand Total</td><td class='tb-gtt'>$total</td></tr>";
        } else {
            
            $html .= "<tr><td colspan='$len' class='td-span5 tb-noinfo'>No information.</td></tr>";
        }
        $html .= "</table>"
                . "</div><!-- #$id -->\n";
        return $html;
    }
    public function prep_get_url($url,$name){
        if(is_integer(strpos($url,"?"))){
            $t = explode("?",$url);
            $base = $t[0];
            $get = explode("&",$t[1]);
            foreach($get as $k=>$v){
                if(strlen($v)<1){
                    unset($get[$k]);
                } else if(is_integer(strpos($v,$name."="))){
                    unset($get[$k]);
                }
            }
            if(count($get)>0){
                $res = $base."?".implode("&",$get)."&$name=";
            } else {
                $res = $base."?$name=";
            }
            return $res;
        } else {
            return $url."?$name=";
        }
    }
    public function show_search($url,$id,$name,$ph=null,$current=""){
        __autoloada("form");
        $place = (isset($ph)?$ph:"ค้นหา 3 ตัวอักษรขึ้นไป");
        $base = $this->prep_get_url($url, $name);
        $form = new myform();
        $html = "<div class='tb-search'>"
                . $form->show_text_wbutton($id, $id, $current, $place , "", "", "text-but", null, "",$form->show_button("$id-but", "Search","but-100"))
                . "</div><!-- .tb-search -->"
                . "<script>"
                . "tb_search('$id','$base')"
                . "</script>";
        return $html;
    }
    public function show_filter($url,$name,$arr,$current,$show){
        __autoloada("form");
        $base = $this->prep_get_url($url, $name);
        $form = new myform();
        $list = (isset($arr["0"])?array("none"=>"$show")+$arr:array("0"=>"$show")+$arr);
        $html = "<div class='tb-filter'>"
                . $form->show_select($name,$list,"label-inline",null,$current)
                . "</div><!-- .tb-filter -->"
                . "<script>"
                . "tb_filter('$name','$base')"
                . "</script>";
        return $html;
    }
    public function show_pagenav($url,$page,$max){
        if($max<=1){
            return "";
        }
        $base = $this->prep_get_url($url, "page");
        $first = $base."1";
        $last = $base.$max;
        switch($page){
            case 1 :
                $pclass = "nav-inactive";
                $nclass = "";
                $prev = $base."1";
                $next = $base.($page+1);
                break;
            case $max:
                $pclass = "";
                $nclass = "nav-inactive";
                $prev = $base.($page-1);
                $next = $base.$max;
                break;
            default:
                $pclass = "";
                $nclass = "";
                $prev = $base.($page-1);
                $next = $base.($page+1);
        }
        $nav = "<div class='page-nav'>"
                . "<a href='$first' title='Go to the first page' class='pnav-icon icon-jump-left $pclass'></a>"
                . "<a href='$prev' title='Go to the previous page' class='pnav-icon icon-triangle-left $pclass'></a>";
        $nav .= "<select class='page-sel'>";
        for($i=1;$i<=$max;$i++){
            $sel = ($page==$i?" selected='selected'":"");
            $nav.= "<option value='$i'$sel>$i</option>";
        }
        $nav .= "</select>"
                . "<span class='tt-page'> / $max</span>";
        $nav .= "<a href='$next' title='Go to the next page' class='pnav-icon icon-triangle-right $nclass'></a>"
                . "<a href='$last' title='Go to the last page' class='pnav-icon icon-jump-right $nclass'></a>";
        $nav .= "</div><!-- .page-nav -->"
                . "<script>page_change('$base','page-sel');</script>";
        
        return $nav;
    }
    public function show_seo_pagenav($url,$now,$max){
        $base = $this->prep_get_url($url, "page");
        $html = "<div class='all-pages'>";
        if($max<12){
            for($i=1;$i<=$max;$i++){
                $html .= ($i==$now?"<span class='c-page'>$now</span>":"<a href='".$base.$i."'>$i</a>");
            }
        } else {
            if($now<7){
                for($i=1;$i<=11;$i++){
                    switch($i){
                        case $now :
                            $html .= "<span>$i</span>";
                            break;
                        case 10:
                            $html .= "<span>...</span>";
                            break;
                        case 11:
                            $html .= "<a href='".$base.$max."'>$max</a>";
                            break;
                        default:
                            $html .= "<a href='".$base.$i."'>$i</a>";
                    }
                }
            } else if($now>=7 && $now<($max-5)) {
                for($i=1;$i<=11;$i++){
                    switch($i){
                        case 1 :
                            $html .= "<a href='".$base.$i."'>$i</a>";
                            break;
                        case 2 :
                            $html .= "<span>...</span>";
                            break;
                        case 10:
                            $html .= "<span>...</span>";
                            break;
                        case 11:
                            $html .= "<a href='".$base.$max."'>$max</a>";
                            break;
                        case 6:
                            $html .= "<span>$now</span>";
                            break;
                        default:
                            if($i<6){
                                $num = $now-(6-$i);
                                $html .= "<a href='".$base.$num."'>$num</a>";
                            } else {
                                $num = $now+($i-6);
                                $html .= "<a href='".$base.$num."'>$num</a>";
                            }
                    }
                }
            } else {
                for($i=1;$i<=11;$i++){
                    switch($i){
                        case 1:
                            $html .= "<a href='".$base.$i."'>$i</a>";
                            break;
                        case 2:
                            $html .= "<span>...</span>";
                            break;
                        default:
                            $num = $max-(11-$i);
                            if($num==$now){
                                $html .= "<span>$num</span>";
                            } else {
                                $html .= "<a href='".$base.$num."'>$num</a>";
                            }
                    }
                }
            }
        }
        $html .= "</div><!-- .all-pages -->";
        return $html;
    }
    public function show_option($rowkey,$rowval,$colkey,$colval){
        __autoload("form");
        $form = new myform();
        $html = "<div id='op-wrap'>"
                . "<div class='op-tb cheight'>"
                . $form->show_num("row_val",$rowval,1,"1-9999","Rows/page","","label-4050 cheight")
                . $form->show_checkbox("op-tb-check","col_val",$colval,"op-tb-col cheight")
                . $form->show_button("op-tb-apply","Apply","sm-but-right")
                . $form->show_hidden("row_key","row_key",$rowkey)
                . $form->show_hidden("col_key","col_key",$colkey);
        $arrn = json_encode($form->array_name);
        $colv = json_encode($colval);
        $html .= "</div><!-- .op-tb -->"
                . "</div>"
                . "<div class='op-but icon-widget'></div>"
                . $form->submitscript("opt_apply($arrn,$colv);")
                . "<script>"
                . "opt_show();"
                . "</script>";
        return $html;
    }
    public function show_quote_tb($head,$rec,$id=null,$numcol,$margin,$adj){
        $sum = 0;
        $len = sizeof($head,0);
        $html = "<div id='$id' class='ez-table'>"
                . "<table>"
                . "<tr class='tb-head tb-row'>";
        foreach($head as $value){
            $html .= "<th>$value</th>";
        }
        $html .= "</tr>";
        if(isset($rec)&&sizeof($rec,0)>0){
            $tt = 0;
            $tt_mg = 0;
            $j = 0;
            foreach($rec as $k=>$v){
                $rows = count($v);
                $ttd = ($rows==0?"<td colspan='6'></td>":"");
                $html .= "<tr class='tb-first-gp tb-data'><th rowspan='$rows'>$k</th>".$ttd;
                $i = 0;
                foreach($v as $kk=>$vv){
                    $tt += $vv[3];
                    $html .= ($i==0?"":"<tr class='tb-data'>");
                    foreach($vv as $kkk=>$vvv){
                        $av = (in_array($kkk,$numcol)?number_format($vvv,0):$vvv);
                        $cls = ($kkk==3?"class='tb-stcost'":"");
                        $html .= "<td $cls>$av</td>";
                    }
                    if(isset($adj[$j])&&$adj[$j]!=""){
                        $mg_v = $adj[$j];
                        $mg = $vv[3]*(1+$adj[$j]/100);
                    } else {
                        $mg_v = $margin;
                        $mg = $vv[3]*(1+$margin/100);
                    }
                    $tt_mg += $mg;
                    $html .= "<td>"
                            . "<input type='number' min='-100' id='mg_$i' value='$mg_v' step='0.01' class='tb-mg-input' name='adj_margin[]'/>"
                            . "</td>"
                            . "<td class='tb-pr'>".number_format($mg,0)."</td>";
                    $html .= "</tr>";
                    $i++;
                    $j++;
                }
            }
            $span = $len-3;
            if(isset($adj[$j])&&$adj[$j]!=""){
                $mg_v = $adj[$j];
            } else {
                $mg_v = $margin;
            }
            $html .= "<tr class='tb-total'><td colspan='$span'>TOTAL</td><td class='tb-stcost'>".number_format($tt,0)."</td>"
                    . "<td>"
                    . "<input type='number' min='-100' id='mg_$i' value='$mg_v' step='0.01' class='tb-mg-input-tt' name='adj_margin[]'/>"
                    . "</td>"
                    . "<td class='tb-total-quote'>".number_format($tt_mg,0)."</td>"
                    . "</tr>";
        } else {
            
            $html .= "<tr><td colspan='$len' class='td-span5 tb-noinfo'>No information.</td></tr>";
        }
        $html .= "</table>"
                . "</div><!-- #$id -->"
                . "<script>quote_adj('$id')</script>";
        return $html;
    }
}
