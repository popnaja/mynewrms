function adj_rec(){
    $(document).ready(function(){
        var sel = $("#all");
        var tg = $("input[name='receive[]']");
        sel.on("change",function(){
            if($(this).val()=="no"){
                tg.parent().removeClass("readonly");
                tg.attr("readonly",false);
            } else {
                tg.parent().addClass("readonly");
                tg.attr("readonly",true);
            }
        });
    });
}
function del_process_deli(){
$(document).ready(function(){
    $("#del-process-deli").on("click",function(){
        if(confirm("ยืนยันการลบรายการรับเข้านี้")){
            var data = {};
            data['request'] = "delete_process_deli";
            data['dyid'] = $("#dyid").val();
            data['redirect'] = $("#redirect").val();
            post_ajax(data,$("#ajax_req").val());
        }
    });
});
}
function del_mat_deli(){
$(document).ready(function(){
    $("#del-mat-deli").on("click",function(){
        if(confirm("ยืนยันการลบรายการรับเข้านี้")){
            var data = {};
            data['request'] = "delete_mat_deli";
            data['dyid'] = $("#dyid").val();
            data['redirect'] = $("#redirect").val();
            post_ajax(data,$("#ajax_req").val());
        }
    });
});
}