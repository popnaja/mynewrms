<?php
include_once("myfunction.php");
__autoload("form");
class mymenu{
    public $astyle = array();
    public $ascript = array();
    public $extrascript = "";
    public $pageTitle;
    public $canonical_link = "";
    public $language;
    public $root;
    public $aroot;
    public $throot;
    public $fbmeta = "";
    public $menu = "";
    public $dir;
    public $meta = array(
        'viewport'      => 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'
    );
    public $cart = 0;
    public $login = false;
    public $login_redirect;
    public $site;
    public $icon;
    public $logo;
    public $contact;
    public function __construct($lang=null) {
        $this->site = SITE;
        $this->language = (isset($lang)?$lang:"en");
        $this->root = ROOTS;
        $this->aroot = AROOTS;
        $this->logo = AROOTS."image/resoluteMS_logo.jpg";
        $this->dir = ADMIN_PATH."/";
        
        $this->astyle[] = $this->aroot."css/fontface.css";
        $this->astyle[] = $this->aroot."css/ifonts.css";
        $this->ascript[] = $this->aroot."js/jquery-1.11.2.min.js";
        
        $this->astyle[] = $this->aroot."css/class.menu.css";
        $this->ascript[] = $this->aroot."js/class.menu.js";
    }
    /*function load class use for loading both php js and css */
    public function __autoloadall($class_name) {
        include_once ($this->dir."class.".$class_name .".php");
        $this->astyle[] = $this->aroot."css/class.".$class_name.".css";
        $this->ascript[] = $this->aroot."js/class.".$class_name.".js";
    }
    public function canonical($url){
        $this->canonical_link = "<link rel='canonical' href='$url' />";
    }
    public function fb_meta($title,$url,$des,$img=null){
        $fbmeta = "<meta property='og:title' content='$title' />"
                . "<meta property='og:site_name' content='CalForLife.com' />"
                . "<meta property='og:url' content='$url' />"
                . "<meta property='og:description' content='$des' />"
                . "<meta property='fb:appid' content='1714256045469524' />";
        if(isset($img)){
            $fbmeta .= "<meta property='og:image' content='$img' />";
        } else {
            $fbmeta .= "<meta property='og:image' content='' />";
        }
        $this->fbmeta = $fbmeta;
    }
    public function goggle_ana(){
        $script = <<<END_OF_TEXT
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-69406267-1', 'auto');
  ga('send', 'pageview');

</script>
END_OF_TEXT;
        return $script;
    }
    public function show_menu($menu){
        $this->menu = $menu;
    }
    public function showhead(){
        $head = "<!DOCTYPE html>\n"
                . "<html lang='$this->language'>\n"
                . "<head profile='http://www.w3.org/2005/10/profile'>\n"
                . '<meta http-equiv=Content-Type content="text/html; charset=utf-8">';
        foreach($this->meta as $key => $value){
            $head .= "<meta name='$key' content='$value'>\n";
        }
        $head .= "<title>$this->pageTitle</title>\n";
        $head .= $this->canonical_link;
        $head .= $this->fbmeta;
        foreach($this->astyle as $value){
            $head .= "<link href='$value' type='text/css' rel='stylesheet'>\n";
        }
        foreach($this->ascript as $value){
            $head .= "<script src='$value'></script>\n";
        }
        //icon
        
        $head .= "<link rel='icon' type='image/png' href='$this->logo' />";
        $head .= $this->extrascript;
        $head .= "</head>"
                . "<body>";
        
        //google analytic
        //$head .= $this->goggle_ana();
        
        return $head;
    }
    public function showpanel($active,$sub="",$visible=true){
        if($visible){
            $panel = "<div id='top-panel'>"
                . "<div id='menu-mobile' class='icon-three-bars'></div>"
                . "<h1 id='logo'>Resolute MS </h1>"
                . "<div class='rms-logo'>"
                . "<a href='http://www.resolutems.com/' title='Resolute MS'><img src='$rms_white'></a>"
                . "</div><!-- .rms-logo -->"
                . "</div><!-- #top-panel -->"
                . "<div id='panel'>"
                . "<ul id='mymenu'>"
                . $this->show_menuli($active,$sub)
                . "</ul>"
                . "</div><!-- #panel -->"
                . "<script>"
                . "show_mobilem();"
                . "flex_menu();"
                . "</script>\n";
        } else {
            $panel = "";
        }
        $panel = "<div id='wrapper' class='cheight'>\n"
                . $panel
                . "<div id='content' class='cheight'>"
                . $this->loading();
        return $panel;
    }
    public function show_contact(){
        $form = new myform();
        $contact = $form->show_st_form()
                . "<div id='contact-dt' class='p-down'>"
                . "<div class='ct-in'>"
                . "<a href='tel:061-864-8641' title='Call 061-864-8641' class='tel'><span class='icon-call-phone-square'></span>061-864-8641</a>"
                . "<h2>Leave your message: </h2>"
                . $form->show_text("name","name","","","Name","","label-inline")
                . $form->show_text("tel","tel","","","Telephone","","label-inline")
                . $form->show_textarea("msg","",2,10,"","Message","label-inline")
                . $form->show_submit("send","Send","but-right")
                . $form->show_hidden("request","request","add_msg")
                . $form->show_hidden("referurl","referurl",AROOTS."request.php")
                . "</div>"
                . "<div id='contact'>Contact <span class='icon-chevron-down'></span><span class='icon-chevron-up'></span></div>"
                . "</div><!-- #contact-dt -->";
        $form->addformvalidate('ct-msg',['name','tel','msg']);
        $arrn = json_encode($form->array_name);
        $contact .= $form->submitscript("send_msg(e,$arrn);")
                . "<script>pull_down('contact');</script>";
        $this->contact = $contact;
    }
    public function loading(){
        $gif = ROOTS."p-admin/image/ajax-loader.gif";
        return  "<div class='pg-loading-dialog'>"
                . "<div class='ajax-dialog'>"
                . "<h3>Loading...</h3>"
                . "<p></p>"
                . "<div class='ajax-gif'><img src='".$gif."'/></div>"
                . "</div><!-- .ajax-dialog -->"
                . "</div><!-- .pg-loading-dialog -->";
    }
    public function showfooter(){
        $footer = "</div><!-- #content -->"
                . "<footer>"
                . "<div id='popsget_logo'></div>"
                . "<div id='copyright'>"
                . "<p>All material herein &#64 2015 $this->site, All Rights Reserved. <br/>"
                . "Developed by <a href='http://www.resolutems.com/' title='ResoluteMS.com'>Resolute Management Services CO., LTD.</a></p>"
                . "</div><!-- #cotyright -->"
                . "</footer>"
                . "</div><!-- #wrapper -->"
                . "</body>"
                . "</html>";
        return $footer;
    }
    
    public function show_menuli($active,$sub=""){
        $logo = "<div id='site-logo'>"
                . "<a href='$this->root' title='$this->site'>"
                . "<img src='$this->logo' alt='$this->site'>"
                . "</a>"
                . "</div><!-- #site-logo -->";
        $panel = $logo;
        foreach($this->menu as $key => $value){
            if(!is_array($value)){
                $actclass = ($key==$active?"m-active":"");
                $id = str_replace(" ","",$key);
                $panel .= "<li id='$id' class='list-menu $actclass'><a href='$value' class='menu-link' title='$key' data-role='none'>$key</a></li>";
            } else {
                if($key==$active){
                    $actclass = "m-active";
                    $hid_sub = "";
                } else {
                    $actclass = "";
                    $hid_sub = "form-hide";
                }
                $id = str_replace(" ","",$key);
                $panel .= "<li id='$id' class='list-menu $actclass'>";
                $panel .= "<span>$key</span>"
                        . "<ul class='sub-menu $hid_sub'>";
                foreach($value as $keym => $valuem){
                    if($keym == "0"){
                        $panel .= "";
                    } else {
                        $panel .= "<li><a href='$valuem' title='$keym' ".($keym==$sub&&$key==$active ? "class='subactive'" : "")." data-role='none'>$keym</a></li>";
                    }
                }
                $panel .= "</ul></li>\n";
            }
        }
        $panel .= "<script>show_sub_menu();</script>";
        return $panel;
    }
}