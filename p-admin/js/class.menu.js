function pull_down(id){
    var but = $("#"+id);
    but.on("click",function(){
       $(this).parent().toggleClass("p-down"); 
    });
}
function send_msg(e,arr){
    e.preventDefault();
    $("#contact").trigger("click");
    var data = get_val(arr);
    post_ajax(data,data['referurl']);
}
function show_sub_menu(){
    $("#panel").height($("#content").height());
    var but = $(".list-menu span");
    but.on("click",function(){
        $(this).siblings("ul").toggleClass("form-hide");
        $(this).parent("li").siblings("li").children("ul").addClass("form-hide");
        flex_menu();
    });
}
function show_mobilem(){
    var but = $("#menu-mobile");
    var panel = $("#panel");
    but.on("click",function(){
        panel.toggleClass("panel-show");
        $(window).scrollTop(0);
    });
}
function flex_menu(){
var mh,wh,ch,ph,sc;
$(document).ready(function(){
    var menu = $("#mymenu");
    var panel = $("#panel");
    var ct = $("#content");
    mh = menu.outerHeight();
    wh = $(window).height()-44;
    ct.height("auto");
    ch = ct.height();
    ph = panel.height();
    if(mh>wh){
        $(document).off("scroll");
        panel.addClass("panel-ab");
        if(ch<mh){
            $(document).off("scroll");
            ct.height(mh);
        } else {
            check_sc();
            $(document).on("scroll",function(){
                check_sc();
            });
        }
    } else {
        $(document).off("scroll");
        panel.removeClass("panel-ab");
    }
    function check_sc(){
        sc = $(document).scrollTop();
        if(wh+sc>mh){
            panel.removeClass("panel-ab");
            panel.css({"top":"auto"});
        } else {
            panel.addClass("panel-ab");
            panel.css({"top":"44px"});
        }
    }
});
}
function user_log(uid,url){
    $(document).ready(function(){
        var data = {};
        data['request'] = "update_u_log";
        data['uid'] = uid;
        post_ajax(data,url,true);
    });
}
function copy_fn(url){
    $(document).ready(function(){
        var but = $(".copy-fn");
        but.on("click",function(e){
            e.preventDefault();
            var data = {};
            data['request'] = "copy_fn";
            data['fid'] = $(this).attr("copy-id");
            //console.log(data);
            post_ajax(data,url);
        });
    });
}
function search_fn(link,month,type,scomp){
    $(document).ready(function(){
        var but = $("#search-but");
        but.on("click",function(){
            var find = $("#name-s").val().replace(/^[\s,]+|[\s,]+$/g,"");
            var v = "?m="+month+"&t="+type+"&sc="+scomp+"&s="+find;
            window.location.replace(link+v);
        });
    });
}