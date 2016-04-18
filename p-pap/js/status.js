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

