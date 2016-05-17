function page_change(url,selclass){
    $(document).ready(function(){
        var sel = $("."+selclass);
        sel.on("change",function(){
            window.location.href = url+sel.val();
        });
    });
}
function opt_show(){
    var but = $(".op-but");
    var div = $("#op-wrap");
    var arr = $(".op-arr");
    var h;
    but.on("click",function(){
        h = div.height();
        if(h>0){
            but.css({"top":"0"});
            arr.removeClass("icon-chevron-up");
            arr.addClass("icon-chevron-down");
            div.height(0);
        } else {
            but.css({"top":"-1px"});
            arr.removeClass("icon-chevron-down");
            arr.addClass("icon-chevron-up");
            div.css({"height":"auto"});
        }
    });
}
function opt_apply(arr,cols){
    var data = get_val(arr),res;
    data['request'] = "up_umeta_tb";
    data['uid'] = $("#uid").val();
    data['redirect'] = $("#tbadj_re").val();
    res = data['col_val'];
    $.each(cols,function(k,v){
        cols[k][1] = 0;
    });
    $.each(res,function(k,v){
        cols[v][1] = 1;
    });
    data['col_val'] = cols;
    post_ajax(data,"request.php");
}

function user_del(){
    var but = $(".del-user");
    but.on("click",function(){
        if(confirm("Are you sure you want to delete this user?")){
            var data = {};
            data['uid'] = $(this).attr("uid");
            data['request'] = "del_user";
            //animate
            var row = $(this).parents("tr");
            row.css({
                "background-color":"#f16451",
                "color":"#fff"
            }).animate({opacity:0},1000,function(){
                $(this).remove();
            });
            post_ajax(data,$("#referurl").val());
        }
    });
}

function del_fn(){
    var but = $(".del-fn");
    but.on("click",function(){
        if(confirm("กรุณายืนยันการลบ")){
            var data = {};
            var url = $("#referurl").val();
            data['fid'] = $(this).attr("fid");
            data['request'] = "del_fn";

            post_ajax(data,url);
        }
    });
}
function tb_filter(name,base){
$(document).ready(function(){
    var sel = $("select[name="+name+"]");
    sel.on("change",function(){
        window.location.replace(base+$(this).val());
    });
});
}
function tb_date_filter(name,base){
$(document).ready(function(){
    var sel = $("input[name="+name+"]");
    sel.on("change",function(){
        window.location.replace(base+$(this).val());
    });
});
}
function tb_search(id,base){
    $(document).ready(function(){
        var but = $("#"+id+"-but");
        but.on("click",function(){
            window.location.replace(base+$("#"+id).val());
        });
    });
}
function show_table(head,rec,id){
    var html = "<div id='"+id+"' class='ez-table'><table><tr class='tb-head tb-row'>";
    $.each(head,function(i,val){
        html += "<th>"+val+"</th>";
    });
    html += "</tr>";
    if(rec.length>0||Object.keys(rec).length>0){
        $.each(rec,function(key,value){
            if($.type(value)=="array"){
                html += "<tr class='tb-data'>";
                $.each(value,function(k,v){
                    html += "<td>"+v+"</td>";
                });
                html += "</tr>";
            }
        });
    } else {
        var len = head.length;
        html += "<tr><td colspan='"+len+"' class='tb-noinfo'>No information</td></tr>";
    }
    return html;
}