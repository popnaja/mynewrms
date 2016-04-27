function status_update(){
$(document).ready(function(){
    var start = $(".status-start");
    var tab = $("#up-result .my-tab-inside");
    var end = $(".status-end");
    var url = $("#ajax_req").val();
    start.on("click",function(e){
        e.preventDefault();
        var data = {};
        data['request'] = "get_start_status";
        data['cproid'] = $(this).attr("cproid");
        data['target'] = "result-input";
        post_ajax(data,url);
        tab.removeClass("form-hide");
    });
    end.on("click",function(e){
        e.preventDefault();
        var data = {};
        data['request'] = "get_end_status";
        data['cproid'] = $(this).attr("cproid");
        data['target'] = "result-input";
        post_ajax(data,url);
        tab.removeClass("form-hide");
    });
});
}
function comp_status(){
$(document).ready(function(){
    var edit = $(".edit-comp-status");
    edit.on("click",function(e){
        e.preventDefault();
        $("#oid").val($(this).attr("oid"));
        $("#compid").val($(this).attr("compid"));
        //console.log($(this).attr("oid"));
        my_float_box("status-box",true);
        var data = {};
        data['request'] = "get_sel_comp_status";
        data['compid'] = $(this).attr("compid");
        post_ajax(data,$("#ajax_req").val());
    });
    var medit = $(".edit-main-status");
    medit.on("click",function(e){
        e.preventDefault();
        $("#oid").val($(this).attr("oid"));
        my_float_box("status-box",true);
        var data = {};
        data['request'] = "get_sel_main_status";
        data['oid'] = $(this).attr("oid");
        post_ajax(data,$("#ajax_req").val());
    });
})
}
function mach_sel(url){
$(document).ready(function(){
    var sel = $("#mach");
    sel.on("change",function(){
        var mid = $(this).val();
        if(mid!=0){
            window.location.replace(url+"?mid="+mid);
        }
    });
    var dsel = $("#date");
    dsel.on("change",function(){
        var mid = $("#mid").val();
        var date = $(this).val();
        var reg = /^\d{4}-\d{2}-\d{2}$/;
        if(date.search(reg)===0){
            window.location.replace(url+"?mid="+mid+"&date="+date);
        }
    });
})
}
