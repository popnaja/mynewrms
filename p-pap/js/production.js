function plan_change(){
    $(document).ready(function(){
       //change month
        var but = $(".plan-c-month");
        var url = $("#ajax_req").val();
        but.on("click",function(){
            if(!$(this).hasClass("plan-but-disable")){
                var data = {};
                data['request'] = "plan_change_month";
                data['date'] = $(this).attr("date");
                data['type'] = $(this).attr('ptype');
                post_ajax(data,url)
            }
        }); 
    });
}
function prod_edit(){
$(document).ready(function(){
    var but = $(".myplan-edit");
    var tab = $(".my-tab-inside");
    var url = $("#ajax_req").val();
    but.on("click",function(e){
        e.preventDefault();
        var info = $(this).attr("info").split(";");
        $("#pc-name").val(info[0]);
        $("#amount").val(info[1]);
        $("#prodtime").val(info[2]);
        $("#cpid").val(info[3]);
        $("#type").val($(this).attr("type"));
        if($.type(info[4])!=="undefined"){
            console.log(info[4]);
            var tt = info[4].split(" ");
            var stdate = tt[0];
            var timeh = tt[1].split(":")[0];
            var timem = tt[1].split(":")[1];
            $("#stdate").val(stdate);
            $("#timeh").val(timeh);
            $("#timem").val(timem);
        }
        tab.removeClass("form-hide");
        if($(this).attr("source")==2){
            $("#sel-machine").html("<input type='hidden' name='mcid' value='0' />");
        } else {
            var data = {};
            data['request'] = "get_mach";
            data['pid'] = $(this).attr("pid");
            data['target'] = "sel-machine";
            post_ajax(data,url);
        }
    });
});
}
function lock_plan_head(){
$(document).ready(function(){
    var tg = $(".plan-header");
    var sc;
    var po = tg.offset();
    $(document).on("scroll",function(){
        sc = $(document).scrollTop();
        if(sc>po.top){
            tg.addClass("plan-fixed");
        } else {
            tg.removeClass("plan-fixed");
        }
    });
});
}
function del_cpro(){
$(document).ready(function(){
    $("#del-cpro").on("click",function(){
        if(confirm("โปรดยืนยันการลบกระบวนการผลิต")){
            var data = {};
            data['request'] = "delete_cpro";
            data['cproid'] = $("#cproid").val();
            data['redirect'] = $("#redirect").val();
            post_ajax(data,$("#ajax_req").val());
        }
    });
});
}

