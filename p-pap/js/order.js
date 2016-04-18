function ga_function(){
    $(document).ready(function(){
        var oid = $("#oid");
        var ed = $(".edit-status");
        ed.on("click",function(e){
            e.preventDefault();
            my_float_box('date-box',true);
            oid.val($(this).attr("oid"));
        });
    });
}
function check_ga_status(e){
    $(document).ready(function(){
        var sel = $("#status");
        var date = $("#date");
        if(sel.val()==="2"||sel.val()==="5"){
            if(date.val()===""){
                e.preventDefault();
                pg_dialog("คำเตือน","กรุณาเลือกวันที่");
            }
        }
    });
}
function order_search(){
    var id = "scid";
    auto_complete_input(id,findex,show);
    function show(ele){
        if(ele.hasClass("nores")){
            $("#"+id).val("");
        } else {
            $("#"+id).val(ele.html());
            $("#scid-but").trigger("click");
        }
    }
    function findex(find,f1,f2){
        var data = {};
        data['request'] = "find_job";
        data['f'] = find;
        var url = $("#ajax_req").val();
        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data:data,
            success: function(response) {
                f1(response);
            },
            error: function(err){
                f2("ERROR"+JSON.stringify(err));
            }
        });
    }
}