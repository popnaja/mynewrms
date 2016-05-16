<?php
class prodPlan{
    private $st_date;
    private $type;
    private $thm = array("ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
    public function __construct($date,$type) {
        $this->date = $date;
        $this->st_date = new DateTime($date,new DateTimeZone("Asia/Bangkok"));
        $this->type = $type;
    }
    public function show_plan($mach,$data){
        $html = "<div class='my-prod'>"
                . $this->plan_nav()
                . $this->plan_header()
                . $this->plan_schedule($mach,$data)
                . "</div><!-- .my-prod -->";
        return $html;
    }
    public function show_vplan($mach,$data){
        $html = "<div class='my-vprod'>"
                . $this->plan_nav()
                . $this->plan_vheader($mach)
                . $this->plan_vschedule($mach,$data)
                . "</div><!-- .my-vprod -->"
                . "<script>"
                . "lock_plan_head();"
                . "</script>";
        return $html;
    }
    private function plan_nav(){
        $type = $this->type;
        $today = date_create(null,timezone_open("Asia/Bangkok"));
        $todayd = date_format($today,"Y-m-d");
        $next = date_format(date_add(date_create($this->date,timezone_open("Asia/Bangkok")),date_interval_create_from_date_string("1 DAY")),"Y-m-d");
        $prev = date_format(date_sub(date_create($this->date,timezone_open("Asia/Bangkok")),date_interval_create_from_date_string("1 DAY")),"Y-m-d");
        if($this->st_date->format("Y-m-d")==$todayd){
            $tclass = "plan-but-disable";
        } else {
            $tclass = "";
        }
        if($type=="day"){
            $dclass = "plan-but-active";
            $hclass = "";
        } else {
            $dclass = "";
            $hclass = "plan-but-active";
        }
        $html = "<div class='plan-menu'>"
                . "<div class='plan-nav float-left'>"
                . "<span class='gray-but plan-c-month $tclass' date='$todayd' ptype='$type'>Today</span>"
                . "<span class='gray-but plan-c-month icon-chevron-left' date='$prev' ptype='$type'></span>"
                . "<span class='gray-but plan-c-month icon-chevron-right' date='$next' ptype='$type'></span>"
                . "</div><!-- . -->"
                . "<div class='plan-switch float-right'>"
                . "<span class='gray-but plan-switch-type $hclass' ptype='hour' date=''>HOUR</span>"
                . "<span class='gray-but plan-switch-type $dclass' ptype='day' date=''>DAY</span>"
                . "</div>"
                . "</div><!-- .plan-menu -->"
                . "<script>plan_change();</script>";
        return $html;
    }
    private function plan_header(){
        $wid = 60;
        $interval = 60;
        $col_perday = 1440/$interval;
        $dw = $wid*($col_perday);
        $html = "<div class='plan-header'>"
                . "<table cellpadding='0' cellspacing='0'>"
                . "<tr>"
                . "<td rowspan='2' width='120'>เครื่องจักร</td>";
        $time = "<tr>";
        for($j=0;$j<3;$j++){
            $day = $this->st_date->format("d")." ".$this->thm[$this->st_date->format("n")-1];
            $html .= "<th width='$dw' colspan='$col_perday' class='plan-header-date'>$day</th>";
            for($i=0;$i<$col_perday;$i++){
                $stime = $this->st_date->format("H:i");
                $time .= "<th width='$wid'>$stime</th>";
                $this->st_date->add(new DateInterval("PT".$interval."M"));
            }
        }
        $time .= "</tr>";
        $html .= "</tr>"
                . $time
                . "</table>"
                . "</div><!-- .plan-header -->";
        return $html;
    }
    private function plan_vheader($mach){
        $html = "<div class='plan-header'>"
                . "<table cellpadding='0' cellspacing='0'>"
                . "<tr>"
                . "<td colspan='2' width='100'>วันที่</td>";
        foreach($mach AS $k=>$v){
            $html .= "<td><p class='mach-vertical'>$v</p></td>";
        }
        $html .= "</tr>"
                . "</table>"
                . "</div><!-- .plan-header -->";
        return $html;
    }
    private function plan_vschedule($mach,$data){
        $height = 40;  //1 hour height
        $interval = 30; //minute
        $row_perday = 1440/$interval;
        $ratio = $height/60;
        $r_height = $interval*$ratio;
        $html = "<div class='plan-vschedule'>"
                . "<table cellpadding='0' cellspacing='0'>";
        $st = new DateTime($this->date,new DateTimeZone("Asia/Bangkok"));
        $now = new DateTime(pap_now(),new DateTimeZone("Asia/Bangkok"));
        $oidclass = array();
        $x=0;
        $o=0;
        for($d=0;$d<3;$d++){
            for($h=0;$h<$row_perday;$h++){
                $html .= "<tr>";
                $time = $st->format("H:i");
                $min = $st->format("i");
                if($time=="00:00"){
                    $day = $st->format("d")." ".$this->thm[$st->format("n")-1];
                    $html .= "<td rowspan='$row_perday' width='50' class='plan-date'>$day</td>";
                    $rclass = "plan-date";
                } else {
                    $rclass = ($min=="00"?"plan-full":"plan-half-hr");
                }
                $stime = ($min=="00"?$time:"&nbsp;");
                $to = new DateTime($st->format("Y-m-d H:i:s"),new DateTimeZone("Asia/Bangkok"));
                $to->add(new DateInterval("PT".$interval."M"));
                //show current
                $show_now = "";
                if($now>=$st&&$now<$to){
                    $nowdiff = date_diff($st,$now);
                    $nowtop = $this->get_interval_ttmin($nowdiff)*$ratio;
                    $show_now = "<span class='show-now' style='top:$top;'></span>";
                }
                $html .= "<td width='50' height='$r_height' class='$rclass'>$stime".$show_now."</td>";
                foreach($mach as $k=>$v){
                    $plan = "&nbsp;";
                    if(isset($data[$k])){
                        foreach($data[$k] as $info){
                            $ptime = new DateTime($info[0],new DateTimeZone("Asia/Bangkok"));
                            if($ptime>=$st&&$ptime<$to){
                                if($k==0){
                                    $outsource = "plan-out";
                                    $s = $o*6;
                                    $step = "left:".$s."px;";
                                    $o++;
                                } else {
                                    $outsource = "";
                                    $step = "";
                                }
                                $stdiff = date_diff($st,$ptime);
                                $top = $this->get_interval_ttmin($stdiff)*$ratio;
                                $ptime->add(new DateInterval("PT".round($info[3]*60)."M"));
                                $endiff = date_diff($ptime,$to);
                                //$bottom = $this->get_interval_ttmin($endiff)*$ratio;
                                $hei = $info[3]*60*$ratio;
                                if(isset($oidclass[$info[1]])){
                                    $bclass = $oidclass[$info[1]];
                                } else {
                                    $bclass = $oidclass[$info[1]] = "bar-color-$x";
                                    $x++;
                                }
                                $plan = "<div class='plan-vbar $bclass $outsource' style='top:$top"."px;height:$hei"."px;$step'>&nbsp;</div>"
                                        . "<div class='plan-bar-info'>$info[4]</div>";
                            }
                        }
                    } 
                    $html .= "<td height='$r_height' class='plan-rec-box $rclass'>$plan</td>";
                }
                $html .= "</tr>";
                $st->add(new DateInterval("PT".$interval."M"));
            }
        }
        $html .= "</table>"
                . "</div><!-- .plan-vschedule -->";
        return $html;
    }
    private function plan_schedule($mach,$data){
        $wid = 60;
        $interval = 30;
        $col_perday = 1440/$interval;
        $cwid = $wid/(60/$interval);
        $html = "<div class='plan-schedule'>"
                . "<table cellpadding='0' cellspacing='0'>";
        $oidclass = array();
        $x=0;
        foreach($mach as $k=>$v){
            $st = new DateTime($this->date,new DateTimeZone("Asia/Bangkok"));
            $outsource = ($k==0?"plan-out":"");
            $html .= "<tr class='$outsource'>"
                . "<th width='120'>$v</th>";
            for($j=0;$j<3;$j++){
                for($i=0;$i<$col_perday;$i++){
                    if($st->format("i")=="00"){
                        $cclass = "";
                    } else {
                        $cclass = "plan-sub-min";
                    }
                    $to = new DateTime($st->format("Y-m-d H:i:s"),new DateTimeZone("Asia/Bangkok"));
                    $to->add(new DateInterval("PT".$interval."M"));
                    $plan = "&nbsp;";
                    if(isset($data[$k])){
                        foreach($data[$k] as $info){
                            $ptime = new DateTime($info[0],new DateTimeZone("Asia/Bangkok"));
                            if($ptime>=$st&&$ptime<$to){
                                $stdiff = date_diff($st,$ptime);
                                $left = $this->get_interval_ttmin($stdiff);
                                $ptime->add(new DateInterval("PT".round($info[3]*60)."M"));
                                $endiff = date_diff($ptime,$to);
                                $right = $this->get_interval_ttmin($endiff);
                                if(isset($oidclass[$info[1]])){
                                    $bclass = $oidclass[$info[1]];
                                } else {
                                    $bclass = $oidclass[$info[1]] = "bar-color-$x";
                                    $x++;
                                }
                                $plan = "<div class='plan-bar $bclass $outsource' style='left:$left"."px;right:$right"."px;'>&nbsp;</div>"
                                        . "<div class='plan-bar-info'>$info[4]</div>";
                            }
                        }
                    } 
                    $html .= "<td width='$cwid' class='plan-rec-box $cclass'>$plan</td>";
                    $st->add(new DateInterval("PT".$interval."M"));
                }
            }
            $html .= "</tr>";
        }
        $html .= "</table>"
                . "</div><!-- .plan-schedule -->";
        return $html;
    }
    private function get_interval_ttmin(DateInterval $diff){
        $sign = $diff->invert;
        return ($sign==1?-1:1)*(($diff->d *24*60)+($diff->h*60)+$diff->i);
    }
}

