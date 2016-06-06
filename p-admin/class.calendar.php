<?php
class mycalendar{
    private $st_day;
    private $year;
    private $month;
    private $week;
    private $data;
    private $type;
    private $adj = 150;
    private $thm = array("ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
    private $thmonth = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฏาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
    private $dayname = array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
    private $dayname_th = array("อาทิตย์","จันทร์","อังคาร","พุทธ","พฤหัสบดี","ศุกร์","เสาร์");
    public function __construct($year,$month) {
        $this->year = $year;
        $this->month = $month;
    }
    public function show_calendar($data,$type="month",$week=null,$script=""){
        $this->data = $data;   //data is array key=yyyy-mm-dd val=array(max,id,text)
        $this->type = $type;
        if($type=="day"||$type=="month"){
            $date = new DateTime("$this->year-$this->month-01",new DateTimeZone("Asia/Bangkok"));
            $sub = -$date->format("w");
            $date->sub(new DateInterval("P".abs($sub)."D"));
            $this->st_day = $date;
            $this->week = $this->weekno($this->st_day);
        } else {
            $this->week = $week;
            $this->st_day = $this->st_week($week, $this->year);
        }
        if($type=="day"){
            $cdbox = $this->my_dayschedule();
        } else {
            $cdbox = "<div class='mycd-box'>"
                . $this->my_dayname()
                . $this->my_calendar()
                . "</div><!-- .mycd-box -->";
        }
        $html = "<div class='mycd-container'>"
                . $this->mycd_nav()
                . $cdbox
                . "</div><!-- .mycd-container -->"
                . "<script>"
                . "mycd_height($this->adj);"
                . "$script"
                . "</script>";
        return $html;
    }
    private function mycd_nav(){
        $type = $this->type;
        if($type=="day"||$type=="month"){
            $next = new DateTime("$this->year-$this->month-01",new DateTimeZone("Asia/Bangkok"));
            $next->add(new DateInterval("P1M"));
            $prev = new DateTime("$this->year-$this->month-01",new DateTimeZone("Asia/Bangkok"));
            $prev->sub(new DateInterval("P1M"));
        } else if($type=="W") {
            $next = $this->st_week($this->week, $this->year);
            $next->add(new DateInterval("P1W"));
            $prev = $this->st_week($this->week, $this->year);
            $prev->sub(new DateInterval("P1W"));
        } else if($type=="2W"){
            $next = $this->st_week($this->week, $this->year);
            $next->add(new DateInterval("P2W"));
            $prev = $this->st_week($this->week, $this->year);
            $prev->sub(new DateInterval("P2W"));
        }
        $nweek = $this->weekno($next);
        $pweek = $this->weekno($prev);
        $now_m = date_format(date_create(null,timezone_open("Asia/Bangkok")),"m");
        $now_y = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y");
        $now_w = date_format(date_create(null,timezone_open("Asia/Bangkok")),"W");
        if($type=="day"||$type=="month"){
            $tclass = ($now_m==$this->month?"mycd-disable":"");
        } else {
            if($now_w>$pweek&&$now_w<$nweek){
                $tclass = "mycd-disable";
            } else {
                $tclass = "";
            }
        }
        $dclass = ($type=="day"?"mycd-active":"");
        $wclass = ($type=="W"?"mycd-active":"");
        $w2class = ($type=="2W"?"mycd-active":"");
        $mclass = ($type=="month"?"mycd-active":"");
        $thyear = $this->year+543;
        $show_month = $this->thmonth[$this->month-1]." พ.ศ. $thyear";
        $html = "<div class='mycd-menu'>"
                . "<div class='mycd-title'>$show_month</div>"
                . "<div class='mycd-nav'>"
                . "<span class='gray-but mycd-c-month $tclass' year='$now_y' month='$now_m' week='$now_w' cdtype='$type'>Today</span>"
                . "<span class='gray-but mycd-c-month icon-chevron-left' year='".$prev->format("Y")."' month='".$prev->format("m")."' week='$pweek' cdtype='$type'></span>"
                . "<span class='gray-but mycd-c-month icon-chevron-right' year='".$next->format("Y")."' month='".$next->format("m")."' week='$nweek' cdtype='$type'></span>"
                . "</div><!-- . -->"
                . "<div class='mycd-switch'>"
                . "<span class='gray-but mycd-switch-type $dclass' cdtype='day' year='$this->year' week='$this->week' month='$this->month'>DAY</span>"
    . "<span class='gray-but mycd-switch-type $wclass' cdtype='W' year='$this->year' week='$this->week' month='$this->month'>WEEK</span>"
    . "<span class='gray-but mycd-switch-type $w2class' cdtype='2W' year='$this->year' week='$this->week'month='$this->month'>2WEEK</span>"
                . "<span class='gray-but mycd-switch-type $mclass' cdtype='month' year='$this->year' week='$this->week' month='$this->month'>MONTH</span>"
                . "</div>"
                . "</div><!-- .mycd-menu -->";
        return $html;
    }
    private function my_dayname(){
        $html = "<table class='mycd-daynames'>"
                . "<tr>";
        foreach ($this->dayname_th as $k=>$v){
            $html .= "<th>$v</th>";
        }
        $html .= "</tr>"
                . "</table>";
        return $html;
    }
    private function my_calendar(){
        $html = "<div class='mycd-calendar'>";
        if($this->type=="month"||$this->type=="day"){
            $w = $this->num_week($this->year, $this->month);
        } else if($this->type=="W"){
            $w = 1;
        } else if($this->type=="2W"){
            $w = 2;
        }
        for($i=0;$i<$w;$i++){
            $top = $i*(100/$w);
            $height = (100/$w);
            $html .= "<div class='month-row' style='height:$height%;top:$top%;'>"
                    . $this->mycd_rundate()
                    . "</div><!-- .month-row -->";
        }
        $html .= "</div><!-- .mycd-calendar -->";
        return $html;
    }
    private function mycd_rundate(){
        $big = "<table class='mycd-bg-tb'>"
                . "<tr>";
        $html = "<table class='mycd-week-tb'>"
                . "<tr>";
        $max = 0;
        
        $today = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Ymd");
        for($i=0;$i<7;$i++){
            $date = $this->st_day->format("Ymd");
            $dd = $this->st_day->format("d");
            $dofm = $this->st_day->format("j");
            $month = $this->st_day->format("m");
            $tdate = new DateTime($this->st_day->format("Ymd"),new DateTimeZone("Asia/Bangkok"));
            //loop big tb
            if($date==$today){
                $bclass = "mycd-bg-today";
                $istoday = "mycd-today";
            } else {
                $bclass = "";
                $istoday = "";
            }
            $big .= "<td class='$bclass'>&nbsp;</td>";
            //check same month
            if($month!==$this->month){
                $ismonth = "mycd-gray";
            } else {
                $ismonth = "";
            }
            //check is 1st 
            $show = ($dofm==1?$dofm." ".$this->thm[$month-1]:$dofm);
            $html .= "<td class='mycd-dtitle $ismonth $istoday'>$show</td>";
            //check max data
            if(isset($this->data[$date])){
                $max = max($max,count($this->data[$date]));
            } else if(isset($this->data[$dd])){
                $max = max($max,count($this->data[$dd]));
            }
            $this->st_day->add(new DateInterval("P1D"));
        }
        $big .= "</tr>"
                . "</table>";
        $html .= "</tr>"
                . $this->load_info($tdate, $max)
                . "</table>";
        
        return $big.$html;
    }
    private function load_info($stdate,$max){
        $info = "";
        $stdate->sub(new DateInterval("P6D"));
        for($j=0;$j<$max;$j++){
            $info .= "<tr>";
            $st = new DateTime($stdate->format("Ymd"),new DateTimeZone("Asia/Bangkok"));
            for($i=0;$i<7;$i++){
                $date = $st->format("Ymd");
                $dd = $st->format("d");
                $move = 1;
                if(isset($this->data[$date])){
                    $dinfo = $this->data[$date];
                    if(isset($dinfo[$j])&&is_array($dinfo[$j])){
                        $oid = $dinfo[$j][0];
                        $name = $dinfo[$j][1];
                        $days = $dinfo[$j][2];
                        $rclass = "";
                        if($days>1){
                            $rclass = "deli-range";
                            $tnext = new DateTime($st->format("Ymd"),new DateTimeZone("Asia/Bangkok"));
                            for($ii=0;$ii<$days-1;$ii++){
                                $tnext->add(new DateInterval("P1D"));
                                $td = $tnext->format("Ymd");
                                if(isset($this->data[$td])){
                                    array_unshift($this->data[$td],0);
                                    $max++;
                                }
                            }
                            $i += $days-1;
                            $move = $days;
                        }
                        $info .= "<td class='mycd-rec $rclass' oid='$oid' colspan='$days'>$name</td>";
                    } else {
                        $info .= "<td>&nbsp;</td>";
                    }
                } else if(isset($this->data[$dd])){
                    if($st->format("m")!=$this->month){
                        continue;
                    }
                    if(isset(explode(",",$this->data[$dd][1])[$j])){
                        $oid = explode(",",$this->data[$dd][1]);
                        $name = explode(",",$this->data[$dd][2]);
                        $info .= (isset($oid[$j])?"<td class='mycd-rec' oid='$oid[$j]'>$name[$j]</td>":"");
                    } else {
                        $info .= "<td>&nbsp;</td>";
                    }
                } else {
                    $info .= "<td>&nbsp;</td>";
                }
                $st->add(new DateInterval("P".$move."D"));
            }
            $info .= "</tr>";
        }
        return $info;
    }
    private function my_dayschedule(){
        $html = "<div class='mycd-schedule'>";
        $st = new DateTime("$this->year-$this->month-01",new DateTimeZone("Asia/Bangkok"));
        $tt = $st->format("t");
        $today = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Ymd");
        $extra = array();
        $bcolor = 0;
        for($i=0;$i<$tt;$i++){
            $day = $st->format("j");
            $dd = $st->format("d");
            $wday = $this->dayname_th[$st->format("w")];
            $date = $st->format("Ymd");
            $istoday = ($date==$today?"mycd-day-today":"");
            $html .= "<div class='mycd-day $istoday'>"
                    . "<div class='mycd-day-show'>"
                    . "<span>$day</span>"
                    . "<span>$wday</span>"
                    . "</div>"
                    . "<div class='mycd-day-rec'>";
            if(isset($extra[$date])){
                $einfo = $extra[$date];
                for($x=0;$x<count($einfo);$x++){
                    $id = $einfo[$x][0];
                    $name = $einfo[$x][1];
                    $bc = "bar-color-".$einfo[$x][2];
                    $html .= "<span class='mycd-rec $bc' oid='$id'>$name</span>";
                }
            }
            if(isset($this->data[$date])){
                $dinfo = $this->data[$date];
                for($j=0;$j<count($dinfo);$j++){
                    $id = $dinfo[$j][0];
                    $name = $dinfo[$j][1];
                    $days = $dinfo[$j][2];
                    $bc = "";
                    if($days>1){
                        $tnext = new DateTime($st->format("Ymd"),new DateTimeZone("Asia/Bangkok"));
                        if(isset($color[$id])){
                            $c = $color[$id];
                        } else {
                            $c = $bcolor;
                            $color[$id] = $c;
                            $bcolor++;
                        }
                        for($ii=0;$ii<$days-1;$ii++){
                            $tnext->add(new DateInterval("P1D"));
                            $nd = $tnext->format("Ymd");
                            if(isset($extra[$nd])){
                                array_push($extra[$nd],array($id,$name,$c));
                            } else {
                                $extra[$nd] = array(array($id,$name,$c));
                                
                            }
                        }
                        $bc = "bar-color-".$c;
                        
                    }
                    $html .= "<span class='mycd-rec $bc' oid='$id'>$name</span>";
                }
            } else if(isset($this->data[$dd])){
                $aoid = explode(",",$this->data[$dd][1]);
                $aname = explode(",",$this->data[$dd][2]);
                for($j=0;$j<count($aoid);$j++){
                    $html .= "<span class='mycd-rec' oid='$aoid[$j]'>$aname[$j]</span>";
                }
            }
            $html .= "</div>"
                    . "</div>";
            $st->add(new DateInterval("P1D"));
        }
        $html .="</div><!-- .mycd-schedule -->";
        return $html;
    }
    private function num_week($year,$month){
        $first = new DateTime("$year-$month-01",new DateTimeZone("Asia/Bangkok"));
        $wf = $first->format("w");
        $t = $first->format("t")-1;
        $first->add(new DateInterval("P".$t."D"));
        $wl = $first->format("w");
        return ceil(($t+$wf+(6-$wl))/7);
    }
    private function weekno($date){
        if($date->format("w")==0){
            return $date->format("W")+1;
        } else {
            return $date->format("W");
        }
    }
    private function st_week($weekno,$year){
        $d = new DateTime();
        $d->setTimezone(new DateTimeZone("Asia/Bangkok"));
        $d->setISODate($year, $weekno);
        $d->setTime(0,0,0);
        $d->sub(new DateInterval("P1D"));
        return $d;
    }
}

