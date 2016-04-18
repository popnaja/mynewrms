function get_process_info(){
    $(document).ready(function(){
        var sel = $("#sel_process");
        sel.on("change",function(){
            var data = {};
            var url = $("#ajax_rq").val();
            data['request'] = "get_process_info";
            data['pid'] = $(this).val();
            data['target'] = "pinfo";
            post_ajax(data,url);
        });
    });
}


