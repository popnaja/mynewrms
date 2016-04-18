<?php
class myslider {
   public function show_slide($id,$arrinside){
       $html = "<div id='$id' class='ez-slide-frame cheight'>"
               . "<div class='ez-slide-cont'>";
       foreach($arrinside as $k => $v){
           $html .= "<div class='ez-slide-item cheight'>$v</div>";
       }
       $html .= "</div>"
               . "<div class='ez-slide-nav'>"
               . "<span class='ez-slide-prev icon-arrow-circle-left'></span>"
               . "<span class='ez-slide-next icon-arrow-circle-right'></span>"
               . "</div><!-- .ez-slide-nav -->"
               . "<div class='ez-slide-dot'>"
               . "<ul></ul>"
               . "</div><!-- .ez-slide-dot -->"
               . "</div><!-- #$id -->"
               . "<script>ez_slider();</script>\n";
       return $html;
   }
}
